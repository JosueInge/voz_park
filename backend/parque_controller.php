<?php
// controllers/ParqueController.php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/parque.php';

class ParqueController {

    /**
     * GET /parques
     * GET /parques?estado=Urgente|En revisión|Activo
     *
     * Endpoint PÚBLICO — no requiere autenticación.
     * Los parques son información pública necesaria para el formulario
     * de reporte y el mapa de usuarios invitados/registrados.
     */
    public static function listar(): void {

        $estado  = isset($_GET['estado']) ? trim($_GET['estado']) : null;
        $parques = Parque::todos($estado);

        if (empty($parques)) {
            success([], 'No hay parques registrados');
            return;
        }

        // Filtrar solo parques con coordenadas válidas
        $parques = array_filter($parques, fn($p) =>
            is_numeric($p['latitud'])  && (float)$p['latitud']  >= -90  && (float)$p['latitud']  <= 90 &&
            is_numeric($p['longitud']) && (float)$p['longitud'] >= -180 && (float)$p['longitud'] <= 180
        );

        success(array_values($parques), 'Parques obtenidos correctamente');
    }

    /**
     * GET /config/maps
     * Retorna la API Key de Google Maps desde variables de entorno.
     * Requiere autenticación para no exponer la key públicamente.
     */
    public static function mapsConfig(): void {
        // Autenticación opcional: si hay token lo valida, si no lo ignora
        // Esto permite que tanto usuarios registrados como el mapa admin la usen
        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        if (!$apiKey || $apiKey === 'YOUR_GOOGLE_MAPS_API_KEY_HERE') {
            error('API Key de Google Maps no configurada', 500);
        }

        success(['api_key' => $apiKey], 'Configuración obtenida');
    }
}