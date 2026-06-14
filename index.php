<?php
// index.php — Router principal VozPark Backend

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/jwt.php';

// ── CORS ──────────────────────────────────────────────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Método y ruta ─────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = '/vozpark/backend';                      // ajustar según servidor
$path   = '/' . ltrim(str_replace($base, '', $uri), '/');
$path   = rtrim($path, '/') ?: '/';

// ── Segmentos ─────────────────────────────────────────────────────────────────
$segments = explode('/', ltrim($path, '/'));
$seg0     = $segments[0] ?? '';    // primer segmento
$seg1     = $segments[1] ?? '';    // segundo segmento
$seg2     = $segments[2] ?? '';    // tercer segmento (ID normalmente)
$id       = isset($segments[2]) && is_numeric($segments[2]) ? (int)$segments[2] : null;
$id1      = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;

// ── Dispatch ──────────────────────────────────────────────────────────────────
match(true) {

    // ── Métricas públicas (sin autenticación — landing invitados) ────────────
    $method === 'GET' && $path === '/metricas/globales' => (function () {
        require_once __DIR__ . '/controllers/MetricaPublicaController.php';
        MetricaPublicaController::globales();
    })(),

    // ── Encabezado / Búsqueda ────────────────────────────────────────────────
    $method === 'GET'  && $path === '/api/bienvenida' => (function () {
        require_once __DIR__ . '/controllers/EncabezadoController.php';
        EncabezadoController::bienvenida();
    })(),

    $method === 'GET'  && $path === '/api/buscar' => (function () {
        require_once __DIR__ . '/controllers/EncabezadoController.php';
        EncabezadoController::buscar();
    })(),

    // ── Reportes ─────────────────────────────────────────────────────────────
    $method === 'GET'  && $path === '/reportes/mis-reportes' => (function () {
        require_once __DIR__ . '/controllers/ReporteController.php';
        ReporteController::misReportes();
    })(),

    $method === 'GET'  && $seg0 === 'reportes' && $id1 !== null => (function () use ($id1) {
        require_once __DIR__ . '/controllers/ReporteController.php';
        ReporteController::detalle($id1);
    })(),

    $method === 'POST' && $path === '/reportes' => (function () {
        require_once __DIR__ . '/controllers/ReporteController.php';
        ReporteController::crear();
    })(),

    $method === 'GET'  && $path === '/incidencias/recientes' => (function () {
        require_once __DIR__ . '/controllers/ReporteController.php';
        ReporteController::recientes();
    })(),

    // ── Perfil ───────────────────────────────────────────────────────────────
    $method === 'GET' && $path === '/perfil' => (function () {
        require_once __DIR__ . '/controllers/PerfilController.php';
        PerfilController::ver();
    })(),

    $method === 'PUT' && $path === '/perfil/foto' => (function () {
        // PHP no llena $_FILES en PUT; usamos _method override o POST + campo _method
        require_once __DIR__ . '/controllers/PerfilController.php';
        PerfilController::actualizarFoto();
    })(),

    // Soporte PUT via POST + ?_method=PUT
    $method === 'POST' && $path === '/perfil/foto' => (function () {
        require_once __DIR__ . '/controllers/PerfilController.php';
        PerfilController::actualizarFoto();
    })(),

    // ── Parques ──────────────────────────────────────────────────────────────
    $method === 'GET' && $path === '/parques' => (function () {
        require_once __DIR__ . '/controllers/ParqueController.php';
        ParqueController::listar();
    })(),

    $method === 'GET' && $path === '/config/maps' => (function () {
        require_once __DIR__ . '/controllers/ParqueController.php';
        ParqueController::mapsConfig();
    })(),

    // ── Notificaciones ───────────────────────────────────────────────────────
    $method === 'GET'    && $path === '/notificaciones' => (function () {
        require_once __DIR__ . '/controllers/NotificacionController.php';
        NotificacionController::listar();
    })(),

    $method === 'DELETE' && $seg0 === 'notificaciones' && $id1 !== null => (function () use ($id1) {
        require_once __DIR__ . '/controllers/NotificacionController.php';
        NotificacionController::eliminar($id1);
    })(),

    // ════════════════════════════════════════════════════════════════════════
    // RUTAS ADMIN
    // ════════════════════════════════════════════════════════════════════════

    // ── Encabezado admin ─────────────────────────────────────────────────────
    $method === 'GET' && $path === '/admin/perfil' => (function () {
        require_once __DIR__ . '/controllers/EncabezadoAdminController.php';
        EncabezadoAdminController::perfil();
    })(),

    // ── Métricas ─────────────────────────────────────────────────────────────
    $method === 'GET' && $path === '/admin/metricas/reportes-activos' => (function () {
        require_once __DIR__ . '/controllers/MetricasController.php';
        MetricasController::reportesActivos();
    })(),

    $method === 'GET' && $path === '/admin/metricas/resueltos-hoy' => (function () {
        require_once __DIR__ . '/controllers/MetricasController.php';
        MetricasController::resueltosHoy();
    })(),

    $method === 'GET' && $path === '/admin/metricas/personal-activo' => (function () {
        require_once __DIR__ . '/controllers/MetricasController.php';
        MetricasController::personalActivo();
    })(),

    $method === 'GET' && $path === '/admin/metricas/parques-cubiertos' => (function () {
        require_once __DIR__ . '/controllers/MetricasController.php';
        MetricasController::parquesCubiertos();
    })(),

    // ── Reportes admin (pendientes + PDF) ────────────────────────────────────
    $method === 'GET' && $path === '/admin/reportes/pendientes' => (function () {
        require_once __DIR__ . '/controllers/ReporteAdminController.php';
        ReporteAdminController::pendientes();
    })(),

    $method === 'GET' && $path === '/admin/reportes/exportar-pdf' => (function () {
        require_once __DIR__ . '/controllers/ReporteAdminController.php';
        ReporteAdminController::exportarPdf();
    })(),

    // ── Gestión de incidencias ────────────────────────────────────────────────
    $method === 'GET' && $path === '/admin/incidencias' => (function () {
        require_once __DIR__ . '/controllers/IncidenciaController.php';
        IncidenciaController::listar();
    })(),

    $method === 'GET' && $seg0 === 'admin' && $seg1 === 'incidencias'
        && $id !== null => (function () use ($id) {
        require_once __DIR__ . '/controllers/IncidenciaController.php';
        IncidenciaController::detalle($id);
    })(),

    $method === 'PUT' && $seg0 === 'admin' && $seg1 === 'incidencias'
        && $id !== null => (function () use ($id) {
        require_once __DIR__ . '/controllers/IncidenciaController.php';
        IncidenciaController::actualizar($id);
    })(),

    // ── Mapa admin ────────────────────────────────────────────────────────────
    $method === 'GET' && $path === '/admin/parques' => (function () {
        require_once __DIR__ . '/controllers/MapaAdminController.php';
        MapaAdminController::parques();
    })(),

    $method === 'GET' && $path === '/admin/incidencias/recientes' => (function () {
        require_once __DIR__ . '/controllers/MapaAdminController.php';
        MapaAdminController::incidenciasRecientes();
    })(),

    $method === 'GET' && $path === '/admin/personal/campo' => (function () {
        require_once __DIR__ . '/controllers/MapaAdminController.php';
        MapaAdminController::personalCampo();
    })(),

    $method === 'GET' && $path === '/admin/personal/disponible' => (function () {
        require_once __DIR__ . '/controllers/MapaAdminController.php';
        MapaAdminController::personalDisponible();
    })(),

    // ── 404 ─────────────────────────────────────────────────────────────────
    default => error('Ruta no encontrada', 404),
};