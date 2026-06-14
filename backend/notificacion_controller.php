<?php
// controllers/NotificacionController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Notificacion.php';

class NotificacionController {

    /**
     * GET /notificaciones
     * GET /notificaciones?categoria=Reportes|Alertas
     * Retorna las notificaciones del usuario autenticado.
     */
    public static function listar(): void {
        $payload   = requireAuth('poblador');
        $categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : null;

        // Validar categoría permitida
        if ($categoria && !in_array($categoria, ['Reportes', 'Alertas'])) {
            error('Categoría no válida. Use: Reportes o Alertas', 422);
        }

        $notificaciones = Notificacion::porUsuario($payload['id'], $categoria);

        if (empty($notificaciones)) {
            success([], 'No tienes notificaciones');
            return;
        }

        success($notificaciones, 'Notificaciones obtenidas correctamente');
    }

    /**
     * DELETE /notificaciones/{id}
     * Elimina una notificación del usuario autenticado.
     */
    public static function eliminar(int $id): void {
        $payload = requireAuth('poblador');

        $eliminada = Notificacion::eliminar($id, $payload['id']);

        if (!$eliminada) {
            error('Notificación no encontrada o no tienes acceso', 404);
        }

        success(null, 'Notificación descartada correctamente');
    }
}