<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/registroPaso2.css">
</head>
<body>
    <main class="container">
        <?php include 'seccionBeneficios.php'; ?>

        <section class="form-panel">
            <div class="form-header">
                <a href="../login/login.php" class="back-link">‹ Ya tengo cuenta</a>
                <h1>Crear cuenta en VozPark</h1>
                <p>Es gratuito y toma menos de 2 minutos</p>
            </div>

            <div class="stepper">
                <div class="step active"></div>
                <div class="step active"></div>
                <div class="step"></div>
            </div>

            <form class="register-form">
                <p class="step-label">Paso 2 de 3 · Información personal</p>

                <div class="input-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" placeholder="Contraseña">
                </div>

                <div class="input-group">
                    <label for="password">Confirmar contraseña *</label>
                    <input type="password" id="password" placeholder="Confirmar contraseña">
                </div>

                <div class="input-group">
                    <p>Tu contraseña debe tener:</p>
                    <ul>
                        <li class="check">Al menos 8 caracteres</li>
                        <li class="check">Una letra mayúscula</li>
                        <li class="check">Un número</li>
                    </ul>
                </div>

                <button type="button" class="btn-continue">Continuar →</button>
                <button onclick="history.back()" type="button" class="btn-atras"> ← Atrás</button>
            </form>
        </section>
    </main>
</body>
</html>