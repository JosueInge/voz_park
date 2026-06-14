<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <title>VozPark – Diagnóstico</title>
  <style>
    body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 24px; }
    h2   { color: #97C459; margin-bottom: 16px; }
    .ok  { color: #4ade80; }
    .err { color: #f87171; }
    .warn{ color: #fbbf24; }
    .box { background: #1e293b; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
    pre  { margin: 0; white-space: pre-wrap; }
  </style>
</head>
<body>
<h2>🔍 VozPark — Diagnóstico del Backend</h2>

<?php
// ──────────────────────────────────────────────────────────────
// SOLO PARA DIAGNÓSTICO — eliminar este archivo en producción
// ──────────────────────────────────────────────────────────────

$root = dirname(__DIR__);   // backend/

// 1. Verificar .env
echo '<div class="box"><b>1. Archivo .env</b><br>';
$envPath = $root . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    echo '<span class="ok">✓ Encontrado en: ' . htmlspecialchars($envPath) . '</span><br>';
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k] = explode('=', $line, 2);
        $k = trim($k);
        // Mostrar clave pero ocultar contraseña
        $val = ($k === 'DB_PASS') ? '***' : explode('=', $line, 2)[1];
        echo '<span class="warn">' . htmlspecialchars($k) . ' = ' . htmlspecialchars(trim($val)) . '</span><br>';
    }
} else {
    echo '<span class="err">✗ No encontrado en: ' . htmlspecialchars($envPath) . '</span>';
}
echo '</div>';

// Cargar .env manualmente para el diagnóstico
$env = [];
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}

// 2. Verificar conexión a PostgreSQL
echo '<div class="box"><b>2. Conexión a PostgreSQL</b><br>';
$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '5432';
$name = $env['DB_NAME'] ?? 'vozpark';
$user = $env['DB_USER'] ?? 'postgres';
$pass = $env['DB_PASS'] ?? '';
$dsn  = "pgsql:host={$host};port={$port};dbname={$name}";

echo "DSN: <span class='warn'>{$dsn}</span><br>";
echo "Usuario: <span class='warn'>{$user}</span><br>";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
    ]);
    $ver = $pdo->query("SELECT version()")->fetchColumn();
    echo '<span class="ok">✓ Conexión exitosa</span><br>';
    echo '<span class="ok">PostgreSQL: ' . htmlspecialchars(substr($ver, 0, 60)) . '</span><br>';
} catch (PDOException $e) {
    echo '<span class="err">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
    echo '</div></body></html>';
    exit;
}
echo '</div>';

// 3. Verificar tablas
echo '<div class="box"><b>3. Tablas en la BD</b><br>';
$tablas = ['usuarios','parques','reportes','reporte_imagenes',
           'incidencia_historial','notificaciones','personal_municipal','encuestas'];
$stmt = $pdo->query(
    "SELECT table_name FROM information_schema.tables
      WHERE table_schema = 'public' ORDER BY table_name"
);
$existentes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'table_name');

foreach ($tablas as $t) {
    if (in_array($t, $existentes)) {
        $count = $pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        echo "<span class='ok'>✓ {$t} ({$count} registros)</span><br>";
    } else {
        echo "<span class='err'>✗ {$t} — NO EXISTE</span><br>";
    }
}
echo '</div>';

// 4. Verificar carpeta uploads/
echo '<div class="box"><b>4. Carpeta uploads/reportes/</b><br>';
$uploadDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reportes';
if (is_dir($uploadDir)) {
    echo '<span class="ok">✓ Existe: ' . htmlspecialchars($uploadDir) . '</span><br>';
    echo is_writable($uploadDir)
        ? '<span class="ok">✓ Tiene permisos de escritura</span><br>'
        : '<span class="err">✗ SIN permisos de escritura — ejecutar: chmod 755 uploads/reportes/</span><br>';
} else {
    echo '<span class="err">✗ No existe. Créala: mkdir -p uploads/reportes</span><br>';
    // Intentar crearla automáticamente
    if (@mkdir($uploadDir, 0755, true)) {
        echo '<span class="ok">✓ Creada automáticamente</span><br>';
    } else {
        echo '<span class="err">✗ No se pudo crear automáticamente</span><br>';
    }
}
echo '</div>';

// 5. Verificar parques con coordenadas (necesarios para reportes)
echo '<div class="box"><b>5. Parques con coordenadas válidas</b><br>';
$stmt = $pdo->query(
    "SELECT COUNT(*) FROM parques
      WHERE latitud  BETWEEN -90  AND 90
        AND longitud BETWEEN -180 AND 180"
);
$parquesValidos = (int)$stmt->fetchColumn();
if ($parquesValidos > 0) {
    echo "<span class='ok'>✓ {$parquesValidos} parques con coordenadas válidas</span><br>";
    // Mostrar los primeros 3
    $muestra = $pdo->query("SELECT id, nombre FROM parques LIMIT 3")->fetchAll();
    foreach ($muestra as $p) {
        echo "<span class='warn'>  → ID {$p['id']}: {$p['nombre']}</span><br>";
    }
} else {
    echo "<span class='err'>✗ No hay parques registrados — ejecutar los INSERT del database_postgresql.sql</span><br>";
}
echo '</div>';

// 6. Simular INSERT de reporte (sin commit)
echo '<div class="box"><b>6. Prueba de INSERT en reportes (rollback automático)</b><br>';
try {
    // Obtener un usuario y parque de prueba
    $usuarioId = $pdo->query("SELECT id FROM usuarios LIMIT 1")->fetchColumn();
    $parqueId  = $pdo->query("SELECT id FROM parques  LIMIT 1")->fetchColumn();

    if (!$usuarioId) {
        echo "<span class='warn'>⚠ No hay usuarios en la BD — registra uno primero</span><br>";
    } elseif (!$parqueId) {
        echo "<span class='err'>✗ No hay parques — inserta los datos semilla</span><br>";
    } else {
        $pdo->beginTransaction();
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
            ':uid'         => $usuarioId,
            ':tipo'        => 'Diagnóstico',
            ':parque_id'   => $parqueId,
            ':urgencia'    => 'Baja',
            ':descripcion' => 'Reporte de prueba — rollback',
            ':ubicacion'   => 'Zona de prueba',
            ':nombre'      => 'Sistema',
        ]);
        $row = $stmt->fetch();
        $pdo->rollBack();   // NO guarda nada, solo prueba

        if ($row && $row['id']) {
            echo "<span class='ok'>✓ INSERT funciona correctamente — ID generado: {$row['id']} (revertido)</span><br>";
            echo "<span class='ok'>✓ RETURNING id funciona con EMULATE_PREPARES=true</span><br>";
        } else {
            echo "<span class='err'>✗ INSERT ejecutó pero RETURNING id no devolvió valor</span><br>";
        }
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<span class='err'>✗ Error en INSERT: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
echo '</div>';

echo '<div class="box"><span class="ok">✓ Diagnóstico completado</span></div>';
echo '<p style="color:#64748b;font-size:12px">⚠ Elimina este archivo (diagnostico.php) antes de subir a producción.</p>';
?>
</body>
</html>