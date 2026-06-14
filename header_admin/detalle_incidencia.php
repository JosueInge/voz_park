<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle de incidencia</title>

    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="detalle_incidencia.css">

</head>

<body>

    <?php include 'header_admin.php'; ?>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">

        <div class="detalle-card">

            <div class="detalle-header">

                <h1>Detalle de incidencia</h1>

                <span class="numero-incidencia">
                    #1045
                </span>

            </div>

            <div class="detalle-grid">

                <div class="campo">

                    <label>Tipo de incidencia</label>

                    <p>Iluminación</p>

                </div>

                <div class="campo">

                    <label>Parque</label>

                    <p>Parque Libertad</p>

                </div>

                <div class="campo">

                    <label>Nivel de urgencia</label>

                    <span class="badge alta">
                        Alta
                    </span>

                </div>

                <div class="campo">

                    <label>Estado</label>

                    <span class="badge estado">
                        Sin asignar
                    </span>

                </div>

            </div>

            <div class="campo-completo">

                <label>Descripción</label>

                <div class="descripcion-box">

                    Se reporta una luminaria dañada en el sector norte del parque. Durante las noches la zona queda completamente oscura.

                </div>

            </div>

            <div class="campo-completo">

                <label>Ubicación exacta</label>

                <p>
                    Sector norte de los juegos infantiles.
                </p>

            </div>

            <div class="campo-completo">

                <label>Reportado por</label>

                <p>
                    María López
                </p>

            </div>

            <div class="campo-completo">

                <label>Evidencias</label>

                <div class="evidencias">

                    <img src="https://via.placeholder.com/250x180"
                         alt="Evidencia 1">

                    <img src="https://via.placeholder.com/250x180"
                         alt="Evidencia 2">

                </div>

            </div>

            <div class="campo-completo">

                <label>Asignar a</label>

                <select>

                    <option>
                        Seleccione personal municipal
                    </option>

                    <option>
                        Ana Flores
                    </option>

                    <option>
                        Carlos Martínez
                    </option>

                    <option>
                        Pedro Ramírez
                    </option>

                </select>

            </div>

            <div class="acciones">

                <button class="btn-asignar">

                    Asignar incidencia

                </button>

            </div>

        </div>

    </main>

    <?php include 'dashboard.php'; ?>


</body>

</html>