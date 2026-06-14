<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/jwt.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Metodo no permitido'
    ], 405);
}

$googleClientId = trim($_ENV['GOOGLE_CLIENT_ID'] ?? '');
if ($googleClientId === '') {
    jsonResponse([
        'success' => false,
        'message' => 'GOOGLE_CLIENT_ID no esta configurado en backend/.env',
        'code' => 'GOOGLE_CONFIG_MISSING'
    ], 500);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput ?: '', true);

if (!is_array($input)) {
    jsonResponse([
        'success' => false,
        'message' => 'JSON invalido'
    ], 400);
}

$idToken = trim((string)($input['credential'] ?? $input['id_token'] ?? ''));
$confirmOwnership = filter_var($input['confirm_ownership'] ?? false, FILTER_VALIDATE_BOOLEAN);

if ($idToken === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Token de Google requerido'
    ], 400);
}

try {
    $googleData = verifyGoogleIdToken($idToken, $googleClientId);

    $googleSub = trim((string)($googleData['sub'] ?? ''));
    $email = strtolower(trim((string)($googleData['email'] ?? '')));
    $name = trim((string)($googleData['name'] ?? ''));
    $picture = trim((string)($googleData['picture'] ?? ''));
    $emailVerifiedRaw = $googleData['email_verified'] ?? false;
    $emailVerified = $emailVerifiedRaw === true
        || $emailVerifiedRaw === 'true'
        || $emailVerifiedRaw === '1'
        || $emailVerifiedRaw === 1;

    if ($googleSub === '' || $email === '') {
        throw new RuntimeException('INVALID_TOKEN');
    }

    if (!$emailVerified) {
        jsonResponse([
            'success' => false,
            'message' => 'El correo de Google no esta verificado',
            'code' => 'EMAIL_NOT_VERIFIED'
        ], 401);
    }

    $pdo = getDB();

    if (!schemaReady($pdo)) {
        jsonResponse([
            'success' => false,
            'message' => 'Falta estructura para Google Auth. Ejecuta backend/sql/google_auth_postgres.sql',
            'code' => 'SCHEMA_MISSING'
        ], 500);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT id, nombre, correo, telefono, zona, foto_perfil, rol, google_sub, auth_provider
         FROM usuarios
         WHERE correo = :correo OR google_sub = :google_sub
         LIMIT 1"
    );
    $stmt->execute([
        ':correo' => $email,
        ':google_sub' => $googleSub
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $userId = null;

    if ($user) {
        $linkedGoogleSub = trim((string)($user['google_sub'] ?? ''));

        if ($linkedGoogleSub === '' && !$confirmOwnership) {
            $pdo->rollBack();
            jsonResponse([
                'success' => false,
                'message' => 'Confirmacion requerida para vincular Google con esta cuenta existente.',
                'code' => 'OWNER_CONFIRMATION_REQUIRED'
            ], 409);
        }

        $userId = (int)$user['id'];

        $update = $pdo->prepare(
            "UPDATE usuarios
             SET nombre = COALESCE(NULLIF(:nombre, ''), nombre),
                 google_sub = :google_sub,
                 foto_perfil = COALESCE(NULLIF(:foto_perfil, ''), foto_perfil),
                 email_verificado = TRUE,
                 auth_provider = CASE
                     WHEN auth_provider = 'local' THEN auth_provider
                     ELSE 'google'
                 END,
                 ultimo_login = NOW()
             WHERE id = :id"
        );

        $update->execute([
            ':nombre' => $name,
            ':google_sub' => $googleSub,
            ':foto_perfil' => $picture,
            ':id' => $userId
        ]);
    } else {
        $fallbackName = $name !== '' ? $name : 'Usuario Google';
        $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

        $insert = $pdo->prepare(
            "INSERT INTO usuarios
             (nombre, correo, password, telefono, zona, foto_perfil, rol, google_sub, auth_provider, email_verificado, ultimo_login)
             VALUES
             (:nombre, :correo, :password, NULL, NULL, :foto_perfil, 'poblador', :google_sub, 'google', TRUE, NOW())
             RETURNING id"
        );

        $insert->execute([
            ':nombre' => $fallbackName,
            ':correo' => $email,
            ':password' => $randomPassword,
            ':foto_perfil' => $picture,
            ':google_sub' => $googleSub
        ]);

        $userId = (int)$insert->fetchColumn();
    }

    $finalUserStmt = $pdo->prepare(
        "SELECT id, nombre, correo, telefono, zona, foto_perfil, rol, auth_provider
         FROM usuarios
         WHERE id = :id
         LIMIT 1"
    );
    $finalUserStmt->execute([':id' => $userId]);
    $finalUser = $finalUserStmt->fetch(PDO::FETCH_ASSOC);

    if (!$finalUser) {
        throw new RuntimeException('USER_SAVE_ERROR');
    }

    $token = jwtEncode([
        'sub' => (int)$finalUser['id'],
        'nombre' => $finalUser['nombre'],
        'correo' => $finalUser['correo'],
        'rol' => $finalUser['rol'],
        'provider' => 'google'
    ]);

    $expiresIn = (int)($_ENV['JWT_EXPIRY'] ?? 3600);
    $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
    $tokenHash = hash('sha256', $token);
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $sessionStmt = $pdo->prepare(
        "INSERT INTO sesiones_usuario
         (usuario_id, token_hash, provider, ip, user_agent, expira_en)
         VALUES
         (:usuario_id, :token_hash, 'google', :ip, :user_agent, :expira_en)"
    );
    $sessionStmt->execute([
        ':usuario_id' => (int)$finalUser['id'],
        ':token_hash' => $tokenHash,
        ':ip' => $ip,
        ':user_agent' => $userAgent,
        ':expira_en' => $expiresAt
    ]);

    $pdo->commit();

    success([
        'token' => $token,
        'expires_in' => $expiresIn,
        'usuario' => [
            'id' => (int)$finalUser['id'],
            'nombre' => $finalUser['nombre'],
            'correo' => $finalUser['correo'],
            'telefono' => $finalUser['telefono'],
            'zona' => $finalUser['zona'],
            'foto_perfil' => $finalUser['foto_perfil'],
            'rol' => $finalUser['rol'],
            'auth_provider' => $finalUser['auth_provider']
        ]
    ], 'Autenticacion con Google exitosa');
} catch (RuntimeException $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e->getMessage() === 'INVALID_TOKEN') {
        jsonResponse([
            'success' => false,
            'message' => 'Token invalido de Google',
            'code' => 'INVALID_GOOGLE_TOKEN'
        ], 401);
    }

    if ($e->getMessage() === 'GOOGLE_CONNECTION_ERROR') {
        jsonResponse([
            'success' => false,
            'message' => 'Error de conexion con Google',
            'code' => 'GOOGLE_CONNECTION_ERROR'
        ], 502);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Problemas al guardar o autenticar el usuario',
        'code' => 'GOOGLE_AUTH_ERROR'
    ], 500);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
    $response = [
        'success' => false,
        'message' => 'Problemas al guardar o autenticar el usuario',
        'code' => 'GOOGLE_AUTH_ERROR'
    ];

    if ($isDev) {
        $response['debug'] = $e->getMessage();
    }

    jsonResponse($response, 500);
}

