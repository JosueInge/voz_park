<?php
// reporte.php — Modelo + Controlador de Reportes (estructura plana)

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';

// ════════════════════════════════════════
// MODELO
// ════════════════════════════════════════
class Reporte {

    public static function porUsuario(int $userId): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT r.id, r.tipo_incidencia,
                    p.nombre AS parque,
                    r.urgencia, r.estado,
                    TO_CHAR(r.fecha_reporte, 'DD Mon YYYY') AS fecha
               FROM reportes r
               JOIN parques p ON p.id = r.parque_id
              WHERE r.usuario_id = :uid
              ORDER BY r.fecha_reporte DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function detalle(int $reporteId, int $userId): ?array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT r.id, r.tipo_incidencia,
                    p.nombre AS parque,
                    r.urgencia, r.estado, r.descripcion,
                    r.ubicacion_parque, r.nombre_reportante,
                    TO_CHAR(r.fecha_reporte, 'DD Mon YYYY') AS fecha_reporte
               FROM reportes r
               JOIN parques p ON p.id = r.parque_id
              WHERE r.id = :id AND r.usuario_id = :uid
              LIMIT 1"
        );
        $stmt->execute([':id' => $reporteId, ':uid' => $userId]);
        $reporte = $stmt->fetch();
        if (!$reporte) return null;
        $imgs = $pdo->prepare("SELECT ruta FROM reporte_imagenes WHERE reporte_id = :id ORDER BY id");
        $imgs->execute([':id' => $reporteId]);
        $reporte['imagenes'] = array_column($imgs->fetchAll(), 'ruta');
        return $reporte;
    }

    // IMPORTANTE: usar RETURNING id — lastInsertId() no funciona en PostgreSQL
    public static function crear(array $data): int {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "INSERT INTO reportes
                (usuario_id, tipo_incidencia, parque_id, urgencia,
                 descripcion, ubicacion_parque, nombre_reportante,
                 estado, fecha_reporte)
             VALUES
                (:uid, :tipo, :parque_id, :urgencia,
                 :descripcion, :ubicacion, :nombre,
                 'Sin asignar', NOW())
             RETURNING id"
        );
        $stmt->execute([
            ':uid'         => $data['usuario_id'],
            ':tipo'        => $data['tipo_incidencia'],
            ':parque_id'   => $data['parque_id'],
            ':urgencia'    => $data['urgencia'],
            ':descripcion' => $data['descripcion'],
            ':ubicacion'   => $data['ubicacion_parque'],
            ':nombre'      => $data['nombre_reportante'],
        ]);
        $row = $stmt->fetch();
        if (!$row || empty($row['id'])) {
            throw new RuntimeException('No se pudo obtener el ID del reporte');
        }
        return (int)$row['id'];
    }

    public static function guardarImagen(int $reporteId, string $ruta): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "INSERT INTO reporte_imagenes (reporte_id, ruta) VALUES (:rid, :ruta)"
        );
        return $stmt->execute([':rid' => $reporteId, ':ruta' => $ruta]);
    }

    public static function parqueValido(int $parqueId): bool {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id FROM parques
              WHERE id = :id
                AND latitud  BETWEEN -90  AND 90
                AND longitud BETWEEN -180 AND 180
              LIMIT 1"
        );
        $stmt->execute([':id' => $parqueId]);
        return (bool)$stmt->fetch();
    }

    public static function recientes(int $limite = 10): array {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT r.id, r.tipo_incidencia, p.nombre AS parque,
                    r.urgencia, r.estado,
                    EXTRACT(EPOCH FROM (NOW()-r.fecha_reporte))::int/60 AS mins
               FROM reportes r
               JOIN parques p ON p.id = r.parque_id
              WHERE r.estado != 'Resuelto'
              ORDER BY r.fecha_reporte DESC
              LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $m = (int)$row['mins'];
            $row['tiempo'] = $m < 60 ? "Hace {$m}min"
                : ($m < 1440 ? 'Hace '.intdiv($m,60).'h' : 'Hace '.intdiv($m,1440).'d');
            unset($row['mins']);
        }
        return $rows;
    }
}

// ════════════════════════════════════════
// CONTROLADOR
// ════════════════════════════════════════
class ReporteController {

    const URGENCIAS = ['Alta','Media','Baja'];

    public static function misReportes(): void {
        $payload  = self::autenticar();
        $reportes = Reporte::porUsuario($payload['id']);
        empty($reportes)
            ? success([], 'Aún no has realizado ningún reporte')
            : success($reportes, 'Reportes obtenidos correctamente');
    }

    public static function detalle(int $id): void {
        $payload = self::autenticar();
        $reporte = Reporte::detalle($id, $payload['id']);
        $reporte
            ? success($reporte, 'Detalle obtenido correctamente')
            : error('Reporte no encontrado', 404);
    }

