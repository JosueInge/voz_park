<?php
require_once __DIR__ . '/../backend/database.php';
require_once __DIR__ . '/../backend/jwt.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;
$correo = '';

if ($token) {
    $payload = jwtDecode($token);
    if (!$payload || ($payload['purpose'] ?? '') !== 'reset_password') {
        $error = 'El enlace de recuperación es inválido o ya expiró.';
    } else {
        $correo = $payload['correo'] ?? '';
    }
} else {
    $error = 'No se proporcionó un enlace de recuperación válido.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && $correo) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($password === '' || $confirm === '') {
        $error = 'Ambos campos son obligatorios.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $pdo = getDB();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE LOWER(correo) = :correo");
            $stmt->execute([':password' => $hash, ':correo' => $correo]);

            if ($stmt->rowCount() > 0) {
                $success = true;
            } else {
                $error = 'No se encontró una cuenta con ese correo.';
            }
        } catch (Throwable $e) {
            $error = 'Error al restablecer la contraseña. Intentalo de nuevo.';
        }
    }
}
?>
<?php if ($success): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contraseña restablecida - VozPark</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #FAF8F2;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            max-width: 420px;
            box-shadow: 0 4px 20px rgba(0,0,0,.06);
        }
        .card h2 { color: #1A2E0F; font-size: 20px; margin-bottom: 12px; }
        .card p { color: #3B6D11; font-size: 14px; margin-bottom: 20px; }
        .spinner {
            width: 32px; height: 32px;
            border: 3px solid #e2ebd9;
            border-top-color: #3B6D11;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card">
        <h2>Contraseña restablecida</h2>
        <p>Tu contraseña se actualizó correctamente.</p>
        <p style="color:#768a76;font-size:13px;">Redirigiendo al inicio de sesión...</p>
        <div class="spinner"></div>
    </div>
    <script>setTimeout(function(){ window.location.href = '../login/login.php'; }, 2500);</script>
</body>
</html>
<?php else: ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - VozPark</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="contenedor">
        <section class="seccionInformacion">
            <div class="seccionIzquierda">
                <h1>VozPark</h1>
                <p class="subTitulo">Parques urbanos · San Salvador Centro</p>
            </div>
            <div class="beneficios">
                <h3>CON VOZPARK PODES</h3>
                <div class="item-beneficio">
                    <div class="cuadroBeneficio reportar"><img class="tamañoIconos" src="imagenes/reportar.png" alt="Icono de reportar"></div>
                    <div class="textoBeneficio"><strong>Reportar incidencias de los parques</strong></div>
                </div>
                <div class="item-beneficio">
                    <div class="cuadroBeneficio participar"><img class="tamañoIconos" src="imagenes/participar.png" alt="Icono de participar"></div>
                    <div class="textoBeneficio"><strong>Participar en decisiones comunitarias</strong></div>
                </div>
                <div class="item-beneficio">
                    <div class="cuadroBeneficio consultar"><img class="tamañoIconos" src="imagenes/consultar.png" alt="Icono de consultar"></div>
                    <div class="textoBeneficio"><strong>Consultar VozBot, tu asistente IA</strong></div>
                </div>
                <div class="item-beneficio">
                    <div class="cuadroBeneficio verImpacto"><img class="tamañoIconos" src="imagenes/impacto.png" alt="Icono de ver impacto"></div>
                    <div class="textoBeneficio"><strong>Ver el impacto de tu participación</strong></div>
                </div>
            </div>
        </section>
        <main class="seccionLogin">
            <div class="contenedorLogin">
                <a href="../login/login.php" class="linkVolver">
                    <img src="imagenes/izquierda.png" alt="Volver" class="iconoVolver">
                    Volver al inicio de sesión
                </a>
                <h2>Restablecer contraseña</h2>
                <p class="subTituloLogin">Ingresá tu nueva contraseña</p>

                <?php if ($error): ?>
                    <div class="mensaje-error" style="display:block;margin-bottom:16px;text-align:center;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if ($correo): ?>
                <form method="POST" novalidate>
                    <div class="gruposFormulario">
                        <label for="password">Nueva contraseña</label>
                        <div class="contenedorContraseña">
                            <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6" required>
                            <img id="togglePassword" class="iconoOjo" src="imagenes/ojoCerrado.webp" alt="Mostrar contraseña" tabindex="0">
                        </div>
                    </div>
                    <div class="gruposFormulario">
                        <label for="confirm">Confirmar contraseña</label>
                        <div class="contenedorContraseña">
                            <input type="password" id="confirm" name="confirm" placeholder="Repetí la contraseña" minlength="6" required>
                            <img id="toggleConfirm" class="iconoOjo" src="imagenes/ojoCerrado.webp" alt="Mostrar contraseña" tabindex="0">
                        </div>
                    </div>
                    <button type="submit" class="btnIniciarSesion" id="btn-restablecer">Restablecer</button>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        const btn = document.getElementById('btn-restablecer');
        if (btn) {
            btn.addEventListener('pointerdown', function (e) {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                btn.style.setProperty('--wave-x', x + 'px');
                btn.style.setProperty('--wave-y', y + 'px');
                btn.classList.add('btn-wave');
                setTimeout(function() { btn.classList.remove('btn-wave'); }, 500);
            });
            btn.addEventListener('mouseenter', function () { btn.classList.add('btn-hover'); });
            btn.addEventListener('mouseleave', function () { btn.classList.remove('btn-hover'); });
            btn.addEventListener('click', function () { btn.blur(); });
        }

        document.querySelectorAll('.iconoOjo').forEach(function(img) {
            img.addEventListener('click', function () {
                const input = this.parentElement.querySelector('input');
                if (input) {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    this.src = isPassword ? 'imagenes/ojoAbierto.webp' : 'imagenes/ojoCerrado.webp';
                    this.alt = isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña';
                }
            });
        });
    </script>
</body>
</html>
<?php endif; ?>
