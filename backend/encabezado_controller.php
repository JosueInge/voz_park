<?php
// controllers/Admin/EncabezadoAdminController.php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/middleware/auth.php';
require_once dirname(__DIR__, 2) . '/helpers/response.php';
require_once dirname(__DIR__, 2) . '/models/Usuario.php';

class EncabezadoAdminController {

    /**
     * GET /admin/perfil
     * Retorna nombre, rol e institución del administrador autenticado.
     */
    public static function perfil(): void {
        $payload = requireAuth('admin');

        $usuario = Usuario::findById($payload['id']);

        if (!$usuario) {
            error('Sesión inválida', 401);
        }

        success([
            'nombre'      => $usuario['nombre'],
            'rol'         => 'Administrador',
            'institucion' => $usuario['institucion'] ?? 'Alcaldía SS',
            'iniciales'   => self::iniciales($usuario['nombre']),
        ], $usuario['nombre']);
    }

    /** Genera las iniciales del nombre (máx. 2 caracteres). */
    private static function iniciales(string $nombre): string {
        $partes = explode(' ', trim($nombre));
        $ini    = '';
        foreach ($partes as $p) {
            if ($p !== '') $ini .= mb_strtoupper(mb_substr($p, 0, 1));
            if (mb_strlen($ini) === 2) break;
        }
        return $ini;
    }
}