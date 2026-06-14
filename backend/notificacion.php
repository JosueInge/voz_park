<?php
// models/Notificacion.php

require_once dirname(__DIR__) . '/config/database.php';

class Notificacion {

    /**
     * Retorna todas las notificaciones del usuario, opcionalmente filtradas por categoría.
     */
    public static function porUsuario(int $userId, ?string $categoria = null): array {
        $pdo  = getDB();
        $sql  = "SELECT id, categoria, mensaje, enlace,
                        DATE_FORMAT(fecha_hora, '%d %b %Y %H:%i') AS fecha_hora
                   FROM notificaciones
                  WHERE usuario_id = :uid";
        $params = [':uid' => $userId];

        if ($categoria) {
            $sql .= " AND categoria = :cat";
            $params[':cat'] = $categoria;
        }

        $sql .= " ORDER BY fecha_hora DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Elimina una notificación validando que pertenezca al usuario.
     * Retorna true si se eliminó, false si no existe o no pertenece al usuario.
     */
    public static function eliminar(int $id, int $userId): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "DELETE FROM notificaciones
              WHERE id = :id AND usuario_id = :uid"
        );
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Crea una notificación de cambio de estado de reporte.
     * Se llama desde el controlador de reportes al actualizar estado.
     */
    public static function crearCambioEstado(int $userId, int $reporteId, string $nuevoEstado): void {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "INSERT INTO notificaciones (usuario_id, categoria, mensaje, enlace, fecha_hora)
             VALUES (:uid, 'Reportes',
                     :msg,
                     :enlace,
                     NOW())"
        );
        $stmt->execute([
            ':uid'    => $userId,
            ':msg'    => "Tu reporte #{$reporteId} cambió de estado a: {$nuevoEstado}.",
            ':enlace' => "/reportes/{$reporteId}",
        ]);
    }

    /**
     * Crea una notificación de alerta de encuesta próxima a vencer.
     * Se llama desde un cron o proceso automatizado.
     */
    public static function crearAlertaEncuesta(int $userId, int $encuestaId, string $titulo): void {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "INSERT INTO notificaciones (usuario_id, categoria, mensaje, enlace, fecha_hora)
             VALUES (:uid, 'Alertas',
                     :msg,
                     :enlace,
                     NOW())"
        );
        $stmt->execute([
            ':uid'    => $userId,
            ':msg'    => "La encuesta \"{$titulo}\" está próxima a vencer. ¡Participa antes que cierre!",
            ':enlace' => "/encuestas/{$encuestaId}",
        ]);
    }
}