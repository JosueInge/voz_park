<?php
// controllers/ReporteController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Reporte.php';

class ReporteController {

    /**
     * GET /reportes/mis-reportes
     * Lista todos los reportes del usuario autenticado.
     */
    public static function misReportes(): void {
        $payload  = requireAuth('poblador');
        $reportes = Reporte::porUsuario($payload['id']);

        if (empty($reportes)) {
            success([], 'Aún no has realizado ningún reporte');
            return;
        }
        success($reportes, 'Reportes obtenidos correctamente');
    }

    /**
     * GET /reportes/{id}
     * Detalle de un reporte (solo del usuario autenticado).
     */
    public static function detalle(int $id): void {
        $payload = requireAuth('poblador');
        $reporte = Reporte::detalle($id, $payload['id']);

        if (!$reporte) {
            error('Reporte no encontrado o no tienes acceso', 404);
        }
        success($reporte, 'Detalle obtenido correctamente');
    }

    /**
     * POST /reportes
     * Crea un nuevo reporte con imágenes. Guarda todo en PostgreSQL
     * dentro de una transacción. El trigger de BD registra automáticamente
     * el evento "Reporte recibido" en incidencia_historial.
     *
     * Espera multipart/form-data con:
     *   tipo_incidencia    string   obligatorio
     *   parque_id          int      obligatorio
     *   urgencia           string   Alta|Media|Baja
     *   descripcion        string   obligatorio
     *   ubicacion_parque   string   obligatorio
     *   nombre_reportante  string   opcional (usa el del token)
     *   imagenes[]         file     1-2 archivos JPG/PNG máx 2 MB c/u
     */
    public static function crear(): void {
        // ── DEBUG TEMPORAL — eliminar en producción ───────────────────────
        $debugInfo = [
            'method'      => $_SERVER['REQUEST_METHOD'],
            'post_keys'   => array_keys($_POST),
            'files_keys'  => array_keys($_FILES),
            'auth_header' => $_SERVER['HTTP_AUTHORIZATION']
                             ?? apache_request_headers()['Authorization']
                             ?? 'NO HEADER',
            'token_presente' => !empty(
                $_SERVER['HTTP_AUTHORIZATION']
                ?? apache_request_headers()['Authorization']
                ?? ''
            ),
        ];
        // ─────────────────────────────────────────────────────────────────

        // En desarrollo: si no hay token usar usuario_id=1 (primer poblador)
        $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
        $payload = null;

        try {
            $payload = requireAuth('poblador');
        } catch (Throwable $e) {
            if ($isDev) {
                // Sin token en desarrollo: usar primer usuario poblador de la BD
                $pdo  = getDB();
                $user = $pdo->query(
                    "SELECT id, nombre FROM usuarios WHERE rol='poblador' LIMIT 1"
                )->fetch();
                if ($user) {
                    $payload = ['id' => $user['id'], 'nombre' => $user['nombre']];
                    $debugInfo['auth_bypass'] = "Usando usuario ID {$user['id']} ({$user['nombre']})";
                } else {
                    jsonResponse([
                        'success' => false,
                        'message' => 'No hay usuarios poblador en la BD',
                        'debug'   => $debugInfo,
                    ], 401);
                }
            } else {
                jsonResponse(['success' => false, 'message' => 'Token inválido'], 401);
            }
        }

        // ── 1. Leer campos del formulario ─────────────────────────────────
        $tipo      = trim($_POST['tipo_incidencia']   ?? '');
        $parqueId  = (int)($_POST['parque_id']         ?? 0);
        $urgencia  = trim($_POST['urgencia']            ?? '');
        $desc      = trim($_POST['descripcion']         ?? '');
        $ubicacion = trim($_POST['ubicacion_parque']    ?? '');
        $nombre    = trim($_POST['nombre_reportante']   ?? '') ?: ($payload['nombre'] ?? '');

        // ── 2. Validaciones ───────────────────────────────────────────────
        $errores = [];

        if ($tipo === '') {
            $errores[] = 'El tipo de incidencia es obligatorio';
        }
        if ($parqueId === 0) {
            $errores[] = 'El parque es obligatorio';
        }
        if ($urgencia === '') {
            $errores[] = 'El nivel de urgencia es obligatorio';
        } elseif (!in_array($urgencia, Reporte::URGENCIAS, true)) {
            $errores[] = 'Nivel de urgencia no válido. Use: Alta, Media o Baja';
        }
        if ($desc === '') {
            $errores[] = 'La descripción es obligatoria';
        }
        if ($ubicacion === '') {
            $errores[] = 'La ubicación exacta es obligatoria';
        }
        if (empty($_FILES['imagenes']['name'][0])) {
            $errores[] = 'Debes seleccionar al menos una imagen';
        }
        if ($parqueId > 0 && !Reporte::parqueValido($parqueId)) {
            $errores[] = 'El parque seleccionado no existe o no tiene coordenadas registradas';
        }

        if (!empty($errores)) {
            jsonResponse([
                'success' => false,
                'message' => 'Error al enviar el reporte',
                'errores' => $errores,
            ], 422);
        }

        // ── 3. Procesar y guardar imágenes en disco ───────────────────────
        $uploadDir = dirname(__DIR__)
                   . DIRECTORY_SEPARATOR
                   . ($_ENV['UPLOAD_DIR'] ?? 'uploads/reportes')
                   . DIRECTORY_SEPARATOR;

        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $maxBytes       = (int)($_ENV['UPLOAD_MAX_MB'] ?? 2) * 1024 * 1024;
        $tiposMime      = ['image/jpeg', 'image/png'];
        $rutasGuardadas = [];

        $files = $_FILES['imagenes'];
        // Normalizar estructura aunque venga un solo archivo
        if (!is_array($files['name'])) {
            $files = array_map(fn($v) => [$v], $files);
        }
        $total = count($files['name']);

        for ($i = 0; $i < min($total, 2); $i++) {
            $tmpName = $files['tmp_name'][$i] ?? '';
            $size    = (int)($files['size'][$i] ?? 0);

            // Verificar que el archivo fue subido correctamente
            if (!is_uploaded_file($tmpName)) {
                error('Error al recibir el archivo ' . ($i + 1), 422);
            }

            // Validar tipo MIME real (no confiar en la extensión del cliente)
            $mime = mime_content_type($tmpName);
            if (!in_array($mime, $tiposMime, true)) {
                error('Solo se permiten imágenes JPG o PNG de máximo 2MB', 422);
            }
            if ($size > $maxBytes) {
                error('Solo se permiten imágenes JPG o PNG de máximo 2MB', 422);
            }

            $ext      = ($mime === 'image/png') ? 'png' : 'jpg';
            $filename = 'rpt_' . $payload['id'] . '_' . uniqid('', true) . '.' . $ext;
            $destino  = $uploadDir . $filename;

            if (!move_uploaded_file($tmpName, $destino)) {
                error('No se pudo guardar la imagen en el servidor', 500);
            }

            $rutaRelativa     = ($_ENV['UPLOAD_DIR'] ?? 'uploads/reportes') . '/' . $filename;
            $rutasGuardadas[] = $rutaRelativa;
        }

        // ── 4. Guardar reporte + imágenes en BD (transacción) ────────────
        try {
            $reporteId = Reporte::crear([
                'usuario_id'        => $payload['id'],
                'tipo_incidencia'   => $tipo,
                'parque_id'         => $parqueId,
                'urgencia'          => $urgencia,
                'descripcion'       => $desc,
                'ubicacion_parque'  => $ubicacion,
                'nombre_reportante' => $nombre,
            ], $rutasGuardadas);

        } catch (RuntimeException $e) {
            // Limpiar imágenes ya guardadas en disco si falló la BD
            foreach ($rutasGuardadas as $ruta) {
                $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . $ruta;
                if (file_exists($abs)) @unlink($abs);
            }
            // DEBUG: mostrar error real en desarrollo
            $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
            jsonResponse([
                'success' => false,
                'message' => 'Error al enviar el reporte',
                'debug'   => $isDev ? $e->getMessage() : null,
            ], 500);
        }

        // ── 5. Respuesta de éxito ─────────────────────────────────────────
        success(
            ['reporte_id' => $reporteId],
            'Reporte enviado correctamente',
            201
        );
    }

    /**
     * GET /incidencias/recientes
     * Incidencias activas recientes para el mapa ciudadano.
     */
    public static function recientes(): void {
        requireAuth('poblador');
        $incidencias = Reporte::recientes(10);

        if (empty($incidencias)) {
            success([], 'No hay incidencias recientes');
            return;
        }
        success($incidencias, 'Incidencias recientes obtenidas');
    }
}