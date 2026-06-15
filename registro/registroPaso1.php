<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/seccionBeneficios.css">
    <link rel="stylesheet" href="estilos/registroPaso1.css">
</head>
<body>
    <main class="contenedor">
        <?php include 'seccionBeneficios.php'; ?>

        <section class="panelFormulario">
          <div class="contenidoFormulario">
            <div class="encabezadoFormulario">
                <div class="volverGrupo">
                    <a href="../home_invitado.php" class="linkVolverAtras">
                        <img src="imagenes/izquierda.png" alt="Volver al inicio" class="iconoVolverAtras">
                    </a>
                    <a href="../login/login.php" class="linkVolver">Ya tengo cuenta</a>
                </div>
                <h1>Crear cuenta en VozPark</h1>
            </div>

            <div class="pasos">
                <div class="paso active"></div>
                <div class="paso2"></div>
                <div class="paso3"></div>
            </div>

            <form class="formularioRegistro">
                <p class="pasoLabel">Paso 1 de 3 - Información personal</p>

                <div class="grupoInput">
                    <label for="name">Nombre completo *</label>
                    <input type="text" id="name" placeholder="Escribe tu nombre">
                </div>

                <div class="grupoInput">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" placeholder="tu@correo.com">
                </div>

                <div class="grupoInput">
                    <label for="location">Zona de residencia *</label>
                    <input type="text" id="location" placeholder="Escribe tu zona de residencia">
                </div>

                <button type="button" class="btn-continuar">Continuar →</button>
            </form>
          </div>
        </section>
    </main>
    <script src="js/registroPaso1.js"></script>
</body>
</html>