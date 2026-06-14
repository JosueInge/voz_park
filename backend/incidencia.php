<?php
// incidencia.php — Modelo de Incidencias (estructura plana)
// Las incidencias son los reportes vistos desde el panel admin

require_once __DIR__ . '/database.php';

class Incidencia {

    const URGENCIAS = ['Alta', 'Media', 'Baja'];
    const ESTADOS   = ['Sin asignar', 'Asignado', 'En curso', 'En revisión', 'Resuelto'];

    /**
     * Lista incidencias con búsqueda y filtros combinados.
     * Usada por GET /admin/incidencias
     */
    public static function listar(
        string $busqueda = '',
        string $urgencia = '',
        string $estado   = '',
        string $parque   = ''
    ): array {
        $pdo = getDB();

        $sql = "SELECT
                    r.id,
                    r.tipo_incidencia,
                    p.nombre                                        AS parque,
                    r.urgencia,
                    r.estado,
                    r.descripcion,
                    r.ubicacion_parque,
                    r.nombre_reportante,
                    pm.nombre                                       AS personal_asignado,
                    TO_CHAR(r.fecha_reporte, 'DD Mon YYYY')        AS fecha_reporte,
                    TO_CHAR(r.fecha_reporte, 'DD/MM/YYYY HH24:MI') AS fecha_hora
                FROM reportes r
                JOIN parques p          ON p.id  = r.parque_id
                LEFT JOIN personal_municipal pm ON pm.id = r.personal_id
                WHERE 1=1";

        $params = [];

        // Búsqueda por texto
        if ($busqueda !== '') {
            $sql .= " AND (
                CAST(r.id AS TEXT) ILIKE :busqueda
                OR r.tipo_incidencia ILIKE :busqueda
                OR p.nombre          ILIKE :busqueda
                OR r.nombre_reportante ILIKE :busqueda
            )";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        // Filtro urgencia
        if ($urgencia !== '') {
            $sql .= " AND r.urgencia = :urgencia";
            $params[':urgencia'] = $urgencia;
        }

        // Filtro estado
        if ($estado !== '') {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $estado;
        }

        // Filtro parque
        if ($parque !== '') {
            $sql .= " AND p.nombre = :parque";
            $params[':parque'] = $parque;
        }

        $sql .= " ORDER BY
                    CASE r.urgencia
                        WHEN 'Alta'  THEN 1
                        WHEN 'Media' THEN 2
                        WHEN 'Baja'  THEN 3
                        ELSE 4
                    END,
                    r.fecha_reporte DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Retorna el detalle completo de una incidencia: datos + imágenes + historial.
     * Usada por GET /admin/incidencias/{id}
     */
    public static function detalle(int $id): ?array {
        $pdo  = getDB();

        // Datos principales
        $stmt = $pdo->prepare(
            "SELECT
                r.id,
                r.tipo_incidencia,
                p.nombre                                        AS parque,
                p.latitud,
                p.longitud,
                r.urgencia,
                r.estado,
                r.descripcion,
                r.ubicacion_parque,
                r.nombre_reportante,
                r.personal_id,
                pm.nombre                                       AS personal_asignado,
                pm.especialidad                                 AS personal_especialidad,
                TO_CHAR(r.fecha_reporte, 'DD Mon YYYY')        AS fecha_reporte,
                TO_CHAR(r.updated_at,    'DD Mon YYYY HH24:MI') AS ultima_actualizacion
            FROM reportes r
            JOIN parques p               ON p.id  = r.parque_id
            LEFT JOIN personal_municipal pm ON pm.id = r.personal_id
            WHERE r.id = :id
            LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $incidencia = $stmt->fetch();
        if (!$incidencia) return null;

        // Imágenes adjuntas
        $imgs = $pdo->prepare(
            "SELECT ruta FROM reporte_imagenes
              WHERE reporte_id = :id
              ORDER BY id ASC"
        );
        $imgs->execute([':id' => $id]);
        $incidencia['imagenes'] = array_column($imgs->fetchAll(), 'ruta');

        // Historial de seguimiento
        $hist = $pdo->prepare(
            "SELECT
                tipo_evento,
                descripcion,
                nombre_personal,
                TO_CHAR(fecha_evento, 'DD Mon YYYY HH24:MI') AS fecha
             FROM incidencia_historial
             WHERE reporte_id = :id
             ORDER BY fecha_evento ASC"
        );
        $hist->execute([':id' => $id]);
        $incidencia['historial'] = $hist->fetchAll();

        return $incidencia;
    }

    /**
     * Actualiza estado y personal asignado. Registra evento en historial.
     * Usada por PUT /admin/incidencias/{id}
     */
    public static function actualizar(int $id, string $nuevoEstado, ?int $personalId): bool {
        $pdo = getDB();
        $pdo->beginTransaction();

        try {
            // Obtener nombre del personal si hay asignación
            $nombrePersonal = null;
            if ($personalId) {
                $p = $pdo->prepare("SELECT nombre FROM personal_municipal WHERE id = :id");
                $p->execute([':id' => $personalId]);
                $row = $p->fetch();
                $nombrePersonal = $row['nombre'] ?? null;
            }

            // Actualizar reporte
            $stmt = $pdo->prepare(
                "UPDATE reportes
                    SET estado      = :estado,
                        personal_id = :personal_id,
                        updated_at  = NOW()
                  WHERE id = :id"
            );
            $stmt->execute([
                ':estado'      => $nuevoEstado,
                ':personal_id' => $personalId,
                ':id'          => $id,
            ]);

            // Registrar en historial
            $desc = match($nuevoEstado) {
                'Asignado'    => 'Incidencia asignada' . ($nombrePersonal ? " a {$nombrePersonal}" : ''),
                'En curso'    => 'Atención en curso' . ($nombrePersonal ? " por {$nombrePersonal}" : ''),
                'En revisión' => 'Incidencia en revisión',
                'Resuelto'    => 'Incidencia marcada como resuelta',
                default       => "Estado actualizado a: {$nuevoEstado}",
            };

            $hist = $pdo->prepare(
                "INSERT INTO incidencia_historial
                    (reporte_id, tipo_evento, descripcion, nombre_personal, fecha_evento)
                 VALUES
                    (:rid, :tipo, :desc, :personal, NOW())"
            );
            $hist->execute([
                ':rid'      => $id,
                ':tipo'     => $nuevoEstado,
                ':desc'     => $desc,
                ':personal' => $nombrePersonal,
            ]);

            $pdo->commit();
            return true;

        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
}