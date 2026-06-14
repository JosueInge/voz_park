<?php
// controllers/MapaAdminController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Parque.php';
require_once dirname(__DIR__) . '/models/Incidencia.php';
require_once dirname(__DIR__) . '/models/Personal.php';

class MapaAdminController {

    /**
     * GET /admin/parques
     * GET /admin/parques?estado=Urgente|En revisión
     * Lista parques con coordenadas y estado calculado desde incidencias.
     */
    public static function parques(): void {
        requireAuth('admin');

        $estado  = isset($_GET['estado']) ? trim($_GET['estado']) : null;
        $parques = Parque::todos($estado);

        if (empty($parques)) {
            success([], 'No hay parques registrados');
            return;
        }

        // Validar coordenadas antes de enviar
        $validos = array_filter($parques, fn($p) =>
            is_numeric($p['latitud'])  && (float)$p['latitud']  >= -90  && (float)$p['latitud']  <= 90 &&
            is_numeric($p['longitud']) && (float)$p['longitud'] >= -180 && (float)$p['longitud'] <= 180
        );

        success(array_values($validos), 'Parques obtenidos correctamente');
    }

    /**
     * GET /admin/incidencias/recientes
     * Incidencias más recientes para el panel derecho del mapa.
     */
    public static function incidenciasRecientes(): void {
        requireAuth('admin');

        $incidencias = Incidencia::recientesAdmin(10);

        if (empty($incidencias)) {
            success([], 'No hay incidencias recientes');
            return;
        }

        success($incidencias, 'Incidencias recientes obtenidas correctamente');
    }

    /**
     * GET /admin/personal/campo
     * Personal activo con coordenadas actuales para marcadores en el mapa.
     */
    public static function personalCampo(): void {
        requireAuth('admin');

        $personal = Personal::enCampo();

        if (empty($personal)) {
            success([], 'No hay personal activo en campo');
            return;
        }

        success($personal, 'Personal en campo obtenido correctamente');
    }

    /**
     * GET /admin/personal/disponible
     * Personal disponible para asignación en el detalle de incidencia.
     */
    public static function personalDisponible(): void {
        requireAuth('admin');

        $personal = Personal::disponible();

        if (empty($personal)) {
            success([], 'No hay personal disponible');
            return;
        }

        success($personal, 'Personal disponible obtenido correctamente');
    }

    /**
     * GET /config/maps
     * Retorna la API Key de Google Maps (compartida con ciudadano).
     * Accesible para cualquier usuario autenticado.
     */
    public static function mapsConfig(): void {
        requireAuth();   // cualquier rol

        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        if (!$apiKey || $apiKey === 'YOUR_GOOGLE_MAPS_API_KEY_HERE') {
            error('API Key de Google Maps no configurada en el servidor', 500);
        }

        success(['api_key' => $apiKey], 'Configuración obtenida');
    }
}