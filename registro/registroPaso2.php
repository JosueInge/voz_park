<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - VozPark</title>
    <link rel="stylesheet" href="estilos/registroPaso1.css">
</head>
<body>
    <main class="container">
        <section class="info-panel">
            <header class="brand">
                <h1>VozPark</h1>
                <p>Parques urbanos - El Salvador</p>
            </header>

            <div class="benefits">
                <h2>CON VOZPARK PODES</h2>
                
                <div class="benefit-item">
                    <div class="icon red">📍</div>
                    <div class="text">
                        <strong>Reportar incidencias en tus parques</strong>
                        <span>Tiempo promedio de respuesta: 4.2 horas</span>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="icon blue">🗳️</div>
                    <div class="text">
                        <strong>Participar en decisiones comunitarias</strong>
                        <span>Votaciones, propuestas y encuestas activas</span>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="icon robot">🤖</div>
                    <div class="text">
                        <strong>Consultar VozBot, tu asistente IA</strong>
                        <span>Respuestas en tiempo real sobre tus parques</span>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="icon stats">📊</div>
                    <div class="text">
                        <strong>Ver el impacto de tu participación</strong>
                        <span>Estadísticas y KPIs alineados a ODS</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="form-header">
                <a href="../login/login.php" class="back-link">‹ Ya tengo cuenta</a>
                <h1>Crear cuenta en VozPark</h1>
                <p>Es gratuito y toma menos de 2 minutos</p>
            </div>

            <div class="stepper">
                <div class="step active"></div>
                <div class="step"></div>
                <div class="step"></div>
            </div>

            <form class="register-form">
                <p class="step-label">Paso 1 de 3 · Información personal</p>

                <div class="input-group">
                    <label for="name">Nombre completo *</label>
                    <input type="text" id="name" placeholder="Ana García" required>
                </div>

                <div class="input-group">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" placeholder="tu@correo.com" required>
                </div>

                <div class="input-group">
                    <label for="phone">Número de teléfono *</label>
                    <input type="tel" id="phone" placeholder="+503 7700-0000" required>
                </div>

                <div class="input-group">
                    <label for="location">Zona de residencia *</label>
                    <select id="location" required>
                        <option value="" disabled selected>Seleccioná tu zona</option>
                        <option value="san-salvador">San Salvador</option>
                        <option value="la-libertad">La Libertad</option>
                        </select>
                </div>

                <button type="submit" class="btn-continue">Continuar →</button>
            </form>
        </section>
    </main>
</body>
</html>