    public static function crear(): void {
        $payload = self::autenticar();

        $tipo      = trim($_POST['tipo_incidencia']  ?? '');
        $parqueId  = (int)($_POST['parque_id']        ?? 0);
        $urgencia  = trim($_POST['urgencia']           ?? '');
        $desc      = trim($_POST['descripcion']        ?? '');
        $ubicacion = trim($_POST['ubicacion_parque']   ?? '');
        $nombre    = trim($_POST['nombre_reportante']  ?? '') ?: ($payload['nombre'] ?? 'Ciudadano');

        $errores = [];
        if (!$tipo)      $errores[] = 'El tipo de incidencia es obligatorio';
        if (!$parqueId)  $errores[] = 'El parque es obligatorio';
        if (!$urgencia)  $errores[] = 'El nivel de urgencia es obligatorio';
        elseif (!in_array($urgencia, self::URGENCIAS, true))
            $errores[] = 'Urgencia no válida (Alta, Media, Baja)';
        if (!$desc)      $errores[] = 'La descripción es obligatoria';
        if (!$ubicacion) $errores[] = 'La ubicación exacta es obligatoria';
        if (empty($_FILES['imagenes']['name'][0]))
            $errores[] = 'Debes seleccionar al menos una imagen';
        if ($parqueId && !Reporte::parqueValido($parqueId))
            $errores[] = 'El parque seleccionado no existe';

        if (!empty($errores)) {
            jsonResponse(['success'=>false,'message'=>'Error al enviar el reporte','errores'=>$errores], 422);
        }

        // Guardar imágenes en disco
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reportes' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $maxBytes = (int)($_ENV['UPLOAD_MAX_MB'] ?? 2) * 1024 * 1024;
        $rutas    = [];
        $files    = $_FILES['imagenes'];
        if (!is_array($files['name'])) {
            $files = array_map(fn($v) => [$v], $files);
        }

        for ($i = 0; $i < min(count($files['name']), 2); $i++) {
            $tmp  = $files['tmp_name'][$i] ?? '';
            $size = (int)($files['size'][$i] ?? 0);
            if (!is_uploaded_file($tmp)) continue;
            $mime = mime_content_type($tmp);
            if (!in_array($mime, ['image/jpeg','image/png'])) {
                error('Solo se permiten imágenes JPG o PNG de máximo 2MB', 422);
            }
            if ($size > $maxBytes) {
                error('Solo se permiten imágenes JPG o PNG de máximo 2MB', 422);
            }
            $ext      = $mime === 'image/png' ? 'png' : 'jpg';
            $filename = 'rpt_' . $payload['id'] . '_' . uniqid('',true) . '.' . $ext;
            if (!move_uploaded_file($tmp, $uploadDir . $filename)) {
                error('No se pudo guardar la imagen', 500);
            }
            $rutas[] = 'uploads/reportes/' . $filename;
        }

        // Transacción: insertar reporte + imágenes
        $pdo = getDB();
        $pdo->beginTransaction();
        try {
            $reporteId = Reporte::crear([
                'usuario_id'        => $payload['id'],
                'tipo_incidencia'   => $tipo,
                'parque_id'         => $parqueId,
                'urgencia'          => $urgencia,
                'descripcion'       => $desc,
                'ubicacion_parque'  => $ubicacion,
                'nombre_reportante' => $nombre,
            ]);
            foreach ($rutas as $ruta) {
                Reporte::guardarImagen($reporteId, $ruta);
            }
            $pdo->commit();
            success(['reporte_id' => $reporteId], 'Reporte enviado correctamente', 201);

        } catch (Throwable $e) {
            $pdo->rollBack();
            foreach ($rutas as $ruta) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $ruta);
            $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
            jsonResponse([
                'success' => false,
                'message' => 'Error al enviar el reporte',
                'debug'   => $isDev ? $e->getMessage() : null,
            ], 500);
        }
    }

    public static function recientes(): void {
        $rows = Reporte::recientes(10);
        empty($rows)
            ? success([], 'No hay incidencias recientes')
            : success($rows, 'Incidencias recientes obtenidas');
    }

    private static function autenticar(): array {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $h = apache_request_headers();
            $authHeader = $h['Authorization'] ?? $h['authorization'] ?? '';
        }

        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
            if (file_exists(__DIR__ . '/jwt.php')) {
                require_once __DIR__ . '/jwt.php';
                if (function_exists('jwtDecode')) {
                    $payload = jwtDecode($token);
                    if ($payload) return $payload;
                }
            }
        }

        // Bypass en desarrollo: usar primer usuario poblador
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            $pdo  = getDB();
            $user = $pdo->query(
                "SELECT id, nombre FROM usuarios WHERE rol='poblador' LIMIT 1"
            )->fetch();
            if ($user) return ['id' => (int)$user['id'], 'nombre' => $user['nombre']];
        }

        jsonResponse(['success'=>false,'message'=>'Token de sesión requerido'], 401);
        exit;
    }
}