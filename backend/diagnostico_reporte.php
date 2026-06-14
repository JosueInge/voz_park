<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <title>VozPark – Diagnóstico Reportes</title>
  <style>
    body { font-family: monospace; background:#0f172a; color:#e2e8f0; padding:24px; margin:0; }
    h2   { color:#97C459; margin-bottom:20px; }
    .box { background:#1e293b; border-radius:8px; padding:16px 20px; margin-bottom:14px; border-left:3px solid #334155; }
    .box b { display:block; margin-bottom:8px; color:#cbd5e1; }
    .ok  { color:#4ade80; } .err { color:#f87171; } .warn{ color:#fbbf24; } .info{ color:#93c5fd; }
    button { background:#1e40af; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; margin-top:8px; }
    button:hover { background:#1d3ba0; }
    pre { background:#0f172a; padding:10px; border-radius:4px; overflow-x:auto; font-size:12px; margin-top:8px; }
    input, select, textarea { background:#0f172a; color:#e2e8f0; border:1px solid #334155; border-radius:4px; padding:6px 10px; font-family:monospace; width:100%; margin-top:4px; }
  </style>
</head>
<body>
<h2>🔍 Diagnóstico — POST /reportes</h2>

<?php
// ── Este archivo va en: backend/diagnostico_reporte.php ──

$backendDir = __DIR__;
$envPath    = $backendDir . DIRECTORY_SEPARATOR . '.env';

// Cargar .env
$env = [];
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}

// Conexión
$pdo = null;
try {
    $dsn = "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']}";
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ]);
    $pdo->exec("SET client_encoding TO 'UTF8'");
} catch (PDOException $e) {
    echo '<div class="box"><span class="err">✗ Sin conexión a BD: ' . htmlspecialchars($e->getMessage()) . '</span></div>';
}
?>

<!-- ════════════════════════════════════════
     1. VERIFICAR TABLAS Y DATOS NECESARIOS
════════════════════════════════════════ -->
<?php if ($pdo): ?>
<div class="box">
  <b>1. Datos requeridos para crear un reporte</b>
  <?php
  // Usuarios
  $usuarios = $pdo->query("SELECT id, nombre, correo, rol FROM usuarios LIMIT 5")->fetchAll();
  echo '<br><span class="info">Usuarios disponibles:</span><br>';
  if ($usuarios) {
      foreach ($usuarios as $u) {
          echo "<span class='ok'>  ID {$u['id']}: {$u['nombre']} ({$u['correo']}) — rol: {$u['rol']}</span><br>";
      }
  } else {
      echo "<span class='err'>  ✗ No hay usuarios — registra uno primero</span><br>";
  }

  // Parques
  $parques = $pdo->query("SELECT id, nombre FROM parques ORDER BY id LIMIT 5")->fetchAll();
  $totalParques = (int)$pdo->query("SELECT COUNT(*) FROM parques")->fetchColumn();
  echo "<br><span class='info'>Parques en BD ({$totalParques} total, mostrando primeros 5):</span><br>";
  if ($parques) {
      foreach ($parques as $p) {
          echo "<span class='ok'>  ID {$p['id']}: {$p['nombre']}</span><br>";
      }
  } else {
      echo "<span class='err'>  ✗ No hay parques — ejecuta parques_seed.sql</span><br>";
  }

  // Carpeta uploads
  $uploadDir = $backendDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reportes';
  echo "<br><span class='info'>Carpeta uploads/reportes/:</span><br>";
  if (is_dir($uploadDir)) {
      echo "<span class='ok'>  ✓ Existe</span><br>";
      echo is_writable($uploadDir)
          ? "<span class='ok'>  ✓ Tiene permisos de escritura</span><br>"
          : "<span class='err'>  ✗ Sin permisos de escritura</span><br>";
  } else {
      if (@mkdir($uploadDir, 0755, true)) {
          echo "<span class='ok'>  ✓ Creada automáticamente</span><br>";
      } else {
          echo "<span class='err'>  ✗ No existe y no se pudo crear</span><br>";
      }
  }
  ?>
</div>

<!-- ════════════════════════════════════════
     2. PRUEBA INSERT DIRECTA (sin imágenes)
════════════════════════════════════════ -->
<div class="box">
  <b>2. Prueba INSERT directo en tabla reportes (sin imágenes, con rollback)</b>
  <?php
  $usuarioId = $pdo->query("SELECT id FROM usuarios WHERE rol='poblador' LIMIT 1")->fetchColumn();
  $parqueId  = $pdo->query("SELECT id FROM parques LIMIT 1")->fetchColumn();

  if (!$usuarioId) {
      echo "<span class='warn'>⚠ No hay usuarios con rol 'poblador' — crea uno primero</span><br>";
  } elseif (!$parqueId) {
      echo "<span class='err'>✗ No hay parques</span><br>";
  } else {
      echo "<span class='info'>Usando usuario_id={$usuarioId}, parque_id={$parqueId}</span><br>";
      try {
          $pdo->beginTransaction();
          $stmt = $pdo->prepare(
              "INSERT INTO reportes
                  (usuario_id, tipo_incidencia, parque_id, urgencia,
                   descripcion, ubicacion_parque, nombre_reportante,
                   estado, fecha_reporte)
               VALUES
                  (:uid, :tipo, :parque_id, :urgencia,
                   :desc, :ubic, :nombre,
                   'Sin asignar', NOW())
               RETURNING id"
          );
          $stmt->execute([
              ':uid'      => $usuarioId,
              ':tipo'     => 'Diagnóstico',
              ':parque_id'=> $parqueId,
              ':urgencia' => 'Baja',
              ':desc'     => 'Prueba de inserción directa',
              ':ubic'     => 'Zona de prueba',
              ':nombre'   => 'Sistema',
          ]);
          $row = $stmt->fetch();
          $pdo->rollBack();

          if ($row && $row['id']) {
              echo "<span class='ok'>✓ INSERT funciona — ID generado: {$row['id']} (revertido)</span><br>";
              echo "<span class='ok'>✓ RETURNING id funciona correctamente</span><br>";
          } else {
              echo "<span class='err'>✗ INSERT ejecutó pero no devolvió ID</span><br>";
          }
      } catch (Throwable $e) {
          if ($pdo->inTransaction()) $pdo->rollBack();
          echo "<span class='err'>✗ Error en INSERT: " . htmlspecialchars($e->getMessage()) . "</span><br>";
      }
  }
  ?>
</div>

<!-- ════════════════════════════════════════
     3. SIMULAR POST /reportes COMPLETO
════════════════════════════════════════ -->
<div class="box">
  <b>3. Simular envío real del formulario — POST /reportes</b>
  <p style="color:#94a3b8;font-size:12px;margin:6px 0;">
    Esto envía una petición real al endpoint con una imagen de prueba generada en memoria.
  </p>
  <button onclick="simularEnvio()">▶ Ejecutar prueba de envío real</button>
  <pre id="resultado-envio">Esperando...</pre>
</div>

<!-- ════════════════════════════════════════
     4. ÚLTIMOS REPORTES EN BD
════════════════════════════════════════ -->
<div class="box">
  <b>4. Últimos 5 reportes en la BD</b>
  <?php
  $reportes = $pdo->query(
      "SELECT r.id, r.tipo_incidencia, p.nombre AS parque,
              r.urgencia, r.estado,
              TO_CHAR(r.fecha_reporte, 'DD/MM/YYYY HH24:MI') AS fecha
         FROM reportes r
         JOIN parques p ON p.id = r.parque_id
        ORDER BY r.fecha_reporte DESC
        LIMIT 5"
  )->fetchAll();

  if ($reportes) {
      echo "<br>";
      foreach ($reportes as $r) {
          echo "<span class='ok'>ID {$r['id']}: {$r['tipo_incidencia']} | {$r['parque']} | {$r['urgencia']} | {$r['estado']} | {$r['fecha']}</span><br>";
      }
  } else {
      echo "<span class='warn'>⚠ No hay reportes en la BD aún</span><br>";
  }
  ?>
</div>

<!-- ════════════════════════════════════════
     5. VERIFICAR TRIGGER
════════════════════════════════════════ -->
<div class="box">
  <b>5. Triggers de la tabla reportes</b>
  <?php
  $triggers = $pdo->query(
      "SELECT trigger_name, event_manipulation, action_timing
         FROM information_schema.triggers
        WHERE event_object_table = 'reportes'
          AND trigger_schema = 'public'"
  )->fetchAll();

  if ($triggers) {
      foreach ($triggers as $t) {
          echo "<span class='ok'>✓ {$t['trigger_name']} — {$t['action_timing']} {$t['event_manipulation']}</span><br>";
      }
  } else {
      echo "<span class='warn'>⚠ No hay triggers en la tabla reportes</span><br>";
      echo "<span class='warn'>→ Ejecuta el bloque de triggers del database_postgresql.sql</span><br>";
  }
  ?>
</div>

<?php endif; ?>

<div class="box" style="border-left-color:#64748b;">
  <span style="color:#64748b;font-size:12px;">⚠ Elimina este archivo antes de subir a producción.</span>
</div>

<script>
async function simularEnvio() {
  const out = document.getElementById('resultado-envio');
  out.textContent = 'Enviando...';

  try {
    // Obtener primer usuario y parque de la BD via PHP vars embebidos
    const usuarioId = <?= $pdo ? ($pdo->query("SELECT id FROM usuarios WHERE rol='poblador' LIMIT 1")->fetchColumn() ?: 'null') : 'null' ?>;
    const parqueId  = <?= $pdo ? ($pdo->query("SELECT id FROM parques LIMIT 1")->fetchColumn() ?: 'null') : 'null' ?>;

    if (!usuarioId || !parqueId) {
      out.textContent = '✗ No hay usuario poblador o parques disponibles.';
      return;
    }

    // Crear imagen PNG mínima válida (1x1 pixel rojo) en base64
    const pngB64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==';
    const byteString = atob(pngB64);
    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);
    for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
    const blob = new Blob([ab], { type: 'image/png' });

    const fd = new FormData();
    fd.append('tipo_incidencia',   'Iluminación');
    fd.append('parque_id',         parqueId);
    fd.append('urgencia',          'Media');
    fd.append('descripcion',       'Reporte de prueba desde diagnóstico. Lámpara dañada en sector norte.');
    fd.append('ubicacion_parque',  'Sector norte, entrada principal');
    fd.append('nombre_reportante', 'Sistema Diagnóstico');
    fd.append('imagenes[]',        blob, 'prueba.png');

    // Token del localStorage (si existe)
    const token = localStorage.getItem('vp_token') ?? '';

    const res = await fetch('index.php?path=/reportes', {
      method: 'POST',
      headers: token ? { 'Authorization': 'Bearer ' + token } : {},
      body: fd,
    });

    // Intentar también ruta directa
    const res2 = await fetch('reportes', {
      method: 'POST',
      headers: token ? { 'Authorization': 'Bearer ' + token } : {},
      body: fd,
    }).catch(() => null);

    const text  = await res.text();
    const text2 = res2 ? await res2.text() : 'N/A';

    let json = {};
    try { json = JSON.parse(text); } catch(e) {}

    out.textContent = `=== Intento 1: index.php?path=/reportes ===
Status: ${res.status}
Respuesta: ${JSON.stringify(json, null, 2)}

=== Intento 2: /reportes directo ===
Status: ${res2?.status ?? 'N/A'}
Respuesta: ${text2}`;

  } catch(e) {
    out.textContent = '✗ Error: ' + e.message;
  }
}
</script>
</body>
</html>