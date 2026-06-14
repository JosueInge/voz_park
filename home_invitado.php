<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VozPark – Parques Urbanos El Salvador</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --verde-1:    #1A2E0F;
      --verde-2:    #2D4F1A;
      --verde-3:    #549430;
      --verde-lima: #97C459;
      --verde-borde:#98C459;
      --verde-func: #3B6D11;
      --crema:      #DFFFAA;
      --dorado:     #DAA520;
      --dorado-osc: #B8860B;
      --bg-func:    #FAF8F2;
      --texto:      #070707;
    }

    html { scroll-behavior: smooth; }
    body { font-family: 'Inter', sans-serif; background: var(--bg-func); overflow-x: hidden; }

    /* ── Ripple ── */
    .ripple {
      position: absolute; border-radius: 50%;
      background: rgba(255,255,255,.25);
      transform: scale(0);
      animation: rpl .55s linear;
      pointer-events: none;
    }
    .ripple--gold { background: rgba(218,165,32,.22); }
    @keyframes rpl { to { transform: scale(4); opacity: 0; } }

    /* ════════════════════════════════════════
       1. ENCABEZADO
    ════════════════════════════════════════ */
    .vp-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      height: 60px;
      background: var(--verde-1);
      padding: 0 24px;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .vp-brand { display: flex; flex-direction: column; justify-content: center; }
    .vp-brand__name {
      font-size: 20px; font-weight: 700;
      color: var(--crema); line-height: 1.2; white-space: nowrap;
    }
    .vp-brand__sub {
      font-size: 16px; color: var(--verde-lima);
      line-height: 1.2; white-space: nowrap;
    }

    .vp-header__btns { display: flex; gap: 10px; align-items: center; }

    /* Botón base */
    .vp-btn {
      display: inline-flex; align-items: center; justify-content: center;
      width: 100px; height: 25px; border-radius: 4px;
      font-family: 'Inter', sans-serif; font-size: 12px;
      cursor: pointer; text-decoration: none;
      position: relative; overflow: hidden;
      transition: filter .2s, background .2s, box-shadow .2s;
    }
    .vp-btn:hover  { filter: brightness(1.15); }
    .vp-btn:active { filter: brightness(.88); }

    /* Iniciar sesión */
    .vp-btn--login {
      background: transparent;
      border: 1px solid rgba(151,196,89,.30);
      color: var(--crema);
    }
    .vp-btn--login:hover { background: rgba(151,196,89,.12); }

    /* Crear cuenta — dorado */
    .vp-btn--register {
      background: rgba(184,134,11,.40);
      border: 1px solid var(--dorado);
      color: var(--dorado);
    }
    .vp-btn--register:hover { background: rgba(184,134,11,.62); }

    /* Crear cuenta — variante verde oscuro (CTA inferior) */
    .vp-btn--register-dark {
      background: var(--verde-2);
      border: none;
      color: var(--crema);
    }
    .vp-btn--register-dark:hover {
      background: #1A3D10;
      box-shadow: 0 4px 14px rgba(45,79,26,.35);
    }

    /* Ya tengo cuenta — borde dorado */
    .vp-btn--ya-tengo {
      background: transparent;
      border: 1px solid var(--dorado);
      color: var(--dorado);
    }
    .vp-btn--ya-tengo:hover { background: rgba(218,165,32,.10); }

    /* ════════════════════════════════════════
       2. HERO — Visualización general
    ════════════════════════════════════════ */
    .vp-hero {
      background: linear-gradient(145deg,
        var(--verde-1)  0%,
        var(--verde-2) 50%,
        var(--verde-3) 100%
      );
      padding: 72px 24px 80px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .vp-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2397C459' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
      pointer-events: none;
    }
    .vp-hero__content {
      position: relative; max-width: 720px; margin: 0 auto;
    }
    .vp-hero__title {
      font-size: clamp(28px, 5vw, 36px);
      font-weight: 700; line-height: 1.25; margin-bottom: 20px;
    }
    .vp-hero__title .t1 { color: var(--crema); }
    .vp-hero__title .t2 { color: var(--verde-lima); }

    .vp-hero__desc {
      font-size: 16px; color: var(--crema); line-height: 1.7;
      max-width: 600px; margin: 0 auto 32px;
    }
    .vp-hero__btns {
      display: flex; gap: 12px;
      justify-content: center; flex-wrap: wrap;
    }

    /* ════════════════════════════════════════
       3. ESTADÍSTICAS
    ════════════════════════════════════════ */
    .vp-stats {
      background: #FFFFFF;
      height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .vp-stat {
      flex: 1;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 12px 10px; text-align: center; gap: 4px;
      border-right: 1px solid rgba(152,196,89,.20);
    }
    .vp-stat:last-child { border-right: none; }

    .vp-stat__num { font-size: 36px; font-weight: 700; line-height: 1.1; }
    .vp-stat__num--green  { color: var(--verde-2); }
    .vp-stat__num--golden { color: var(--dorado-osc); }
    .vp-stat__label { font-size: 12px; color: var(--verde-2); }

    /* ════════════════════════════════════════
       4. FUNCIONALIDADES
    ════════════════════════════════════════ */
    .vp-features {
      background: var(--bg-func);
      padding: 56px 24px 64px;
      text-align: center;
    }
    .vp-features__title {
      font-size: 32px; font-weight: 700;
      color: var(--texto); margin-bottom: 10px;
    }
    .vp-features__sub {
      font-size: 16px; color: var(--verde-func);
      margin-bottom: 40px;
      max-width: 620px; margin-left: auto; margin-right: auto;
    }

    .vp-cards-grid {
      display: grid;
      grid-template-columns: repeat(3, 350px);
      gap: 20px;
      justify-content: center;
      max-width: 1120px;
      margin: 0 auto;
    }

    .vp-feat-card {
      width: 350px; min-height: 130px;
      background: #FFFFFF;
      border: 1px solid rgba(152,196,89,.30);
      border-radius: 8px; padding: 14px 16px;
      text-align: left; cursor: pointer;
      position: relative; overflow: hidden;
      transition: border-color .2s, box-shadow .2s, transform .18s;
    }
    .vp-feat-card:hover {
      border-color: var(--dorado);
      box-shadow: 0 6px 20px rgba(218,165,32,.15);
      transform: translateY(-2px);
    }

    .vp-feat-card__icon {
      width: 28px; height: 28px;
      background: rgba(152,196,89,.30);
      border-radius: 5px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 10px;
    }
    .vp-feat-card__icon svg { width: 15px; height: 15px; color: var(--verde-2); }

    .vp-feat-card__title {
      font-size: 16px; font-weight: 600;
      color: #000000; margin-bottom: 6px;
    }
    .vp-feat-card__desc {
      font-size: 12px; color: var(--verde-1); line-height: 1.6;
    }

    /* ════════════════════════════════════════
       5. CÓMO FUNCIONA
    ════════════════════════════════════════ */
    .vp-how {
      background: var(--verde-1);
      padding: 60px 24px;
      text-align: center;
    }
    .vp-how__title {
      font-size: 28px; font-weight: 700;
      color: var(--crema); margin-bottom: 8px;
    }
    .vp-how__sub {
      font-size: 16px; color: var(--verde-lima); margin-bottom: 48px;
    }
    .vp-how__steps {
      display: flex; gap: 32px;
      justify-content: center; flex-wrap: wrap;
      max-width: 900px; margin: 0 auto;
    }
    /* ── Contenedor general de pasos ── */
    .vp-how__steps {
      display: flex;
      flex-direction: column;
      align-items: center;
      max-width: 960px;
      margin: 0 auto;
      gap: 20px;
    }

    /* Fila 1: círculos + líneas conectoras */
    .vp-steps-top {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 0 26px; /* mitad del círculo para que la línea llegue al centro */
    }

     /* Línea continua que pasa POR DETRÁS de todos los círculos */
    .vp-steps-top::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 26px;
      right: 26px;
      height: 2px;
      background: rgba(151,196,89,.50);
      transform: translateY(-50%);
      z-index: 0;
    }
 

    /* Fila inferior: textos alineados */
    .vp-steps-bottom {
      display: flex;
      justify-content: space-between;
      width: 100%;
      padding: 0 0;
    }

    /* Cada columna de paso */
    .vp-step {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      width: 180px;
      flex-shrink: 0;
      position: relative;
      z-index: 1; /* encima de la línea */
    }

    /* Círculo numerado */
    .vp-step__num {
      width: 52px; height: 52px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; font-weight: 700; color: var(--crema);
      flex-shrink: 0;
      position: relative;
      z-index: 1;
    }
    .vp-step__num--1 { background: #3B6D11; }
    .vp-step__num--2 { background: #DAA520; }
    .vp-step__num--3 { background: #0D9488; }
    .vp-step__num--4 { background: #6D28D9; }

    .vp-step__label { font-size: 15px; font-weight: 600; color: var(--crema); text-align: center; }
    .vp-step__desc  { font-size: 13px; color: var(--verde-lima); text-align: center; line-height: 1.5; }

    /* ════════════════════════════════════════
       6. OPCIONES DE ACCESO (CTA)
    ════════════════════════════════════════ */
    .vp-cta {
      background: var(--bg-func);
      padding: 64px 24px;
      text-align: center;
    }
    .vp-cta__title {
      font-size: 24px; font-weight: 700;
      color: var(--verde-1); margin-bottom: 10px;
    }
    .vp-cta__sub {
      font-size: 16px; color: var(--verde-func); margin-bottom: 28px;
    }
    .vp-cta__btns {
      display: flex; gap: 12px;
      justify-content: center; flex-wrap: wrap;
    }

    /* ════════════════════════════════════════
       7. FOOTER
    ════════════════════════════════════════ */
    .vp-footer {
      background: var(--verde-1);
      height: 100px;
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      flex-wrap: wrap; gap: 12px;
    }
    .vp-footer__brand {
      font-size: 16px; font-weight: 700;
      color: var(--crema); white-space: nowrap;
    }
    .vp-footer__copy {
      flex: 1; font-size: 16px; color: var(--crema);
      text-align: center; padding: 0 16px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .vp-footer__links { display: flex; gap: 20px; align-items: center; flex-shrink: 0; }
    .vp-footer__link {
      font-size: 16px; color: var(--crema);
      text-decoration: none; white-space: nowrap;
      position: relative; transition: opacity .2s;
    }
    .vp-footer__link::after {
      content: ''; position: absolute;
      bottom: -2px; left: 0; right: 0;
      height: 1px; background: #FFFFFF;
      transform: scaleX(0); transform-origin: left;
      transition: transform .22s ease;
    }
    .vp-footer__link:hover::after { transform: scaleX(1); }
    .vp-footer__link:hover        { opacity: .85; }

    /* ════════════════════════════════════════
       RESPONSIVO
    ════════════════════════════════════════ */
    @media (max-width: 1100px) {
      .vp-cards-grid { grid-template-columns: repeat(2, 1fr); max-width: 740px; }
      .vp-feat-card  { width: 100%; }
    }
    @media (max-width: 768px) {
      .vp-step-connector { display: none; }
      .vp-how__steps { flex-direction: column; align-items: center; }
    }
    @media (max-width: 640px) {
      .vp-stats { height: auto; flex-wrap: wrap; padding: 20px 0; }
      .vp-stat  { flex: 0 0 50%; padding: 14px 8px; border-bottom: 1px solid rgba(152,196,89,.20); }
      .vp-stat:nth-child(odd) { border-right: 1px solid rgba(152,196,89,.20); }
      .vp-stat:last-child     { border-bottom: none; }
      .vp-cards-grid { grid-template-columns: 1fr; max-width: 400px; }
      .vp-footer { height: auto; padding: 20px; flex-direction: column; text-align: center; }
      .vp-footer__copy  { padding: 0; }
      .vp-footer__links { justify-content: center; flex-wrap: wrap; }
      .vp-brand__sub    { display: none; }
    }
    @media (max-width: 420px) {
      .vp-header { padding: 0 14px; }
      .vp-btn    { width: 92px; font-size: 14px; }
    }
  </style>
</head>
<body>

<!-- ════════════════════════════════════════
  1. ENCABEZADO
════════════════════════════════════════ -->
<header class="vp-header">
  <div class="vp-brand">
    <span class="vp-brand__name">VozPark</span>
    <span class="vp-brand__sub">Parques urbanos – El Salvador</span>
  </div>
  <div class="vp-header__btns">
    <a href="login/login.php"    class="vp-btn vp-btn--login">Iniciar sesión</a>
    <a href="registro/registroPaso1.php" class="vp-btn vp-btn--register">Crear cuenta</a>
  </div>
</header>

<!-- ════════════════════════════════════════
  2. HERO — Visualización general
════════════════════════════════════════ -->
<section class="vp-hero">
  <div class="vp-hero__content">
    <h1 class="vp-hero__title">
      <span class="t1">Tu voz mejora los </span>
      <span class="t2">parques urbanos</span>
    </h1>
    <p class="vp-hero__desc">
      VozPark es la plataforma inteligente de gestión y participación ciudadana que conecta
      a los salvadoreños con la Alcaldía para mejorar los parques de San Salvador en tiempo real.
    </p>
    <div class="vp-hero__btns">
      <a href="registro/registroPaso1.php" class="vp-btn vp-btn--register">Crear cuenta</a>
      <a href="login/login.php"    class="vp-btn vp-btn--login">Iniciar sesión</a>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════
  3. ESTADÍSTICAS
════════════════════════════════════════ -->
<section class="vp-stats" id="stats">
  <div class="vp-stat">
    <span class="vp-stat__num vp-stat__num--green"  id="stat-parques">25</span>
    <span class="vp-stat__label">parques monitoreados</span>
  </div>
  <div class="vp-stat">
    <span class="vp-stat__num vp-stat__num--golden" id="stat-reportes">—</span>
    <span class="vp-stat__label">reportes este mes</span>
  </div>
  <div class="vp-stat">
    <span class="vp-stat__num vp-stat__num--green"  id="stat-resueltos">—</span>
    <span class="vp-stat__label">incidencias resueltas</span>
  </div>
  <div class="vp-stat">
    <span class="vp-stat__num vp-stat__num--green"  id="stat-tiempo">—</span>
    <span class="vp-stat__label">tiempo promedio respuesta</span>
  </div>
  <div class="vp-stat">
    <span class="vp-stat__num vp-stat__num--golden" id="stat-ciudadanos">—</span>
    <span class="vp-stat__label">ciudadanos registrados</span>
  </div>
</section>

<!-- ════════════════════════════════════════
  4. FUNCIONALIDADES
════════════════════════════════════════ -->
<section class="vp-features">
  <h2 class="vp-features__title">Todo lo que necesitas para mejorar tu parque</h2>
  <p class="vp-features__sub">Una plataforma completa para ciudadanos y la administración municipal de San Salvador</p>

  <div class="vp-cards-grid">

    <!-- 1 · Reporta incidencias -->
    <div class="vp-feat-card">
      <div class="vp-feat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div class="vp-feat-card__title">Reporta incidencias</div>
      <div class="vp-feat-card__desc">Reportá incidencias en tiempo real con ubicación exacta. Seguí el estado de tus reportes hasta su resolución.</div>
    </div>

    <!-- 2 · Geolocalización -->
    <div class="vp-feat-card">
      <div class="vp-feat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
      </div>
      <div class="vp-feat-card__title">Geolocalización de incidencias</div>
      <div class="vp-feat-card__desc">Visualiza todos los parques y sus incidencias activas en el mapa. Encontrá el parque más cercano a tu ubicación.</div>
    </div>

    <!-- 3 · Participación ciudadana -->
    <div class="vp-feat-card">
      <div class="vp-feat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="vp-feat-card__title">Participación ciudadana</div>
      <div class="vp-feat-card__desc">Vota en encuestas, envía propuestas de mejora y participa en las decisiones sobre tus parques.</div>
    </div>

    <!-- 4 · VozBot -->
    <div class="vp-feat-card">
      <div class="vp-feat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M12 3v4"/><circle cx="12" cy="7" r="2"/><path d="M8 11v-1a4 4 0 0 1 8 0v1"/><line x1="8" y1="15" x2="8.01" y2="15"/><line x1="12" y1="15" x2="12.01" y2="15"/><line x1="16" y1="15" x2="16.01" y2="15"/></svg>
      </div>
      <div class="vp-feat-card__title">VozBot</div>
      <div class="vp-feat-card__desc">Consulta el estado de reportes, información sobre parques y haz preguntas al asistente inteligente 24/7.</div>
    </div>

    <!-- 5 · Accesibilidad total -->
    <div class="vp-feat-card">
      <div class="vp-feat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><path d="M12 7v6l-3 3"/><path d="M12 13l3 3"/><path d="M9 10H5"/><path d="M19 10h-4"/></svg>
      </div>
      <div class="vp-feat-card__title">Accesibilidad total</div>
      <div class="vp-feat-card__desc">Reportá barreras arquitectónicas, consulta rutas accesibles y personaliza la interfaz según tus necesidades.</div>
    </div>

    <!-- 6 · Datos en tiempo real -->
    <div class="vp-feat-card">
      <div class="vp-feat-card__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="vp-feat-card__title">Datos en tiempo real</div>
      <div class="vp-feat-card__desc">Accede a estadísticas sobre el uso de los parques, tiempos de respuesta y datos que muestran el desempeño del sistema.</div>
    </div>

  </div>
</section>

<!-- ════════════════════════════════════════
  5. CÓMO FUNCIONA
════════════════════════════════════════ -->
<section class="vp-how">
  <h2 class="vp-how__title">¿Cómo funciona VozPark?</h2>
  <p class="vp-how__sub">Tres pasos para mejorar tu parque</p>
  <div class="vp-how__steps">

    <!-- Fila superior: círculos conectados por líneas -->
    <div class="vp-steps-top">
      <div class="vp-step">
        <div class="vp-step__num vp-step__num--1">1</div>
      </div>
      <div class="vp-step">
        <div class="vp-step__num vp-step__num--2">2</div>
      </div>
      <div class="vp-step">
        <div class="vp-step__num vp-step__num--3">3</div>
      </div>
      <div class="vp-step">
        <div class="vp-step__num vp-step__num--4">4</div>
      </div>
    </div>

    <!-- Fila inferior: textos alineados bajo cada círculo -->
    <div class="vp-steps-bottom">
      <div class="vp-step">
        <div class="vp-step__label">Crea tu cuenta</div>
        <div class="vp-step__desc">Registro gratuito en menos de 2 minutos con tu correo electrónico.</div>
      </div>
      <div class="vp-step">
        <div class="vp-step__label">Reporta incidencia</div>
        <div class="vp-step__desc">Fotografía el problema, indica la ubicación y envía el reporte.</div>
      </div>
      <div class="vp-step">
        <div class="vp-step__label">Alcaldía lo gestiona</div>
        <div class="vp-step__desc">El personal municipal recibe la asignación y comienza a trabajar.</div>
      </div>
      <div class="vp-step">
        <div class="vp-step__label">Seguís el estado</div>
        <div class="vp-step__desc">Recibís notificaciones hasta que tu reporte sea resuelto.</div>
      </div>
    </div>

  </div>
</section>

<!-- ════════════════════════════════════════
  6. OPCIONES DE ACCESO
════════════════════════════════════════ -->
<section class="vp-cta">
  <h2 class="vp-cta__title">Comienza a mejorar tu parque hoy</h2>
  <p class="vp-cta__sub">Registro gratuito · Sin tarjeta de crédito · Disponible para todo El Salvador</p>
  <div class="vp-cta__btns">
    <a href="registro/registroPaso1.php" class="vp-btn vp-btn--register-dark">Crear cuenta</a>
    <a href="login/login.php"    class="vp-btn vp-btn--ya-tengo">Ya tengo cuenta</a>
  </div>
</section>

<!-- ════════════════════════════════════════
  7. FOOTER
════════════════════════════════════════ -->
<footer class="vp-footer">
  <span class="vp-footer__brand">VozPark</span>
  <span class="vp-footer__copy">© 2026 VozPark - Alcaldía de San Salvador Centro - Todos los derechos reservados</span>
  <div class="vp-footer__links">
    <a href="terminos.html" class="vp-footer__link">Términos y Condiciones</a>
    <a href="politicas.html" class="vp-footer__link">Políticas de Privacidad</a>
  </div>
</footer>

<!-- ════════════════════════════════════════
  SCRIPTS
════════════════════════════════════════ -->
<script>
/* ── Ripple en botones y tarjetas ── */
function addRipple(el, goldVariant = false) {
  el.addEventListener('click', e => {
    const rect = el.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const r = document.createElement('span');
    r.className = 'ripple' + (goldVariant ? ' ripple--gold' : '');
    r.style.cssText = `width:${size}px;height:${size}px;`
                    + `left:${e.clientX - rect.left - size/2}px;`
                    + `top:${e.clientY  - rect.top  - size/2}px`;
    el.appendChild(r);
    r.addEventListener('animationend', () => r.remove());
  });
}
document.querySelectorAll('.vp-btn').forEach(b => addRipple(b));
document.querySelectorAll('.vp-feat-card').forEach(c => addRipple(c, true));

/* ── Animación de conteo con ease-out ── */
function animateCount(el, target, suffix = '') {
  const end = parseInt(target);
  if (isNaN(end)) return;
  const dur = 1400;
  const t0  = performance.now();
  const tick = now => {
    const p    = Math.min((now - t0) / dur, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.floor(ease * end) + suffix;
    if (p < 1) requestAnimationFrame(tick);
    else el.textContent = end + suffix;
  };
  requestAnimationFrame(tick);
}

/* Activar conteo cuando la sección entra en pantalla */
const statsEl = document.getElementById('stats');
new IntersectionObserver(entries => {
  if (!entries[0].isIntersecting) return;
  document.querySelectorAll('.vp-stat__num').forEach(el => {
    const v = el.textContent.trim();
    if (v !== '—') animateCount(el, v);
  });
}, { threshold: 0.3 }).observe(statsEl);

/* ── Cargar estadísticas del backend — GET /metricas/globales ── */
(async () => {
  try {
    const res = await fetch('backend/metricas/globales');
    if (!res.ok) return;
    const json = await res.json();
    if (!json.success || !json.data) return;

    const d = json.data;
    if (d.parques    > 0) document.getElementById('stat-parques').textContent    = d.parques;
    if (d.reportes_mes > 0) document.getElementById('stat-reportes').textContent = d.reportes_mes;
    if (d.resueltas  > 0) document.getElementById('stat-resueltos').textContent  = d.resueltas;
    if (d.ciudadanos > 0) document.getElementById('stat-ciudadanos').textContent = d.ciudadanos;
    document.getElementById('stat-tiempo').textContent =
      d.tiempo_promedio > 0 ? d.tiempo_promedio + 'h' : '—';

  } catch { /* backend no disponible — los dashes permanecen */ }
})();
</script>

</body>
</html>