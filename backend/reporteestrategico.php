<?php
// models/ReporteEstrategico.php

require_once dirname(__DIR__) . '/config/database.php';

class ReporteEstrategico {

    /**
     * Retorna todos los datos estratégicos del mes actual.
     */
    public static function datosMesActual(): array {
        $pdo = getDB();

        // ── Totales del mes ────────────────────────────────────────────────
        $totalMes = (int) $pdo->query(
            "SELECT COUNT(*)
               FROM reportes
              WHERE fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)
                AND fecha_reporte <  DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'"
        )->fetchColumn();

        $resueltosMes = (int) $pdo->query(
            "SELECT COUNT(*)
               FROM reportes
              WHERE estado = 'Resuelto'
                AND updated_at >= DATE_TRUNC('month', CURRENT_DATE)"
        )->fetchColumn();

        // ── Porcentaje de uso de parques ───────────────────────────────────
        // Parques con al menos 1 reporte activo / total parques * 100
        $totalParques = (int) $pdo->query("SELECT COUNT(*) FROM parques")->fetchColumn();

        $parquesConReporte = (int) $pdo->query(
            "SELECT COUNT(DISTINCT parque_id)
               FROM reportes
              WHERE fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)"
        )->fetchColumn();

        $pctUsoParques = $totalParques > 0
            ? round(($parquesConReporte / $totalParques) * 100, 1)
            : 0;

        // ── Tasa de resolución ─────────────────────────────────────────────
        $tasaResolucion = $totalMes > 0
            ? round(($resueltosMes / $totalMes) * 100, 1)
            : 0;

        // ── Tiempo promedio de respuesta (horas) ───────────────────────────
        $tiempoPromedio = $pdo->query(
            "SELECT ROUND(
                COALESCE(
                    AVG(EXTRACT(EPOCH FROM (updated_at - fecha_reporte)) / 3600),
                    0
                )
             )
               FROM reportes
              WHERE estado = 'Resuelto'
                AND updated_at >= DATE_TRUNC('month', CURRENT_DATE)
                AND updated_at > fecha_reporte"
        )->fetchColumn();

        $pctTiempoRespuesta = (int)($tiempoPromedio ?? 0);

        // ── Incidencias agrupadas por tipo ─────────────────────────────────
        $stmt = $pdo->query(
            "SELECT
                tipo_incidencia                             AS tipo,
                COUNT(*)                                    AS total,
                ROUND(COUNT(*) * 100.0 / NULLIF(
                    (SELECT COUNT(*) FROM reportes
                      WHERE fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)),
                    0
                ), 1)                                       AS porcentaje
               FROM reportes
              WHERE fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)
              GROUP BY tipo_incidencia
              ORDER BY total DESC"
        );
        $porTipo = $stmt->fetchAll();

        // ── Top 5 parques con mayor tasa de incidencias ───────────────────
        $stmt = $pdo->prepare(
            "SELECT
                p.nombre        AS parque,
                COUNT(r.id)     AS total
               FROM parques p
               LEFT JOIN reportes r ON r.parque_id = p.id
                    AND r.fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)
              GROUP BY p.id, p.nombre
              ORDER BY total DESC
              LIMIT 5"
        );
        $stmt->execute();
        $topMayor = $stmt->fetchAll();

        // ── Top 5 parques con menor tasa de incidencias ───────────────────
        $stmt = $pdo->prepare(
            "SELECT
                p.nombre        AS parque,
                COUNT(r.id)     AS total
               FROM parques p
               LEFT JOIN reportes r ON r.parque_id = p.id
                    AND r.fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)
              GROUP BY p.id, p.nombre
              ORDER BY total ASC
              LIMIT 5"
        );
        $stmt->execute();
        $topMenor = $stmt->fetchAll();

        return [
            'mes'                    => date('F Y'),
            'pct_uso_parques'        => $pctUsoParques,
            'pct_tiempo_respuesta'   => $pctTiempoRespuesta,
            'total_reportes_mes'     => $totalMes,
            'tasa_resolucion'        => $tasaResolucion,
            'incidencias_por_tipo'   => $porTipo,
            'top_mayor_incidencias'  => $topMayor,
            'top_menor_incidencias'  => $topMenor,
        ];
    }

    /** Verifica si hay datos en el mes actual. */
    public static function hayDatosMes(): bool {
        $pdo = getDB();
        return (int) $pdo->query(
            "SELECT COUNT(*)
               FROM reportes
              WHERE fecha_reporte >= DATE_TRUNC('month', CURRENT_DATE)"
        )->fetchColumn() > 0;
    }
}