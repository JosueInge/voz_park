<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/seccionBeneficios.css">
    <link rel="stylesheet" href="estilos/registroPaso2.css">
    <script src="js/registroPaso2.js" defer></script>
</head>
<body>
    <main class="contenedor">
        <?php include 'seccionBeneficios.php'; ?>

        <section class="panelFormulario">
          <div class="contenidoFormulario">
            <div class="encabezadoFormulario">
                <a href="../login/login.php" class="linkVolver">Ya tengo cuenta</a>
                <h1>Crear cuenta en VozPark</h1>
            </div>

            <div class="pasos">
                <div class="paso active"></div>
                <div class="paso active"></div>
                <div class="paso"></div>
            </div>

            <form class="formuarioRegistro">
                <p class="pasoLabel">Paso 2 de 3 – Creación de contraseña</p>

                <div class="grupoInput">
                    <label for="password">Contraseña *</label>
                    <div class="contenedorContraseña">
                        <input type="password" id="password" placeholder="Escribe tu contraseña">
                        <img id="togglePassword" class="iconoOjo" src="imagenes/ojoCerrado.webp" alt="Mostrar contraseña" tabindex="0">
                    </div>
                </div>

                <div class="grupoInput">
                    <label for="confirmarPassword">Confirmar contraseña *</label>
                    <div class="contenedorContraseña">
                        <input type="password" id="confirmarPassword" placeholder="Escriba nuevamente tu contraseña">
                        <img id="toggleConfirmar" class="iconoOjo" src="imagenes/ojoCerrado.webp" alt="Mostrar contraseña" tabindex="0">
                    </div>
                </div>

                <div class="grupoInput">
                    <div class="inputInforContraseña">
                        <p>Tu contraseña debe tener:</p>
                        <ul>
                            <li class="val-8caracteres">Al menos 8 caracteres</li>
                            <li class="val-mayuscula">Una letra mayúscula</li>
                            <li class="val-numero">Un número</li>
                        </ul>
                    </div>
                </div>

                <button type="button" class="btn-continue">Continuar →</button>
                <button onclick="history.back()" type="button" class="btn-atras"> ← Atrás</button>
            </form>
          </div>
        </section>
    </main>
</body>
</html>