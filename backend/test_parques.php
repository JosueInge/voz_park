<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <title>Test Parques</title>
  <style>
    body { font-family: monospace; background:#0f172a; color:#e2e8f0; padding:24px; }
    .ok  { color:#4ade80; } .err { color:#f87171; } .warn{ color:#fbbf24; }
    pre  { background:#1e293b; padding:12px; border-radius:6px; margin-top:8px; white-space:pre-wrap; font-size:13px; }
    button { background:#1e40af; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; margin:4px; font-size:14px; }
  </style>
</head>
<body>
<h2 style="color:#97C459">Test GET /parques</h2>

<?php
// Prueba PHP directa — sin pasar por el router
require_once __DIR__ . '/config/database.php';

echo '<div style="background:#1e293b;padding:16px;border-radius:8px;margin-bottom:16px;">';
echo '<b style="color:#cbd5e1">1. Consulta directa a PostgreSQL</b><br><br>';

try {
    $pdo  = getDB();
    $rows = $pdo->query("SELECT id, nombre, latitud, longitud FROM parques ORDER BY id LIMIT 10")->fetchAll();
    $total = $pdo->query("SELECT COUNT(*) FROM parques")->fetchColumn();

    echo "<span class='ok'>✓ Conexión OK — {$total} parques en BD</span><br><br>";
    foreach ($rows as $p) {
        echo "<span class='ok'>ID {$p['id']}: {$p['nombre']}</span><br>";
    }
    if ($total > 10) echo "<span class='warn'>... y " . ($total - 10) . " más</span><br>";
} catch (Throwable $e) {
    echo "<span class='err'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
echo '</div>';

// Mostrar rutas que usará el JS
$base = dirname($_SERVER['SCRIPT_NAME']); // /voz_park/backend
echo '<div style="background:#1e293b;padding:16px;border-radius:8px;margin-bottom:16px;">';
echo '<b style="color:#cbd5e1">2. Rutas del servidor</b><br><br>';
echo "<span class='warn'>SCRIPT_NAME:  {$_SERVER['SCRIPT_NAME']}</span><br>";
echo "<span class='warn'>Base detectada: {$base}</span><br>";
echo "<span class='warn'>URL completa endpoint: http://{$_SERVER['HTTP_HOST']}{$base}/index.php?path=/parques</span><br>";
echo '</div>';
?>

<div style="background:#1e293b;padding:16px;border-radius:8px;margin-bottom:16px;">
  <b style="color:#cbd5e1">3. Test fetch desde JS — probando distintas URLs</b><br><br>
  <button onclick="test('../backend/index.php?path=/parques')">../backend/index.php?path=/parques</button>
  <button onclick="test('/voz_park/backend/index.php?path=/parques')">/voz_park/backend/index.php?path=/parques</button>
  <button onclick="test('index.php?path=/parques')">index.php?path=/parques (misma carpeta)</button>
  <pre id="out">Haz clic en un botón...</pre>
</div>

<div style="background:#1e293b;padding:16px;border-radius:8px;">
  <b style="color:#cbd5e1">4. Ruta actual del navegador</b><br><br>
  <pre id="location"></pre>
</div>

<script>
document.getElementById('location').textContent =
  'window.location.href:\n' + window.location.href +
  '\n\nwindow.location.pathname:\n' + window.location.pathname;

async function test(url) {
  const out = document.getElementById('out');
  out.textContent = 'Fetching: ' + url + '\n\nEsperando...';
  try {
    const res  = await fetch(url);
    const text = await res.text();
    let json = null;
    try { json = JSON.parse(text); } catch(e) {}

    if (json) {
      out.textContent =
        'URL: ' + url + '\n' +
        'Status: ' + res.status + '\n\n' +
        'success: ' + json.success + '\n' +
        'message: ' + json.message + '\n' +
        'total parques: ' + (json.data?.length ?? 'N/A') + '\n\n' +
        (json.data?.length
          ? 'Primeros 3:\n' + json.data.slice(0,3).map(p => `  ID ${p.id}: ${p.nombre}`).join('\n')
          : 'Sin datos\n\nRespuesta completa:\n' + JSON.stringify(json, null, 2)
        );
    } else {
      out.textContent = 'URL: ' + url + '\nStatus: ' + res.status + '\n\nRespuesta (no es JSON):\n' + text.substring(0, 400);
    }
  } catch(e) {
    out.textContent = 'URL: ' + url + '\n\nError de red: ' + e.message;
  }
}
</script>
</body>
</html>