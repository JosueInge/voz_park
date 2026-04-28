<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/seccionBeneficios.css">
    <link rel="stylesheet" href="estilos/registroPaso3.css">
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
                <div class="step active"></div>
            </div>

            <form class="register-form">
                <p class="step-label">Paso 3 de 3 · Información personal</p>

                <div class="input-group">
                    <div class="input-info-box">
                        <p>Revisá tus datos</p>
                        <ul>
                            <li class="check">Nombre</li>
                            <li class="check">Correo</li>
                            <li class="check">Lugar de residencia</li>
                        </ul>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-info-box">
                        <input type="checkbox" id="confirm">
                        </input>
                    </div>
                </div>

                    </div>
                </div>

                <button onclick="window.location.href='registroPaso3.php'" type="button" class="btn-continue">Continuar →</button>
                <button onclick="history.back()" type="button" class="btn-atras"> ← Atrás</button>
            </form>
        </section>
    </main>
</body>
</html>