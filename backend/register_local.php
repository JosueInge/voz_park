<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';

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

$nombre = trim((string)($input['nombre'] ?? ''));
$correo = strtolower(trim((string)($input['correo'] ?? $input['email'] ?? '')));
$telefono = trim((string)($input['telefono'] ?? ''));
$zona = trim((string)($input['zona'] ?? $input['location'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($nombre === '' || $correo === '' || $telefono === '' || $zona === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Faltan datos obligatorios del registro'
    ], 400);
}

try {
    $pdo = getDB();

    $stmt = $pdo->prepare(
        "SELECT id FROM usuarios WHERE LOWER(correo) = :correo LIMIT 1"
    );
    $stmt->execute([':correo' => $correo]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        jsonResponse([
            'success' => false,
            'message' => 'El correo ya esta registrado'
        ], 409);
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $pdo->prepare(
        "INSERT INTO usuarios
         (nombre, correo, password, telefono, zona, foto_perfil, rol, auth_provider, email_verificado, ultimo_login)
         VALUES
         (:nombre, :correo, :password, :telefono, :zona, NULL, 'poblador', 'local', FALSE, NULL)
         RETURNING id"
    );

    $insert->execute([
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':password' => $passwordHash,
        ':telefono' => $telefono,
        ':zona' => $zona
    ]);

    $userId = (int)$insert->fetchColumn();

    success([
        'usuario' => [
            'id' => $userId,
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'zona' => $zona,
            'auth_provider' => 'local'
        ]
    ], 'Registro local exitoso');
} catch (Throwable $e) {
    $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
    $response = [
        'success' => false,
        'message' => 'No se pudo completar el registro'
    ];

    if ($isDev) {
        $response['debug'] = $e->getMessage();
    }

    jsonResponse($response, 500);
}
