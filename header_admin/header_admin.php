<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <link rel="stylesheet" href="estilosheader_admin.css">

    <title>VozPark Admin</title>

</head>

<body>

<header class="admin-header">

    <div class="header-left">

        <div class="brand-container">
            
        <div class="brand-text">
            <span class="brand-main">VozPark Admin</span>
            <span class="brand-sub">Portal Municipal</span>
        </div>

    </div>

    <div class="header-divider"></div>

    <div class="breadcrumb">

        <div class="breadcrumb-item root">
            <i class="fa-solid fa-house"></i>
            <span>Admin</span>
        </div>

        <span class="separator">›</span>

        <div class="breadcrumb-item current">
            <span id="current-section">Dashboard</span>
        </div>

    </div>

    </div>

    <div class="header-right">

        <div class="admin-profile">

            <div class="avatar">
                JP
            </div>

            <div class="admin-info">
                <span class="admin-name">Juan Pérez</span>
                <span class="admin-role">
                    Administrador - Alcaldía SS
                </span>
            </div>

        </div>

        <button class="logout-btn" id="logoutBtn">
            <i class="fa-solid fa-right-from-bracket"></i>
            Salir
        </button>

    </div>

</header>

<div class="modal-overlay" id="logoutModal">

    <div class="modal-box">

        <h2>¿Estás seguro de cerrar sesión?</h2>

        <div class="modal-actions">

            <button class="cancel-btn" id="cancelBtn">
                Cancelar
            </button>

            <button class="confirm-btn" id="confirmBtn">
                Confirmar
            </button>

        </div>
        
    </div>

</div>

<div class="reportes-container">

    <div class="reportes-header">

        <h3>
            Reportes pendientes de asignación
        </h3>

        <a href="gestion_incidencias.php"
           class="btn-ver-todos">

            Ver todos →

        </a>

    </div>

<script src="header.js"></script>

</body>
</html>
