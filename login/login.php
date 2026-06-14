<?php
require_once __DIR__ . '/../backend/database.php';
$googleClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        window.VOZPARK_CONFIG = {
            googleClientId: "<?php echo htmlspecialchars($googleClientId, ENT_QUOTES, 'UTF-8'); ?>",
            localAuthEndpoint: "../backend/login_auth.php",
            googleAuthEndpoint: "../backend/google_auth.php",
            postLoginRedirect: "../home_usuarios/inicio_plataforma.html"
        };
    </script>
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
                    <div class="textoBeneficio">
                        <strong>Reportar incidencias de los parques</strong>
                    </div>
                </div>

                <div class="item-beneficio">
                    <div class="cuadroBeneficio participar"><img class="tamañoIconos" src="imagenes/participar.png" alt="Icono de participar"></div>
                    <div class="textoBeneficio">
                        <strong>Participar en decisiones comunitarias</strong>
                    </div>
                </div>

                <div class="item-beneficio">
                    <div class="cuadroBeneficio consultar"><img class="tamañoIconos" src="imagenes/consultar.png" alt="Icono de consultar"></div>
                    <div class="textoBeneficio">
                        <strong>Consultar VozBot, tu asistente IA</strong>
                    </div>
                </div>

                <div class="item-beneficio">
                    <div class="cuadroBeneficio verImpacto"><img class="tamañoIconos" src="imagenes/impacto.png" alt="Icono de ver impacto"></div>
                    <div class="textoBeneficio">
                        <strong>Ver el impacto de tu participación</strong>
                    </div>
                </div>
            </div>
        </section>
        <main class="seccionLogin">
            <div class="contenedorLogin">
                <a href="../home_invitado.php" class="linkVolver">
                  <img src="imagenes/izquierda.png" alt="Volver" class="iconoVolver">
                  Volver al inicio
                </a>
                
                <h2>Bienvenido de nuevo</h2>
                <p class="subTituloLogin">Ingresa a tu cuenta de VozPark</p>

                    <div id="google-signin-container" class="google-signin-container"></div>

                <form novalidate>
                    <div class="gruposFormulario">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico">
                    </div>

                    <div class="gruposFormulario">
                        <label for="password">Contraseña</label>
                        <div class="contenedorContraseña">
                            <input type="password" id="password" name="password" placeholder="Contraseña">
                            <img id="togglePassword" class="iconoOjo" src="imagenes/ojoCerrado.webp" alt="Mostrar contraseña" tabindex="0">
                        </div>
                    </div>

                    <div class="contenedorOlvidasteContraseña">
                      <a href="#" class="olvidasteContraseña">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btnIniciarSesion">Iniciar sesión</button>
                </form>

                <div class="contenedorRegistro">
                <p class="noTienesCuenta">¿No tenés cuenta? <a href="../registro/registroPaso1.php">Regístrate</a></p>
                </div>
            </div>
        </main>
    </div>
    <script src="login.js"></script>
</body>
</html>