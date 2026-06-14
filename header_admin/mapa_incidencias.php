<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VozPark Admin – Mapa de Incidencias</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #F9FCFF; min-height: 100vh; }

    /* ══ HEADER ══ */
    .vp-header { display:flex; align-items:center; width:100%; height:55px; background:#0F172A; border-bottom:1px solid #1E40AF; padding:0 16px; position:sticky; top:0; z-index:100; }
    .vp-brand  { display:flex; flex-direction:column; padding-right:14px; flex-shrink:0; }
    .vp-brand__name { font-size:16px; font-weight:600; color:#EFF6FF; line-height:1.2; }
    .vp-brand__sub  { font-size:16px; color:#2290BF; line-height:1.2; }
    .vp-hdr-div { width:1px; height:40px; background:rgba(244,250,234,.30); flex-shrink:0; margin-right:14px; }
    .vp-breadcrumb { display:flex; align-items:center; gap:5px; flex:1; min-width:0; }
    .vp-breadcrumb__root { display:flex; align-items:center; gap:5px; font-size:16px; color:#334155; white-space:nowrap; }
    .vp-breadcrumb__root svg { width:18px; height:18px; color:#97C459; }
    .vp-breadcrumb__sep { font-size:16px; color:#7685B7; }
    .vp-breadcrumb__cur { font-size:16px; color:#AAB0C3; }
    .vp-user { display:flex; align-items:center; gap:8px; background:linear-gradient(90deg,rgba(30,64,175,.70),rgba(30,64,175,.10)); border:1px solid #1E40AF; border-radius:6px; padding:4px 10px; margin-left:12px; flex-shrink:0; }
    .vp-user__av   { width:32px; height:32px; border-radius:50%; background:#1E40AF; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#93C5FD; }
    .vp-user__name { font-size:16px; font-weight:600; color:#FFFFFF; white-space:nowrap; }
    .vp-user__role { font-size:12px; color:#EFF6FF; white-space:nowrap; }
    .vp-logout { display:flex; align-items:center; justify-content:center; gap:5px; width:55px; height:25px; background:transparent; border:1px solid rgba(59,130,246,.30); border-radius:4px; cursor:pointer; font-family:'Inter',sans-serif; font-size:14px; color:#1E40AF; margin-left:8px; position:relative; overflow:hidden; transition:background .2s; flex-shrink:0; }
    .vp-logout:hover { background:rgba(59,130,246,.08); }
    .vp-logout svg { width:16px; height:16px; }

    /* Ripple */
    .ripple { position:absolute; border-radius:50%; background:rgba(59,130,246,.25); transform:scale(0); animation:rpl .55s linear; pointer-events:none; }
    @keyframes rpl { to { transform:scale(4); opacity:0; } }

    /* Modal logout */
    .vp-modal-ov { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:999; align-items:center; justify-content:center; }
    .vp-modal-ov.active { display:flex; }
    .vp-modal { background:#1E293B; border:1px solid #334155; border-radius:10px; padding:28px 32px 24px; width:340px; max-width:90vw; display:flex; flex-direction:column; gap:20px; }
    .vp-modal__title { font-size:16px; font-weight:700; color:#FFFFFF; text-align:center; }
    .vp-modal__actions { display:flex; gap:12px; }
    .vp-modal__btn { flex:1; height:40px; border:none; border-radius:8px; font-family:'Inter',sans-serif; font-size:16px; color:#FFFFFF; cursor:pointer; position:relative; overflow:hidden; transition:filter .2s; }
    .vp-modal__btn--cancel  { background:#3B82F6; }
    .vp-modal__btn--confirm { background:#1E40AF; }
    .vp-modal__btn:hover { filter:brightness(1.12); }

    /* ══ MAIN ══ */
    .vp-main { padding:20px 20px 32px; }

    /* Top bar: título + filtros */
    .vp-topbar {
      display:flex; align-items:flex-start;
      justify-content:space-between;
      flex-wrap:wrap; gap:12px;
      margin-bottom:16px;
    }
    .vp-titles {}
    .vp-page-title { font-size:24px; font-weight:700; color:#0F172A; }
    .vp-page-sub   { font-size:14px; color:#0F172A; margin-top:3px; }

    /* Filtros */
    .vp-filters { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .vp-filter {
      display:inline-flex; align-items:center; gap:6px;
      padding:4px 12px;
      background:#DBEAFE;
      border:1px solid rgba(34,144,191,.75);
      border-radius:4px;
      font-family:'Inter',sans-serif; font-size:14px; color:#1E40AF;
      cursor:pointer; position:relative; overflow:hidden;
      transition:background .2s, box-shadow .2s;
      user-select:none;
    }
    .vp-filter:hover { background:#BFDBFE; box-shadow:0 2px 6px rgba(34,144,191,.20); }
    .vp-filter.active { background:#1E40AF; color:#FFFFFF; border-color:#1E40AF; }
    .vp-filter__dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

    /* ══ LAYOUT MAPA + PANEL ══ */
    .vp-map-layout {
      display:grid;
      grid-template-columns:1fr 300px;
      gap:16px;
      /* Alto disponible = 100vh - header(55px) - main-padding(20px) - topbar(~70px) */
      height:calc(100vh - 55px - 20px - 70px - 32px);
      min-height:480px;
    }

    /* Mapa */
    .vp-map-wrap {
      position:relative;
      border-radius:8px;
      overflow:hidden;
      background:#E2E8F0;
      width:100%;
      height:100%;
    }
    #gmap { width:100%; height:100%; }

    /* Leyenda */
    .vp-legend {
      position:absolute; bottom:16px; left:50%; transform:translateX(-50%);
      background:rgba(255,255,255,.80);
      border-radius:4px; padding:6px 14px;
      display:flex; gap:14px; align-items:center;
      pointer-events:none; backdrop-filter:blur(4px);
      white-space:nowrap;
    }
    .vp-legend-item { display:flex; align-items:center; gap:5px; font-size:12px; color:#000000; }
    .vp-legend-dot  { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

    /* ══ PANEL DERECHO ══ */
    .vp-panel {
      background:#FFFFFF;
      border:1px solid rgba(118,133,183,.35);
      border-radius:8px;
      display:flex; flex-direction:column;
      overflow:hidden;
      height:100%;
    }
    .vp-panel__head {
      padding:12px 14px;
      border-bottom:1px solid #F1F5F9;
      font-size:14px; font-weight:600; color:#0F172A;
      flex-shrink:0;
    }
    .vp-panel__list {
      flex:1; overflow-y:auto;
      padding:10px;
      display:flex; flex-direction:column; gap:8px;
    }
    .vp-panel__empty {
      text-align:center; font-size:14px; color:#0F172A;
      padding:30px 0;
    }

    /* Tarjeta de incidencia */
    .vp-inc-card {
      border-radius:6px;
      border:1px solid #F1F5F9;
      border-left:4px solid #7685B7;
      padding:9px 11px;
      display:flex; flex-direction:column; gap:4px;
      background:#FAFAFA;
      transition:box-shadow .15s;
    }
    .vp-inc-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .vp-inc-card__top  { display:flex; align-items:center; gap:6px; }
    .vp-inc-card__top svg { width:16px; height:16px; flex-shrink:0; }
    .vp-inc-card__name { font-size:14px; font-weight:700; color:#000000; }
    .vp-inc-card__meta { font-size:12px; color:#000000; }
    .vp-inc-badge {
      display:inline-block; padding:1px 8px;
      border-radius:10px; font-size:12px; color:#000000;
    }
    .badge-urgente { background:rgba(196,89,89,.30); }
    .badge-media   { background:rgba(196,175,89,.30); }
    .badge-baja    { background:rgba(202,204,201,.50); }

    /* Botón ver todas */
    .vp-btn-all {
      display:flex; align-items:center; justify-content:center;
      width:100%; height:38px; flex-shrink:0;
      background:#1E40AF; border:none; border-radius:0 0 8px 8px;
      font-family:'Inter',sans-serif; font-size:14px; color:#FFFFFF;
      cursor:pointer; position:relative; overflow:hidden;
      transition:background .2s; text-decoration:none;
    }
    .vp-btn-all:hover { background:#1D3BA0; }

    /* Spinner */
    .vp-spinner-wrap { display:flex; align-items:center; justify-content:center; height:100%; }
    .vp-spinner { width:28px; height:28px; border:3px solid #E2E8F0; border-top-color:#1E40AF; border-radius:50%; animation:spin .8s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }

    /* ══ RESPONSIVE ══ */
    @media (max-width:900px) {
      .vp-map-layout { grid-template-columns:1fr; height:auto; }
      .vp-map-wrap   { height:380px; }
      .vp-panel      { height:300px; }
    }
    @media (max-width:640px) {
      .vp-brand__sub, .vp-user__role { display:none; }
      .vp-main { padding:14px 12px 24px; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="vp-header">
  <div class="vp-brand">
    <span class="vp-brand__name">VozPark Admin</span>
    <span class="vp-brand__sub">Portal Municipal</span>
  </div>
  <div class="vp-hdr-div"></div>
  <nav class="vp-breadcrumb">
    <span class="vp-breadcrumb__root">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><polyline points="9 21 9 12 15 12 15 21"/></svg>
      Admin
    </span>
    <span class="vp-breadcrumb__sep">›</span>
    <span class="vp-breadcrumb__cur">Mapa de Incidencias</span>
  </nav>
  <div class="vp-user">
    <div class="vp-user__av" id="hdr-av">CA</div>
    <div>
      <div class="vp-user__name" id="hdr-nombre">Carlos Amaya</div>
      <div class="vp-user__role">Administrador · Alcaldía SS</div>
    </div>
  </div>
  <button class="vp-logout" id="btn-logout">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    Salir
  </button>
</header>

<!-- Modal logout -->
<div class="vp-modal-ov" id="logout-modal">
  <div class="vp-modal">
    <p class="vp-modal__title">¿Estás seguro de cerrar sesión?</p>
    <div class="vp-modal__actions">
      <button class="vp-modal__btn vp-modal__btn--cancel"  id="btn-cancel">Cancelar</button>
      <button class="vp-modal__btn vp-modal__btn--confirm" id="btn-confirm">Confirmar</button>
    </div>
  </div>
</div>

<!-- ══ MAIN ══ -->
<main class="vp-main">

  <!-- Top bar -->
  <div class="vp-topbar">
    <div class="vp-titles">
      <h1 class="vp-page-title">Mapa de incidencias</h1>
      <p class="vp-page-sub">Vista en tiempo real · <span id="parque-count">—</span> parques</p>
    </div>
    <div class="vp-filters">
      <button class="vp-filter active" data-filter="urgente">
        <span class="vp-filter__dot" style="background:#E85D5D;"></span>
        Urgente
      </button>
      <button class="vp-filter active" data-filter="revision">
        <span class="vp-filter__dot" style="background:#C4AF59;"></span>
        En revisión
      </button>
    </div>
  </div>

  <!-- Layout mapa + panel -->
  <div class="vp-map-layout">

    <!-- Mapa -->
    <div class="vp-map-wrap">
      <div id="gmap">
        <div class="vp-spinner-wrap"><div class="vp-spinner"></div></div>
      </div>
      <!-- Leyenda -->
      <div class="vp-legend">
        <div class="vp-legend-item"><span class="vp-legend-dot" style="background:#E85D5D;"></span>Urgente</div>
        <div class="vp-legend-item"><span class="vp-legend-dot" style="background:#C4AF59;"></span>En revisión</div>
        <div class="vp-legend-item"><span class="vp-legend-dot" style="background:#2290BF;"></span>Personal en campo</div>
      </div>
    </div>

    <!-- Panel derecho -->
    <div class="vp-panel">
      <div class="vp-panel__head">Incidencias recientes</div>
      <div class="vp-panel__list" id="panel-list">
        <div class="vp-spinner-wrap"><div class="vp-spinner"></div></div>
      </div>
      <a href="gestion_incidencias.html" class="vp-btn-all">Ver todas las incidencias</a>
    </div>

  </div>
</main>

<!-- ══ SCRIPTS ══ -->
<script>
const API   = '../backend/index.php?path=';
const TOKEN = localStorage.getItem('vp_token') ?? '';

/* ── Íconos SVG por tipo ── */
const ICONOS = {
  'Iluminación':     `<svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>`,
  'Vegetación':      `<svg viewBox="0 0 24 24" fill="none" stroke="#3B6D11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V12M12 12L5 6h14L12 12zM9 6L6 2h12L15 6"/></svg>`,
  'Limpieza':        `<svg viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>`,
  'Infraestructura': `<svg viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>`,
  'Seguridad':       `<svg viewBox="0 0 24 24" fill="none" stroke="#E85D5D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
  'default':         `<svg viewBox="0 0 24 24" fill="none" stroke="#97C459" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
};

/* ── Estado global ── */
let map             = null;
let markersParques  = [];   // { marker, tipo }
let markersPersonal = [];
let activeFilters   = { urgente: true, revision: true };
let allParques      = [];

/* ── Colores y etiquetas de marcador ── */
const MARKER_CONFIG = {
  urgente:  { color: '#E85D5D', label: '!' },
  revision: { color: '#C4AF59', label: '~' },
  activo:   { color: '#3B6D11', label: '✓' },
};

function svgMarker(color, label, selected = false) {
  const size   = selected ? 36 : 32;
  const stroke = selected ? 'white' : 'none';
  const sw     = selected ? 3 : 0;
  return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(
    `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" xmlns="http://www.w3.org/2000/svg">
      <circle cx="${size/2}" cy="${size/2}" r="${size/2 - 1}" fill="${color}" stroke="${stroke}" stroke-width="${sw}"/>
      <text x="${size/2}" y="${size/2 + 5}" font-family="Arial" font-size="${size * 0.45}"
            font-weight="bold" fill="white" text-anchor="middle">${label}</text>
    </svg>`
  )}`;
}

/* ── Inicializar Google Maps ── */
async function initMap() {
  // 1. Obtener API Key del backend
  let apiKey = '';
  try {
    const r = await fetch(`${API}/config/maps`, {
      headers: TOKEN ? { 'Authorization': `Bearer ${TOKEN}` } : {}
    });
    // TODO: descomentar cuando login esté implementado
    // if (r.status === 401) { window.location.href = '../login.php'; return; }
    const j = await r.json();
    apiKey = j.data?.api_key ?? '';
  } catch { /* continuar sin key — mostrará mapa gris */ }

  // 2. Cargar script de Google Maps dinámicamente
  if (apiKey) {
    await new Promise((resolve, reject) => {
      window._gmInitCallback = resolve;
      const s = document.createElement('script');
      s.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=_gmInitCallback&language=es&region=SV`;
      s.onerror = reject;
      document.head.appendChild(s);
    });
  }

  // 3. Crear mapa
  map = new google.maps.Map(document.getElementById('gmap'), {
    center:             { lat: 13.6966, lng: -89.2182 },
    zoom:               14,
    mapTypeControl:     false,
    streetViewControl:  false,
    fullscreenControl:  false,
    styles: [
      { featureType: 'poi',     elementType: 'labels', stylers: [{ visibility: 'off' }] },
      { featureType: 'transit', stylers: [{ visibility: 'off' }] },
    ],
  });

  // 4. Cargar datos en paralelo
  await Promise.all([cargarParques(), cargarPersonal(), cargarPanel()]);
}

/* ── Cargar parques y crear marcadores ── */
async function cargarParques() {
  try {
    const r = await fetch(`${API}/admin/parques`, {
      headers: TOKEN ? { 'Authorization': `Bearer ${TOKEN}` } : {}
    });
    const j = await r.json();
    if (!j.success || !j.data?.length) return;

    allParques = j.data;
    document.getElementById('parque-count').textContent = allParques.length;

    allParques.forEach(p => {
      const tipo = estadoAFiltro(p.estado_general);
      const cfg  = MARKER_CONFIG[tipo] ?? MARKER_CONFIG.activo;

      const marker = new google.maps.Marker({
        position:   { lat: parseFloat(p.latitud), lng: parseFloat(p.longitud) },
        map:        map,
        title:      p.nombre,
        icon:       { url: svgMarker(cfg.color, cfg.label), scaledSize: new google.maps.Size(32, 32) },
        _tipo:      tipo,
        _color:     cfg.color,
        _label:     cfg.label,
      });

      marker.addListener('click', () => resaltarMarcador(marker, markersParques));
      markersParques.push(marker);
    });

    aplicarFiltros();
  } catch { /* sin datos de parques */ }
}

/* ── Cargar personal en campo ── */
async function cargarPersonal() {
  try {
    const r = await fetch(`${API}/admin/personal/campo`, {
      headers: TOKEN ? { 'Authorization': `Bearer ${TOKEN}` } : {}
    });
    const j = await r.json();
    if (!j.success || !j.data?.length) return;

    j.data.forEach(p => {
      const marker = new google.maps.Marker({
        position: { lat: parseFloat(p.latitud), lng: parseFloat(p.longitud) },
        map:      map,
        title:    p.nombre,
        icon:     { url: svgMarker('#2290BF', p.inicial), scaledSize: new google.maps.Size(32, 32) },
        _tipo:    'personal',
        _color:   '#2290BF',
        _label:   p.inicial,
      });
      marker.addListener('click', () => resaltarMarcador(marker, markersPersonal));
      markersPersonal.push(marker);
    });
  } catch { /* sin personal */ }
}

/* ── Panel de incidencias recientes ── */
async function cargarPanel() {
  const list = document.getElementById('panel-list');
  try {
    const r = await fetch(`${API}/admin/incidencias/recientes`, {
      headers: TOKEN ? { 'Authorization': `Bearer ${TOKEN}` } : {}
    });
    const j = await r.json();

    if (!j.success || !j.data?.length) {
      list.innerHTML = '<div class="vp-panel__empty">No hay incidencias recientes</div>';
      return;
    }

    const urgBorderColor = { Alta: '#E85D5D', Media: '#C4AF59', Baja: '#7685B7' };
    const urgBadgeClass  = { Alta: 'badge-urgente', Media: 'badge-media', Baja: 'badge-baja' };

    list.innerHTML = j.data.map(inc => `
      <div class="vp-inc-card" style="border-left-color:${urgBorderColor[inc.urgencia] ?? '#7685B7'};">
        <div class="vp-inc-card__top">
          ${ICONOS[inc.tipo_incidencia] ?? ICONOS['default']}
          <span class="vp-inc-card__name">${inc.tipo_incidencia ?? inc.incidencia}</span>
        </div>
        <div class="vp-inc-card__meta">
          ${inc.parque} · ${inc.personal_asignado ?? 'Sin asignar'}
        </div>
        <span class="vp-inc-badge ${urgBadgeClass[inc.urgencia] ?? 'badge-baja'}">${inc.urgencia}</span>
      </div>
    `).join('');

  } catch {
    list.innerHTML = '<div class="vp-panel__empty">Error al cargar incidencias</div>';
  }
}

/* ── Helpers de marcadores ── */
function estadoAFiltro(estado) {
  if (!estado) return 'activo';
  const s = estado.toLowerCase();
  if (s === 'urgente')     return 'urgente';
  if (s === 'en revisión' || s === 'en revision') return 'revision';
  return 'activo';
}

let marcadorSeleccionado = null;

function resaltarMarcador(marker, grupo) {
  // Restaurar todos del grupo
  grupo.forEach(m => {
    m.setIcon({ url: svgMarker(m._color, m._label, false), scaledSize: new google.maps.Size(32, 32) });
  });
  // Resaltar el seleccionado
  marker.setIcon({ url: svgMarker(marker._color, marker._label, true), scaledSize: new google.maps.Size(36, 36) });
  marcadorSeleccionado = marker;
}

/* ── Aplicar filtros ── */
function aplicarFiltros() {
  markersParques.forEach(m => {
    const visible = m._tipo === 'activo' || activeFilters[m._tipo] === true;
    m.setVisible(visible);
  });
  // Personal siempre visible
  markersPersonal.forEach(m => m.setVisible(true));
}

/* ── Botones de filtro ── */
document.querySelectorAll('.vp-filter').forEach(btn => {
  btn.addEventListener('click', e => {
    crearRipple(btn, e);
    const key = btn.dataset.filter;
    activeFilters[key] = !activeFilters[key];
    btn.classList.toggle('active', activeFilters[key]);
    if (map) aplicarFiltros();
  });
});

/* ── Logout ── */
document.getElementById('btn-logout').addEventListener('click', e => {
  crearRipple(e.currentTarget, e);
  document.getElementById('logout-modal').classList.add('active');
});
document.getElementById('btn-cancel').addEventListener('click', () =>
  document.getElementById('logout-modal').classList.remove('active'));
document.getElementById('btn-confirm').addEventListener('click', () => {
  localStorage.removeItem('vp_token');
  window.location.href = '../login.php';
});
document.getElementById('logout-modal').addEventListener('click', e => {
  if (e.target === document.getElementById('logout-modal'))
    document.getElementById('logout-modal').classList.remove('active');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape')
    document.getElementById('logout-modal').classList.remove('active');
});

/* ── Ripple ── */
function crearRipple(btn, e) {
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const r    = document.createElement('span');
  r.className = 'ripple';
  r.style.cssText = `width:${size}px;height:${size}px;`
                  + `left:${e.clientX-rect.left-size/2}px;`
                  + `top:${e.clientY-rect.top-size/2}px`;
  btn.appendChild(r);
  r.addEventListener('animationend', () => r.remove());
}
document.querySelectorAll('.vp-modal__btn, .vp-logout, .vp-btn-all').forEach(b =>
  b.addEventListener('click', e => crearRipple(b, e)));

/* ── Init ── */
// TODO: Cuando el login esté implementado, descomentar la validación real:
// if (!TOKEN) { window.location.href = '../login.php'; }
// else        { initMap(); }

// Modo desarrollo: iniciar sin validar token
initMap();
</script>
<script src="sidebar.js"></script>
</body>
</html>