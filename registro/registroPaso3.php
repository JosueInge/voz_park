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
    <main class="contenedor">
        <?php include 'seccionBeneficios.php'; ?>

        <section class="panelFormulario">
            <div class="encabezadoFormulario">
                <a href="../login/login.php" class="linkVolver">‹ Ya tengo cuenta</a>
                <h1>Crear cuenta en VozPark</h1>
                <p>Es gratuito y toma menos de 2 minutos</p>
            </div>

            <div class="pasos">
                <div class="paso active"></div>
                <div class="paso active"></div>
                <div class="paso active"></div>
            </div>

            <form class="formularioRegistro">
                <p class="paso-label">Paso 3 de 3 · Información personal</p>

                <div class="grupoInput">
                    <div class="custom-box">
                        <p class="custom-box-title">Revisá tus datos</p>
                        <ul class="custom-box-list">
                            <li class="check">Nombre</li>
                            <li class="check">Correo</li>
                            <li class="check">Lugar de residencia</li>
                        </ul>
                    </div>
                </div>

                <div class="grupoInput">
                    <div class="custom-box custom-box-row">
                        <input type="checkbox" id="confirm" class="checkbox-input">
                        <label for="confirm" class="custom-checkbox-label">
                            Acepto los Términos y Condiciones y la Política de Privacidad de Voz Park
                        </label>
                    </div>
                </div>

                <button onclick="window.location.href='#'" type="button" class="btn-continue">Continuar →</button>
                <button onclick="history.back()" type="button" class="btn-atras"> ← Atrás</button>
            </form>
        </section>
    </main>
</body>
</html>