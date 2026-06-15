<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - VozPark</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            padding: 14px 28px;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #FFFFFF;
            z-index: 9999;
            opacity: 0;
            transition: opacity .35s ease, transform .35s ease;
            pointer-events: none;
            max-width: 90vw;
            text-align: center;
        }
        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        .toast.toast-success { background: #3B6D11; }
        .toast.toast-error { background: #C45959; }
    </style>
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
                <h2>Recuperar contraseña</h2>
                <p class="subTituloLogin">Ingresá tu correo y te enviaremos instrucciones</p>
                <form id="form-recuperar" novalidate>
                    <div class="gruposFormulario">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" placeholder="Ingresa tu correo aquí">
                    </div>
                    <button type="submit" class="btnIniciarSesion" id="btn-enviar">Enviar</button>
                </form>
            </div>
        </main>
    </div>
    <div id="toast" class="toast"></div>
    <script src="recuperarContraseña.js"></script>
</body>
</html>
