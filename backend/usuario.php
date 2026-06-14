<?php
// models/Usuario.php

require_once dirname(__DIR__) . '/config/database.php';

class Usuario {

    /**
     * Busca un usuario por ID. Retorna el registro o null.
     */
    public static function findById(int $id): ?array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, correo, telefono, zona, foto_perfil, rol
               FROM usuarios
              WHERE id = :id
              LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Busca un usuario por correo (para login).
     */
    public static function findByEmail(string $correo): ?array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, correo, telefono, zona, foto_perfil, rol, password
               FROM usuarios
              WHERE correo = :correo
              LIMIT 1"
        );
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Estadísticas del usuario: total reportes, resueltos, parques únicos.
     */
    public static function estadisticas(int $userId): array {
        $pdo = getDB();

        $stmt = $pdo->prepare(
            "SELECT
                COUNT(*) AS total_reportes,
                SUM(CASE WHEN estado = 'Resuelto' THEN 1 ELSE 0 END) AS resueltos,
                COUNT(DISTINCT parque_id) AS parques
               FROM reportes
              WHERE usuario_id = :id"
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch() ?: ['total_reportes' => 0, 'resueltos' => 0, 'parques' => 0];
    }

    /**
     * Actualiza la foto de perfil.
     */
    public static function actualizarFoto(int $userId, string $rutaFoto): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "UPDATE usuarios SET foto_perfil = :foto WHERE id = :id"
        );
        return $stmt->execute([':foto' => $rutaFoto, ':id' => $userId]);
    }

    /**
     * Búsqueda parcial insensible a mayúsculas (para el encabezado).
     */
    public static function buscar(string $termino): array {
        $pdo  = getDB();
        $like = '%' . $termino . '%';
        $stmt = $pdo->prepare(
            "SELECT id, nombre, correo
               FROM usuarios
              WHERE nombre LIKE :t OR correo LIKE :t
              LIMIT 20"
        );
        $stmt->execute([':t' => $like]);
        return $stmt->fetchAll();
    }
}