<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/seccionBeneficios.css">
    <link rel="stylesheet" href="estilos/registroPaso2.css">
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
                <div class="paso"></div>
            </div>

            <form class="formuarioRegistro">
                <p class="pasoLabel">Paso 2 de 3 · Información personal</p>

                <div class="grupoInput">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" placeholder="Minimo 8 caracteres">
                </div>

                <div class="grupoInput">
                    <label for="password">Confirmar contraseña *</label>
                    <input type="password" id="password" placeholder="Repite tu contraseña">
                </div>

                <div class="grupoInput">
                    <div class="inputInforContraseña">
                        <p>Tu contraseña debe tener:</p>
                        <ul>
                            <li class="check">Al menos 8 caracteres</li>
                            <li class="check">Una letra mayúscula</li>
                            <li class="check">Un número</li>
                        </ul>
                    </div>
                </div>

                <button onclick="window.location.href='registroPaso3.php'" type="button" class="btn-continue">Continuar →</button>
                <button onclick="history.back()" type="button" class="btn-atras"> ← Atrás</button>
            </form>
        </section>
    </main>
</body>
</html>