function verifyGoogleIdToken(string $idToken, string $expectedClientId): array {
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        throw new RuntimeException('GOOGLE_CONNECTION_ERROR');
    }

    $httpHeader = $http_response_header[0] ?? '';
    preg_match('/\s(\d{3})\s/', $httpHeader, $matches);
    $status = isset($matches[1]) ? (int)$matches[1] : 0;

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('GOOGLE_CONNECTION_ERROR');
    }

    if ($status !== 200) {
        if ($status >= 500 || $status === 0) {
            throw new RuntimeException('GOOGLE_CONNECTION_ERROR');
        }
        throw new RuntimeException('INVALID_TOKEN');
    }

    if (($data['aud'] ?? '') !== $expectedClientId) {
        throw new RuntimeException('INVALID_TOKEN');
    }

    $exp = isset($data['exp']) ? (int)$data['exp'] : 0;
    if ($exp < time()) {
        throw new RuntimeException('INVALID_TOKEN');
    }

    return $data;
}

function schemaReady(PDO $pdo): bool {
    return tableExists($pdo, 'usuarios')
        && tableExists($pdo, 'sesiones_usuario')
        && columnExists($pdo, 'usuarios', 'google_sub')
        && columnExists($pdo, 'usuarios', 'auth_provider')
        && columnExists($pdo, 'usuarios', 'email_verificado')
        && columnExists($pdo, 'usuarios', 'ultimo_login');
}

function tableExists(PDO $pdo, string $tableName): bool {
    $stmt = $pdo->prepare(
        "SELECT EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = :table_name
        )"
    );
    $stmt->execute([':table_name' => $tableName]);
    return (bool)$stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $tableName, string $columnName): bool {
    $stmt = $pdo->prepare(
        "SELECT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = :table_name
              AND column_name = :column_name
        )"
    );
    $stmt->execute([
        ':table_name' => $tableName,
        ':column_name' => $columnName
    ]);
    return (bool)$stmt->fetchColumn();
}
