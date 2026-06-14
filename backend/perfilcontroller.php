<?php
// controllers/PerfilController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Usuario.php';

class PerfilController {

    /**
     * GET /perfil
     * Retorna la información completa del usuario autenticado.
     */
    public static function ver(): void {
        $payload = requireAuth('poblador');

        $usuario = Usuario::findById($payload['id']);
        if (!$usuario) {
            error('Usuario no encontrado', 404);
        }

        // Eliminar password del resultado por precaución
        unset($usuario['password']);

        // Estadísticas
        $usuario['estadisticas'] = Usuario::estadisticas($payload['id']);

        success($usuario, 'Perfil obtenido correctamente');
    }

    /**
     * PUT /perfil/foto
     * Actualiza la foto de perfil. Acepta multipart/form-data con campo "foto".
     */
    public static function actualizarFoto(): void {
        $payload = requireAuth('poblador');

        if (empty($_FILES['foto']['tmp_name'])) {
            error('No se recibió ninguna imagen', 422);
        }

        $tmpName  = $_FILES['foto']['tmp_name'];
        $mimeType = mime_content_type($tmpName);
        $size     = $_FILES['foto']['size'];
        $maxBytes = (int)($_ENV['UPLOAD_MAX_MB'] ?? 2) * 1024 * 1024;

        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            error('Solo se permiten imágenes JPG o PNG de máximo 2MB', 422);
        }

        if ($size > $maxBytes) {
            error('Solo se permiten imágenes JPG o PNG de máximo 2MB', 422);
        }

        // Guardar archivo
        $uploadDir = dirname(__DIR__) . '/uploads/perfiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext      = $mimeType === 'image/png' ? 'png' : 'jpg';
        $filename = 'user_' . $payload['id'] . '_' . time() . '.' . $ext;

        if (!move_uploaded_file($tmpName, $uploadDir . $filename)) {
            error('Error al procesar la solicitud', 500);
        }

        $ruta = 'uploads/perfiles/' . $filename;
        $ok   = Usuario::actualizarFoto($payload['id'], $ruta);

        if (!$ok) {
            error('Error al procesar la solicitud', 500);
        }

        success(['foto_perfil' => $ruta], 'Foto de perfil actualizada correctamente');
    }
}