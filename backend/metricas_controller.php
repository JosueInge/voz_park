<?php
// controllers/MetricasController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Incidencia.php';
require_once dirname(__DIR__) . '/models/Personal.php';

class MetricasController {

    /**
     * GET /admin/metricas/reportes-activos
     * Total de reportes activos y cuántos tienen urgencia Alta.
     */
    public static function reportesActivos(): void {
        requireAuth('admin');

        $data = Incidencia::metricaReportesActivos();

        success([
            'total'    => $data['total'],
            'urgentes' => $data['urgentes'],
        ], 'Métrica obtenida correctamente');
    }

    /**
     * GET /admin/metricas/resueltos-hoy
     * Número de reportes resueltos en el día actual.
     */
    public static function resueltosHoy(): void {
        requireAuth('admin');

        $total = Incidencia::metricaResueltosHoy();

        success(['resueltos_hoy' => $total], 'Métrica obtenida correctamente');
    }

    /**
     * GET /admin/metricas/personal-activo
     * Número de personal con estado activo en campo.
     */
    public static function personalActivo(): void {
        requireAuth('admin');

        $total = Personal::countActivo();

        success(['personal_activo' => $total], 'Métrica obtenida correctamente');
    }

    /**
     * GET /admin/metricas/parques-cubiertos
     * Parques con cobertura activa vs total registrados.
     */
    public static function parquesCubiertos(): void {
        requireAuth('admin');

        $data = Incidencia::metricaParquesCubiertos();

        success([
            'cubiertos' => $data['cubiertos'],
            'total'     => $data['total'],
        ], 'Métrica obtenida correctamente');
    }
}