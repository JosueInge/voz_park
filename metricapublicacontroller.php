<?php
// controllers/MetricaPublicaController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Metrica.php';

class MetricaPublicaController {

    /**
     * GET /metricas/globales
     * Retorna las métricas estadísticas del sistema para la landing pública.
     * Endpoint abierto — no requiere autenticación.
     */
    public static function globales(): void {

        $metricas = Metrica::globales();

        // Validar que existan datos estadísticos
        $hayDatos = $metricas['parques']    > 0
                 || $metricas['reportes_mes'] > 0
                 || $metricas['resueltas']    > 0
                 || $metricas['ciudadanos']   > 0;

        if (!$hayDatos) {
            success([
                'parques'         => 0,
                'reportes_mes'    => 0,
                'resueltas'       => 0,
                'tiempo_promedio' => 0,
                'ciudadanos'      => 0,
            ], 'Sin datos estadísticos disponibles aún');
            return;
        }

        success($metricas, 'Métricas obtenidas correctamente');
    }
}