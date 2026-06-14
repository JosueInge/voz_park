<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VozPark – Parques Urbanos El Salvador</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      background: #F4FFF0;
      min-height: 100vh;
    }

    /* ═══════════════════════════════ HEADER ═══════════════════════════════ */
    .vp-header {
      display: flex;
      align-items: center;
      width: 100%;
      height: 60px;
      background: #1A2E0F;
      padding: 0 20px;
      gap: 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    /* Brand */
    .vp-brand {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding-right: 16px;
      flex-shrink: 0;
    }
    .vp-brand__name {
      font-size: 20px;
      font-weight: 700;
      color: #DFFFAA;
      line-height: 1.2;
      white-space: nowrap;
    }
    .vp-brand__sub {
      font-size: 16px;
      color: #97C459;
      line-height: 1.2;
      white-space: nowrap;
    }

    /* Divider */
    .vp-divider {
      width: 1px;
      height: 40px;
      background: #F4FAEA;
      flex-shrink: 0;
      margin-right: 16px;
      opacity: 0.55;
    }

    /* Nav section text */
    .vp-nav-section {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 16px;
      color: #FFFFFF;
      white-space: nowrap;
      margin-right: 16px;
      flex-shrink: 0;
    }
    .vp-nav-section svg {
      width: 16px;
      height: 16px;
      color: #97C459;
      flex-shrink: 0;
    }

    /* Search */
    .vp-search-wrap {
      position: relative;
      width: 160px;
      flex-shrink: 0;
      margin-right: 14px;
    }
    .vp-search-wrap svg {
      position: absolute;
      left: 8px;
      top: 50%;
      transform: translateY(-50%);
      width: 12px;
      height: 12px;
      color: #FFFFFF;
      pointer-events: none;
    }
    .vp-search {
      width: 100%;
      height: 25px;
      background: transparent;
      border: 1px solid rgba(151, 196, 89, 0.30);
      border-radius: 4px;
      padding: 0 8px 0 26px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #FFFFFF;
      outline: none;
      transition: border-color .2s;
    }
    .vp-search::placeholder { color: rgba(255,255,255,0.50); }
    .vp-search:focus { border-color: rgba(151,196,89,.70); }

    /* Spacer pushes right side */
    .vp-spacer { flex: 1; }

    /* Notification bell */
    .vp-bell {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 14px;
      cursor: pointer;
      flex-shrink: 0;
    }
    .vp-bell svg {
      width: 14px;
      height: 18px;
      color: #97C459;
    }
    .vp-bell__dot {
      position: absolute;
      top: 0;
      right: -1px;
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: #E85D5D;
    }

    /* User info */
    .vp-user {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-right: 14px;
      flex-shrink: 0;
    }
    .vp-user__avatar {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #3B6D11;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 9px;
      font-weight: 700;
      color: #DFFFAA;
      overflow: hidden;
      flex-shrink: 0;
    }
    .vp-user__avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .vp-user__name {
      font-family: 'Open Sans', sans-serif;
      font-size: 20px;
      font-weight: 600;
      color: #FFFFFF;
      white-space: nowrap;
    }

    /* Logout button */
    .vp-logout {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 20px;
      background: #F4FAEA;
      border: none;
      border-radius: 4px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #DFFFAA;
      cursor: pointer;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
      transition: background .2s;
    }
    .vp-logout:hover { background: #e2f0d6; }

    /* Ripple */
    .ripple {
      position: absolute;
      border-radius: 50%;
      background: rgba(63, 150, 18, 0.25);
      transform: scale(0);
      animation: ripple-anim .55s linear;
      pointer-events: none;
    }
    @keyframes ripple-anim { to { transform: scale(4); opacity: 0; } }

    /* ═══════════════════════════════ NO RESULTS ═══════════════════════════════ */
    .vp-no-results {
      display: none;
      position: fixed;
      inset: 0;
      top: 60px;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #FFFFFF;
      font-family: 'Inter', sans-serif;
      text-align: center;
      background: rgba(26,46,15,0.75);
      z-index: 50;
      pointer-events: none;
    }
    .vp-no-results.visible { display: flex; }

    /* ═══════════════════════════════ MODAL ═══════════════════════════════ */
    .vp-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.60);
      z-index: 999;
      align-items: center;
      justify-content: center;
    }
    .vp-modal-overlay.active { display: flex; }

    .vp-modal {
      background: #1A2E0F;
      border: 1px solid #3B6D11;
      border-radius: 12px;
      padding: 28px 32px 24px;
      width: 340px;
      max-width: 90vw;
      display: flex;
      flex-direction: column;
      gap: 22px;
    }
    .vp-modal__title {
      font-family: 'Arial', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: #FFFFFF;
      text-align: center;
      line-height: 1.4;
    }
    .vp-modal__actions {
      display: flex;
      justify-content: space-between;
      gap: 12px;
    }
    .vp-modal__btn {
      flex: 1;
      height: 40px;
      border: none;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      color: #DFFFAA;
      background: #3B6D11;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: filter .2s;
    }
    .vp-modal__btn:hover { filter: brightness(1.15); }
    .vp-modal__btn:active { filter: brightness(0.90); }

    /* ═══════════════════════════════ HERO ═══════════════════════════════ */
    .vp-hero {
      background: linear-gradient(160deg, #1A2E0F 0%, #2D4A1A 45%, #3B6D11 100%);
      padding: 60px 24px 48px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .vp-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2397C459' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
      pointer-events: none;
    }
    .vp-hero__tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(151,196,89,.15);
      border: 1px solid rgba(151,196,89,.35);
      border-radius: 20px;
      padding: 4px 14px;
      font-size: 13px;
      color: #97C459;
      margin-bottom: 18px;
    }
    .vp-hero__tag::before {
      content: '';
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #97C459;
      animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
      0%,100% { opacity:1; transform:scale(1); }
      50% { opacity:.4; transform:scale(1.4); }
    }
    .vp-hero__title {
      font-size: clamp(28px, 5vw, 48px);
      font-weight: 700;
      color: #DFFFAA;
      margin-bottom: 12px;
      line-height: 1.15;
      position: relative;
    }
    .vp-hero__sub {
      font-size: 16px;
      color: #97C459;
      margin-bottom: 32px;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
      position: relative;
    }
    .vp-hero__cta {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #97C459;
      color: #1A2E0F;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      font-weight: 700;
      border: none;
      border-radius: 6px;
      padding: 12px 28px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: background .2s, box-shadow .2s;
    }
    .vp-hero__cta:hover { background: #DFFFAA; box-shadow: 0 6px 20px rgba(151,196,89,.40); }
    .vp-hero__cta svg { width:18px; height:18px; }

    /* ═══════════════════════════════ PARKS GRID ═══════════════════════════════ */
    .vp-section {
      padding: 40px 24px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .vp-section__head {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 8px;
    }
    .vp-section__title {
      font-size: 22px;
      font-weight: 700;
      color: #1A2E0F;
    }
    .vp-section__count {
      font-size: 14px;
      color: #557097;
    }

    .vp-parks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 18px;
    }

    /* Park card */
    .vp-park-card {
      background: #FFFFFF;
      border: 1px solid rgba(151,196,89,.35);
      border-radius: 10px;
      overflow: hidden;
      cursor: pointer;
      transition: transform .18s, box-shadow .18s;
      display: flex;
      flex-direction: column;
    }
    .vp-park-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 28px rgba(59,109,17,.15);
    }
    .vp-park-card__img {
      height: 130px;
      background: linear-gradient(135deg, #2D4A1A, #3B6D11);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .vp-park-card__img svg {
      width: 48px; height: 48px;
      color: rgba(151,196,89,.45);
    }
    .vp-park-card__badge {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 11px;
      padding: 2px 8px;
      border-radius: 10px;
      font-weight: 600;
    }
    .badge-urgente  { background: rgba(232,93,93,.25); color: #E85D5D; border:1px solid rgba(232,93,93,.40); }
    .badge-activo   { background: rgba(151,196,89,.20); color: #3B6D11; border:1px solid rgba(151,196,89,.40); }
    .badge-revision { background: rgba(196,175,89,.20); color: #8A6E1A; border:1px solid rgba(196,175,89,.40); }

    .vp-park-card__body {
      padding: 14px 16px 16px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex: 1;
    }
    .vp-park-card__name {
      font-size: 16px;
      font-weight: 700;
      color: #1A2E0F;
    }
    .vp-park-card__location {
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 13px;
      color: #557097;
    }
    .vp-park-card__location svg { width:12px; height:12px; flex-shrink:0; }
    .vp-park-card__stats {
      display: flex;
      gap: 12px;
      margin-top: 4px;
    }
    .vp-park-card__stat {
      font-size: 12px;
      color: #3B6D11;
      background: rgba(151,196,89,.12);
      padding: 2px 8px;
      border-radius: 4px;
    }
    .vp-park-card__btn {
      margin-top: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 32px;
      background: #3B6D11;
      color: #DFFFAA;
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      font-weight: 600;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: background .2s;
    }
    .vp-park-card__btn:hover { background: #4a8a16; }

    /* Search overlay no results */
    .vp-no-match {
      display: none;
      text-align: center;
      padding: 40px 0;
      font-size: 24px;
      color: #1A2E0F;
      grid-column: 1/-1;
    }
    .vp-no-match.visible { display: block; }

    /* ═══════════════════════════════ REPORT SECTION ═══════════════════════════════ */
    .vp-report-section {
      background: linear-gradient(135deg, #1A2E0F 0%, #2D4A1A 100%);
      padding: 48px 24px;
      text-align: center;
    }
    .vp-report-section h2 {
      font-size: 24px;
      font-weight: 700;
      color: #DFFFAA;
      margin-bottom: 10px;
    }
    .vp-report-section p {
      font-size: 16px;
      color: #97C459;
      margin-bottom: 24px;
      max-width: 460px;
      margin-left: auto;
      margin-right: auto;
    }
    .vp-report-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      border: 2px solid #97C459;
      border-radius: 6px;
      padding: 12px 28px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      font-weight: 600;
      color: #DFFFAA;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: background .2s, box-shadow .2s;
    }
    .vp-report-btn:hover { background: rgba(151,196,89,.12); box-shadow: 0 4px 16px rgba(151,196,89,.20); }
    .vp-report-btn svg { width:18px; height:18px; color:#97C459; }

    /* ═══════════════════════════════ FOOTER ═══════════════════════════════ */
    .vp-footer {
      background: #0D1A08;
      padding: 20px 24px;
      text-align: center;
      font-size: 13px;
      color: rgba(151,196,89,.55);
    }

    /* ═══════════════════════════════ RESPONSIVE ═══════════════════════════════ */
    @media (max-width: 768px) {
      .vp-nav-section { display: none; }
      .vp-user__name  { font-size: 14px; }
    }
    @media (max-width: 540px) {
      .vp-search-wrap { width: 120px; }
      .vp-user__name  { display: none; }
      .vp-brand__sub  { display: none; }
      .vp-hero { padding: 40px 16px 36px; }
    }
    @media (max-width: 400px) {
      .vp-search-wrap { display: none; }
    }
  </style>
</head>
<body>

<!-- ═══════════════════════════════ HEADER ═══════════════════════════════ -->
<header class="vp-header">

  <!-- Brand -->
  <div class="vp-brand">
    <span class="vp-brand__name">VozPark</span>
    <span class="vp-brand__sub">Parques urbanos – El Salvador</span>
  </div>

  <!-- Divider -->
  <div class="vp-divider"></div>

  <!-- Nav section -->
  <div class="vp-nav-section">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><polyline points="9 21 9 12 15 12 15 21"/></svg>
    Inicio
  </div>

  <!-- Search -->
  <div class="vp-search-wrap">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input class="vp-search" id="search-input" type="text" placeholder="Buscar en VozPark" autocomplete="off"/>
  </div>

  <div class="vp-spacer"></div>

  <!-- Bell -->
  <div class="vp-bell" title="Notificaciones">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
      <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
    </svg>
    <span class="vp-bell__dot"></span>
  </div>

  <!-- User -->
  <div class="vp-user">
    <div class="vp-user__avatar" id="user-avatar">MG</div>
    <span class="vp-user__name" id="user-name">María González</span>
  </div>

  <!-- Logout -->
  <button class="vp-logout" id="btn-logout">Salir</button>

</header>

<!-- No results overlay -->
<div class="vp-no-results" id="no-results-overlay">No se encontraron coincidencias</div>

<!-- ═══════════════════════════════ LOGOUT MODAL ═══════════════════════════════ -->
<div class="vp-modal-overlay" id="logout-modal">
  <div class="vp-modal">
    <p class="vp-modal__title">¿Estás seguro de cerrar sesión?</p>
    <div class="vp-modal__actions">
      <button class="vp-modal__btn" id="btn-cancel">Cancelar</button>
      <button class="vp-modal__btn" id="btn-confirm">Confirmar</button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
<section class="vp-hero">
  <div class="vp-hero__tag">Vista en tiempo real</div>
  <h1 class="vp-hero__title">Parques urbanos de<br/>El Salvador</h1>
  <p class="vp-hero__sub">Consulta el estado de los parques de tu ciudad y reporta incidencias fácilmente.</p>
  <button class="vp-hero__cta" id="btn-reportar-hero">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    Reportar una incidencia
  </button>
</section>

<!-- ═══════════════════════════════ PARKS GRID ═══════════════════════════════ -->
<section class="vp-section">
  <div class="vp-section__head">
    <h2 class="vp-section__title">Parques registrados</h2>
    <span class="vp-section__count" id="parks-count">5 parques</span>
  </div>
  <div class="vp-parks-grid" id="parks-grid">
    <!-- injected by JS -->
  </div>
</section>

<!-- ═══════════════════════════════ REPORT CTA ═══════════════════════════════ -->
<section class="vp-report-section">
  <h2>¿Notaste algo en tu parque?</h2>
  <p>Tu reporte ayuda a mantener los espacios públicos en óptimas condiciones para todos.</p>
  <button class="vp-report-btn" id="btn-reportar-footer">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Enviar un reporte
  </button>
</section>

<!-- ═══════════════════════════════ FOOTER ═══════════════════════════════ -->
<footer class="vp-footer">
  VozPark © 2025 · Alcaldía de San Salvador · Parques urbanos de El Salvador
</footer>

<!-- ═══════════════════════════════ SCRIPT ═══════════════════════════════ -->
<script>
/* ── Park data ── */
const PARKS = [
  { id:1, nombre:'Parque Central',        zona:'Centro, San Salvador',  estado:'urgente',  incidencias:3, personal:2 },
  { id:2, nombre:'Parque La Libertad',    zona:'Santa Tecla, La Libertad', estado:'activo',  incidencias:1, personal:1 },
  { id:3, nombre:'Parque Infantil Norte', zona:'Mejicanos, San Salvador',  estado:'revision', incidencias:2, personal:1 },
  { id:4, nombre:'Parque El Bosque',      zona:'San Salvador Centro',      estado:'activo',   incidencias:0, personal:1 },
  { id:5, nombre:'Parque Simón Bolívar',  zona:'Centro Histórico, SS',     estado:'urgente',  incidencias:2, personal:0 },
];

const BADGE = {
  urgente:  { cls:'badge-urgente',  label:'Urgente' },
  activo:   { cls:'badge-activo',   label:'Activo' },
  revision: { cls:'badge-revision', label:'En revisión' },
};

const TREE_SVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V12M12 12L5 6h14L12 12zM9 6L6 2h12L15 6"/></svg>`;
const PIN_SVG  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`;

/* ── Render parks ── */
function renderParks(data) {
  const grid   = document.getElementById('parks-grid');
  const count  = document.getElementById('parks-count');
  const noRes  = document.getElementById('no-results-overlay');

  if (!data.length) {
    grid.innerHTML = `<div class="vp-no-match visible">No se encontraron coincidencias</div>`;
    noRes.classList.add('visible');
    count.textContent = '0 parques';
    return;
  }

  noRes.classList.remove('visible');
  count.textContent = `${data.length} parque${data.length !== 1 ? 's' : ''}`;

  grid.innerHTML = data.map(p => {
    const b = BADGE[p.estado] || BADGE.activo;
    return `
      <div class="vp-park-card" data-id="${p.id}">
        <div class="vp-park-card__img">
          ${TREE_SVG}
          <span class="vp-park-card__badge ${b.cls}">${b.label}</span>
        </div>
        <div class="vp-park-card__body">
          <div class="vp-park-card__name">${p.nombre}</div>
          <div class="vp-park-card__location">
            ${PIN_SVG}
            ${p.zona}
          </div>
          <div class="vp-park-card__stats">
            <span class="vp-park-card__stat">${p.incidencias} incidencia${p.incidencias !== 1 ? 's':''}</span>
            <span class="vp-park-card__stat">${p.personal} personal</span>
          </div>
          <button class="vp-park-card__btn">Ver parque</button>
        </div>
      </div>
    `;
  }).join('');

  /* Ripple on park buttons */
  grid.querySelectorAll('.vp-park-card__btn').forEach(btn =>
    btn.addEventListener('click', e => { e.stopPropagation(); createRipple(btn, e); }));
}

renderParks(PARKS);

/* ── Search ── */
document.getElementById('search-input').addEventListener('input', function () {
  const q = this.value.trim().toLowerCase();
  if (!q) { renderParks(PARKS); return; }
  const filtered = PARKS.filter(p =>
    p.nombre.toLowerCase().includes(q) ||
    p.zona.toLowerCase().includes(q) ||
    p.estado.toLowerCase().includes(q)
  );
  renderParks(filtered);
});

/* ── Logout modal ── */
const modal   = document.getElementById('logout-modal');
document.getElementById('btn-logout').addEventListener('click', e => {
  createRipple(document.getElementById('btn-logout'), e);
  modal.classList.add('active');
});
document.getElementById('btn-cancel').addEventListener('click', () =>
  modal.classList.remove('active'));
document.getElementById('btn-confirm').addEventListener('click', () => {
  modal.classList.remove('active');
  window.location.href = 'login.html';
});
modal.addEventListener('click', e => {
  if (e.target === modal) modal.classList.remove('active');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') modal.classList.remove('active');
});

/* ── Report buttons ── */
document.getElementById('btn-reportar-hero').addEventListener('click', e => {
  createRipple(document.getElementById('btn-reportar-hero'), e);
  window.location.href = 'reporte.html';
});
document.getElementById('btn-reportar-footer').addEventListener('click', e => {
  createRipple(document.getElementById('btn-reportar-footer'), e);
  window.location.href = 'reporte.html';
});

/* ── Ripple ── */
function createRipple(btn, e) {
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = (e.clientX - rect.left) - size / 2;
  const y = (e.clientY - rect.top)  - size / 2;
  const r = document.createElement('span');
  r.className = 'ripple';
  r.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px`;
  btn.appendChild(r);
  r.addEventListener('animationend', () => r.remove());
}

document.querySelectorAll('.vp-modal__btn, .vp-hero__cta, .vp-report-btn').forEach(btn =>
  btn.addEventListener('click', e => createRipple(btn, e)));
</script>

</body>
</html>