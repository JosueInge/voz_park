<?php
// database.php — estructura plana

/**
 * Carga las variables del archivo .env ubicado en backend/.env
 * El .env está en la misma carpeta backend/
 */
function loadEnv(): void {
    static $loaded = false;
    if ($loaded) return;

    // __DIR__ apunta a backend/ directamente (estructura plana)
    $envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

    if (!file_exists($envFile)) return;

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
    $loaded = true;
}

loadEnv();

/**
 * Retorna una conexión PDO singleton a PostgreSQL.
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '5432';
    $name = $_ENV['DB_NAME'] ?? 'vozpark';
    $user = $_ENV['DB_USER'] ?? 'postgres';
    $pass = $_ENV['DB_PASS'] ?? '';

    $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // IMPORTANTE: true evita problemas con parámetros nombrados en PostgreSQL
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]);
        $pdo->exec("SET client_encoding TO 'UTF8'");

    } catch (PDOException $e) {
        $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error de conexión a la base de datos',
            'debug'   => $isDev ? $e->getMessage() : null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $pdo;
}