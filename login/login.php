<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="contenedor">
        <section class="seccionInformacion">
            <div class="seccionIzquierda">
                <h1>VozPark</h1>
                <p class="subTitulo">Parques urbanos · El Salvador</p>
            </div>

            <div class="beneficios">
                <h3>CON VOZPARK PODES</h3>
                
                <div class="item-beneficio">
                    <div class="cuadroBeneficio reportar"><img class="tamañoIconos" src="imagenes/reportar.png" alt="Icono de reportar"></div>
                    <div class="textoBeneficio">
                        <strong>Reportar incidencias en tus parques</strong>
                        <p>Tiempo promedio de respuesta: 4.2 horas</p>
                    </div>
                </div>

                <div class="item-beneficio">
                    <div class="cuadroBeneficio participar"><img class="tamañoIconos" src="imagenes/participar.png" alt="Icono de participar"></div>
                    <div class="textoBeneficio">
                        <strong>Participar en decisiones comunitarias</strong>
                        <p>Votaciones, propuestas y encuestas activas</p>
                    </div>
                </div>

                <div class="item-beneficio">
                    <div class="cuadroBeneficio consultar"><img class="tamañoIconos" src="imagenes/consultar.png" alt="Icono de consultar"></div>
                    <div class="textoBeneficio">
                        <strong>Consultar VozBot, tu asistente IA</strong>
                        <p>Respuestas en tiempo real sobre tus parques</p>
                    </div>
                </div>

                <div class="item-beneficio">
                    <div class="cuadroBeneficio verImpacto"><img class="tamañoIconos" src="imagenes/impacto.png" alt="Icono de ver impacto"></div>
                    <div class="textoBeneficio">
                        <strong>Ver el impacto de tu participación</strong>
                        <p>Estadísticas y KPIs alineados a ODS</p>
                    </div>
                </div>
            </div>
        </section>

        <main class="seccionLogin">
            <div class="contenedorLogin">
                <a href="#" class="linkVolver">‹ Volver al inicio</a>
                
                <h2>Bienvenido de nuevo</h2>
                <p class="subTituloLogin">Ingresá a tu cuenta de VozPark</p>

                <form>
                    <div class="gruposFormulario">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email">
                    </div>

                    <div class="gruposFormulario">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password">
                        <a href="#" class="olvidasteContraseña">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btnIniciarSesion">Iniciar sesión</button>
                </form>

                <p class="noTienesCuenta">¿No tenés cuenta? <a href="#">Registrate gratis →</a></p>

                <div class="apartadoAdmin">
                    <div class="contenedorAdmin">
                        <div class="iconoAdmin"><img class="tamañoIconoAdmin" src="imagenes/seguridad.png" alt="Icono de seguridad"></div>
                        <div class="textoAdmin">
                            <strong>Personal municipal · Portal Admin</strong>
                            <p>Acceso exclusivo para administradores de la Alcaldía</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>