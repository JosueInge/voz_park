<?php
// incidencia_controller.php — estructura plana

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/incidencia.php';
require_once __DIR__ . '/personal.php';

class IncidenciaController {

    /**
     * GET /admin/incidencias
     * GET /admin/incidencias?busqueda=&urgencia=&estado=&parque=
     */
    public static function listar(): void {
        // Bypass en desarrollo
        self::autenticarAdmin();

        $busqueda = trim($_GET['busqueda'] ?? '');
        $urgencia = trim($_GET['urgencia'] ?? '');
        $estado   = trim($_GET['estado']   ?? '');
        $parque   = trim($_GET['parque']   ?? '');

        if ($urgencia !== '' && !in_array($urgencia, Incidencia::URGENCIAS)) {
            error('Valor de urgencia no válido. Use: Alta, Media o Baja', 422);
        }
        if ($estado !== '' && !in_array($estado, Incidencia::ESTADOS)) {
            error('Valor de estado no válido', 422);
        }

        $incidencias = Incidencia::listar($busqueda, $urgencia, $estado, $parque);

        if (empty($incidencias)) {
            $hayFiltros = $busqueda !== '' || $urgencia !== '' || $estado !== '' || $parque !== '';
            success([], $hayFiltros ? 'No se encontraron resultados' : 'No hay incidencias registradas');
            return;
        }

        success($incidencias, 'Incidencias obtenidas correctamente');
    }

    /**
     * GET /admin/incidencias/{id}
     */
    public static function detalle(int $id): void {
        self::autenticarAdmin();

        $incidencia = Incidencia::detalle($id);
        if (!$incidencia) {
            error('La incidencia no fue encontrada', 404);
        }

        success($incidencia, 'Detalle obtenido correctamente');
    }

    /**
     * PUT /admin/incidencias/{id}
     */
    public static function actualizar(int $id): void {
        self::autenticarAdmin();

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $nuevoEstado = trim($body['estado']      ?? '');
        $personalId  = isset($body['personal_id']) && $body['personal_id'] !== null
                        ? (int)$body['personal_id']
                        : null;

        if (!$nuevoEstado || !in_array($nuevoEstado, Incidencia::ESTADOS)) {
            error('Estado no válido', 422);
        }

        $actual = Incidencia::detalle($id);
        if (!$actual) error('La incidencia no fue encontrada', 404);
        if ($actual['estado'] === 'Resuelto')
            error('No se puede modificar una incidencia con estado Resuelto', 422);

        if (in_array($nuevoEstado, ['Asignado','En curso','En revisión']) && !$personalId)
            error('Debes seleccionar personal para este estado', 422);

        if ($personalId && !Personal::existeActivo($personalId))
            error('El personal seleccionado no existe o no está activo', 422);

        $ok = Incidencia::actualizar($id, $nuevoEstado, $personalId);
        $ok ? success(null, 'Cambios guardados correctamente')
            : error('Error al procesar la solicitud', 500);
    }

    // ── Autenticación admin con bypass en desarrollo ──────────────────────────
    private static function autenticarAdmin(): void {
        $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
        if ($isDev) return; // sin token en desarrollo

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $h = apache_request_headers();
            $authHeader = $h['Authorization'] ?? $h['authorization'] ?? '';
        }

        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
            if (file_exists(__DIR__ . '/jwt.php')) {
                require_once __DIR__ . '/jwt.php';
                if (function_exists('jwtDecode')) {
                    $payload = jwtDecode($token);
                    if ($payload && ($payload['rol'] ?? '') === 'admin') return;
                }
            }
        }

        jsonResponse(['success' => false, 'message' => 'Acceso no autorizado'], 401);
        exit;
    }
}