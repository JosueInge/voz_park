<?php
// personal.php — Modelo de Personal Municipal (estructura plana)

require_once __DIR__ . '/database.php';

class Personal {

    /**
     * Verifica que un empleado exista y esté activo.
     * Usado por IncidenciaController al validar asignación.
     */
    public static function existeActivo(int $id): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id FROM personal_municipal
              WHERE id = :id AND estado = 'activo'
              LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetch();
    }

    /**
     * Retorna todo el personal activo disponible para asignar.
     * Usado por GET /admin/personal/disponible
     */
    public static function disponible(): array {
        $pdo  = getDB();
        $stmt = $pdo->query(
            "SELECT id, nombre, especialidad
               FROM personal_municipal
              WHERE estado = 'activo'
              ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Retorna personal activo con coordenadas para el mapa.
     * Usado por GET /admin/personal/campo
     */
    public static function enCampo(): array {
        $pdo  = getDB();
        $stmt = $pdo->query(
            "SELECT
                id,
                nombre,
                especialidad,
                LEFT(nombre, 1)  AS inicial,
                latitud_actual   AS latitud,
                longitud_actual  AS longitud
               FROM personal_municipal
              WHERE estado = 'activo'
                AND latitud_actual  IS NOT NULL
                AND longitud_actual IS NOT NULL
              ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Retorna todos los empleados (activos e inactivos).
     * Usado por reportes estratégicos y listados admin.
     */
    public static function todos(): array {
        $pdo  = getDB();
        $stmt = $pdo->query(
            "SELECT id, nombre, especialidad, estado,
                    latitud_actual, longitud_actual
               FROM personal_municipal
              ORDER BY estado ASC, nombre ASC"
        );
        return $stmt->fetchAll();
    }
}