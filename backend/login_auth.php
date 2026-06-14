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

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput ?: '', true);

if (!is_array($input)) {
    jsonResponse([
        'success' => false,
        'message' => 'JSON invalido'
    ], 400);
}

$correo = strtolower(trim((string)($input['correo'] ?? $input['email'] ?? '')));
$password = (string)($input['password'] ?? '');

if ($correo === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Correo electronico o contraseña incorrectos'
    ], 401);
}

try {
    $pdo = getDB();

    $stmt = $pdo->prepare(
        "SELECT id, nombre, correo, telefono, zona, foto_perfil, rol, password, auth_provider, email_verificado
         FROM usuarios
         WHERE LOWER(correo) = :correo
         LIMIT 1"
    );
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (!$usuario) {
        jsonResponse([
            'success' => false,
            'message' => 'Correo electronico o contraseña incorrectos'
        ], 401);
    }

    $authProvider = strtolower(trim((string)($usuario['auth_provider'] ?? 'local')));
    if ($authProvider !== 'local') {
        jsonResponse([
            'success' => false,
            'message' => 'Correo electronico o contraseña incorrectos'
        ], 401);
    }

    $emailVerificado = filter_var($usuario['email_verificado'] ?? false, FILTER_VALIDATE_BOOLEAN);
    if (!$emailVerificado) {
        jsonResponse([
            'success' => false,
            'message' => 'Debes verificar tu correo electronico antes de iniciar sesion'
        ], 403);
    }

    $hash = (string)($usuario['password'] ?? '');
    if ($hash === '' || !password_verify($password, $hash)) {
        jsonResponse([
            'success' => false,
            'message' => 'Correo electronico o contraseña incorrectos'
        ], 401);
    }

    $token = jwtEncode([
        'sub' => (int)$usuario['id'],
        'nombre' => $usuario['nombre'],
        'correo' => $usuario['correo'],
        'rol' => $usuario['rol'],
        'provider' => 'local'
    ]);

    $expiresIn = (int)($_ENV['JWT_EXPIRY'] ?? 3600);

    if (tableExists($pdo, 'sesiones_usuario')) {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
        $tokenHash = hash('sha256', $token);
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $sessionStmt = $pdo->prepare(
            "INSERT INTO sesiones_usuario
             (usuario_id, token_hash, provider, ip, user_agent, expira_en)
             VALUES
             (:usuario_id, :token_hash, 'local', :ip, :user_agent, :expira_en)"
        );

        $sessionStmt->execute([
            ':usuario_id' => (int)$usuario['id'],
            ':token_hash' => $tokenHash,
            ':ip' => $ip,
            ':user_agent' => $userAgent,
            ':expira_en' => $expiresAt
        ]);
    }

    success([
        'token' => $token,
        'expires_in' => $expiresIn,
        'usuario' => [
            'id' => (int)$usuario['id'],
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'telefono' => $usuario['telefono'],
            'zona' => $usuario['zona'],
            'foto_perfil' => $usuario['foto_perfil'],
            'rol' => $usuario['rol'],
            'auth_provider' => 'local'
        ]
    ], 'Autenticacion local exitosa');
} catch (Throwable $e) {
    $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
    $response = [
        'success' => false,
        'message' => 'Error al iniciar sesion'
    ];

    if ($isDev) {
        $response['debug'] = $e->getMessage();
    }

    jsonResponse($response, 500);
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
