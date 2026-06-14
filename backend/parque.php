<?php
// parque.php — estructura plana

require_once __DIR__ . '/database.php';

class Parque {

    /**
     * Retorna todos los parques con coordenadas y estado de incidencias.
     * Opcionalmente filtra por estado.
     */
    public static function todos(?string $estado = null): array {
        $pdo = getDB();

        $sql = "SELECT
                    p.id,
                    p.nombre,
                    p.latitud,
                    p.longitud,
                    COALESCE(
                        CASE
                            WHEN SUM(CASE WHEN r.urgencia = 'Alta'  AND r.estado != 'Resuelto' THEN 1 ELSE 0 END) > 0 THEN 'Urgente'
                            WHEN SUM(CASE WHEN r.estado IN ('En curso','En revisión')           THEN 1 ELSE 0 END) > 0 THEN 'En revisión'
                            ELSE 'Activo'
                        END,
                        'Activo'
                    ) AS estado_general
                  FROM parques p
                  LEFT JOIN reportes r ON r.parque_id = p.id
                 GROUP BY p.id, p.nombre, p.latitud, p.longitud";

        $params = [];
        if ($estado) {
            $sql  = "SELECT * FROM ({$sql}) sub WHERE sub.estado_general = :estado";
            $params[':estado'] = $estado;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Valida que un parque exista y tenga coordenadas válidas.
     */
    public static function existeConCoordenadas(int $id): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id FROM parques
              WHERE id = :id
                AND latitud  IS NOT NULL AND latitud  BETWEEN -90  AND 90
                AND longitud IS NOT NULL AND longitud BETWEEN -180 AND 180
              LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return (bool)$stmt->fetch();
    }

    /**
     * Busca un parque por nombre (para el buscador del encabezado).
     */
    public static function buscar(string $termino): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, latitud, longitud
               FROM parques
              WHERE nombre LIKE :t
              LIMIT 15"
        );
        $stmt->execute([':t' => '%' . $termino . '%']);
        return $stmt->fetchAll();
    }
}