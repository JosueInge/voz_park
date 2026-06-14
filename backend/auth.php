<?php
// auth.php — estructura plana

require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/response.php';

/**
 * Valida el token JWT del encabezado Authorization.
 * En modo desarrollo (APP_ENV=development) hace bypass automático.
 *
 * @param  string|null $requiredRole  'poblador' | 'admin' | null
 * @return array  Payload decodificado del token
 */
function requireAuth(?string $requiredRole = null): array {

    // ── Bypass en desarrollo ──────────────────────────────────────────────────
    if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
        $pdo  = getDB();
        $rol  = $requiredRole ?? 'poblador';
        $user = $pdo->query(
            "SELECT id, nombre, rol FROM usuarios
              WHERE rol = '{$rol}'
              LIMIT 1"
        )->fetch();

        if ($user) {
            return [
                'id'     => (int)$user['id'],
                'nombre' => $user['nombre'],
                'rol'    => $user['rol'],
            ];
        }
    }

    // ── Validación real con JWT ───────────────────────────────────────────────
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($authHeader) && function_exists('apache_request_headers')) {
        $headers    = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        error('Token de sesión requerido', 401);
    }

    $token   = trim(substr($authHeader, 7));
    $payload = jwtDecode($token);

    if (!$payload) {
        error('Token inválido o expirado', 401);
    }

    if ($requiredRole && ($payload['rol'] ?? '') !== $requiredRole) {
        error('No tienes permisos para realizar esta acción', 403);
    }

    return $payload;
}