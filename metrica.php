<?php
// models/Metrica.php

require_once dirname(__DIR__) . '/config/database.php';

class Metrica {

    /**
     * Retorna todas las métricas globales del sistema para la landing pública.
     * No requiere autenticación — son datos estadísticos visibles para cualquier visitante.
     */
    public static function globales(): array {
        $pdo = getDB();

        // Total de parques registrados
        $totalParques = (int) $pdo->query(
            "SELECT COUNT(*) FROM parques"
        )->fetchColumn();

        // Total de reportes del mes actual
        $reportesMes = (int) $pdo->query(
            "SELECT COUNT(*)
               FROM reportes
              WHERE fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)"
        )->fetchColumn();

        // Total de incidencias resueltas (histórico)
        $resueltas = (int) $pdo->query(
            "SELECT COUNT(*)
               FROM reportes
              WHERE estado = 'Resuelto'"
        )->fetchColumn();

        // Tiempo promedio de resolución en horas
        // (diferencia entre fecha_reporte y updated_at en reportes resueltos)
        $tiempoPromedio = $pdo->query(
            "SELECT ROUND(
                AVG(
                    EXTRACT(EPOCH FROM (updated_at - fecha_reporte)) / 3600
                )
             )
               FROM reportes
              WHERE estado = 'Resuelto'
                AND updated_at > fecha_reporte"
        )->fetchColumn();

        $tiempoPromedio = $tiempoPromedio ? (int)$tiempoPromedio : 0;

        // Total de usuarios con rol poblador
        $ciudadanos = (int) $pdo->query(
            "SELECT COUNT(*)
               FROM usuarios
              WHERE rol = 'poblador'"
        )->fetchColumn();

        return [
            'parques'          => $totalParques,
            'reportes_mes'     => $reportesMes,
            'resueltas'        => $resueltas,
            'tiempo_promedio'  => $tiempoPromedio,   // en horas
            'ciudadanos'       => $ciudadanos,
        ];
    }
}