<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/registroPaso1.css">
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
                <div class="step"></div>
                <div class="step"></div>
            </div>

            <form class="register-form">
                <p class="step-label">Paso 1 de 3 · Información personal</p>

                <div class="input-group">
                    <label for="name">Nombre completo *</label>
                    <input type="text" id="name" placeholder="Ana García">
                </div>

                <div class="input-group">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" placeholder="tu@correo.com">
                </div>

                <div class="input-group">
                    <label for="phone">Número de teléfono *</label>
                    <input type="tel" id="phone" placeholder="+503 7700-0000">
                </div>

                <div class="input-group">
                    <label for="location">Zona de residencia *</label>
                    <select id="location">
                        <option value="" disabled selected>Seleccioná tu zona</option>
                        <option value="san-salvador">San Salvador</option>
                        <option value="la-libertad">La Libertad</option>
                        </select>
                </div>

                <button type="button" class="btn-continue">Continuar →</button>
            </form>
        </section>
    </main>
    <script src="js/registroPaso1.js"></script>
</body>
</html>