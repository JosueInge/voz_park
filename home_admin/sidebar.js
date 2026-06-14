/**
 * sidebar.js — VozPark Admin
 * Inyecta el panel de navegación lateral en todas las vistas.
 * Uso: <script src="sidebar.js"></script>  justo antes de </body>
 * Opcional: window.VP_SECTION = 'nombre_seccion'  para forzar activo.
 */
(function () {
  /* ── CSS ── */
  const style = document.createElement('style');
  style.textContent = `
    .vp-app { display:flex; min-height:calc(100vh - 55px); }

    .vp-sidebar-overlay { display:none; position:fixed; inset:0; top:55px; background:rgba(0,0,0,.45); z-index:89; }
    .vp-sidebar-overlay.open { display:block; }

    .vp-sidebar {
      width:245px; min-height:100%; background:#0F172A;
      flex-shrink:0; display:flex; flex-direction:column;
      padding:16px 0 32px;
      position:sticky; top:55px; height:calc(100vh - 55px);
      overflow-y:auto; z-index:90;
      transition:transform .28s cubic-bezier(.4,0,.2,1);
    }

    .vp-nav-cat {
      font-size:12px; font-family:'Inter',sans-serif;
      text-transform:uppercase; letter-spacing:.07em;
      padding:16px 20px 5px; color:#334155; user-select:none;
      display:block;
    }
    .vp-nav-cat:first-child { padding-top:6px; }
    .vp-nav-cat--green { color:#97C459; }

    .vp-nav-btn {
      display:flex; align-items:center; gap:11px;
      padding:9px 14px 9px 16px;
      background:transparent; border:none; border-radius:5px;
      font-family:'Inter',sans-serif; font-size:16px; color:#557097;
      cursor:pointer; text-align:left; text-decoration:none;
      transition:background .18s,color .18s;
      position:relative; overflow:hidden;
      margin:1px 8px; width:calc(100% - 16px);
    }
    .vp-nav-btn svg { width:24px; height:24px; flex-shrink:0; color:#557097; transition:color .18s; }
    .vp-nav-btn:hover { background:rgba(30,58,95,.55); color:#93C5FD; }
    .vp-nav-btn:hover svg { color:#93C5FD; }
    .vp-nav-btn.active { background:#1E3A5F; color:#93C5FD; border-radius:5px; }
    .vp-nav-btn.active svg { color:#93C5FD; }

    .nav-ripple { position:absolute; border-radius:50%; background:rgba(147,197,253,.25); transform:scale(0); animation:nav-ripple .5s linear; pointer-events:none; }
    @keyframes nav-ripple { to { transform:scale(4); opacity:0; } }

    .vp-content { flex:1; min-width:0; overflow-x:hidden; }

    .vp-hamburger {
      display:none; align-items:center; justify-content:center;
      width:36px; height:36px; background:transparent; border:none;
      cursor:pointer; color:#EFF6FF; flex-shrink:0; margin-right:6px;
    }
    .vp-hamburger svg { width:22px; height:22px; }

    @media (max-width:768px) {
      .vp-hamburger { display:flex; }
      .vp-sidebar {
        position:fixed; top:55px; left:0;
        height:calc(100vh - 55px);
        transform:translateX(-100%);
        box-shadow:4px 0 24px rgba(0,0,0,.35);
      }
      .vp-sidebar.open { transform:translateX(0); }
    }
  `;
  document.head.appendChild(style);

  /* ── SVG icons ── */
  const I = {
    dashboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
    alert:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    users:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    map:       `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
    report:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
    user:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
    lock:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`,
    shield:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
    settings:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`,
    hamburger: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>`,
  };

  /* ── Nav items definition ── */
  const NAV = [
    { type:'cat', label:'Panel', cls:'' },
    { type:'link', label:'Panel principal',        icon:I.dashboard, section:'panel_principal',        href:'panel_principal.html' },
    { type:'link', label:'Gestión de incidencias', icon:I.alert,     section:'gestion_incidencias',    href:'gestion_incidencia.html' },
    { type:'link', label:'Personal municipal',     icon:I.users,     section:'personal_municipal',     href:'personal_municipal.html' },
    { type:'link', label:'Mapa de incidencias',    icon:I.map,       section:'mapa_incidencias',       href:'mapa_incidencias.html' },
    { type:'cat',  label:'Analítica', cls:'vp-nav-cat--green' },
    { type:'link', label:'Reportes estratégicos',  icon:I.report,    section:'reportes_estrategicos',  href:'reportes_estrategicos.html' },
    { type:'cat',  label:'Sistema', cls:'' },
    { type:'link', label:'Usuarios ciudadanos',    icon:I.user,      section:'usuarios_ciudadanos',    href:'usuarios_ciudadanos.html' },
    { type:'link', label:'Roles y permisos',       icon:I.lock,      section:'roles_permisos',         href:'roles_permisos.html' },
    { type:'link', label:'Auditoría',              icon:I.shield,    section:'auditoria',              href:'auditoria.html' },
    { type:'link', label:'Ajustes del sistema',    icon:I.settings,  section:'ajustes_sistema',        href:'ajustes_sistema.html' },
  ];

  /* ── Build sidebar HTML ── */
  const sidebarHTML = NAV.map(item => {
    if (item.type === 'cat')
      return `<span class="vp-nav-cat ${item.cls}">${item.label}</span>`;
    return `<a href="${item.href}" class="vp-nav-btn" data-section="${item.section}">${item.icon}${item.label}</a>`;
  }).join('');

  /* ── Overlay ── */
  const overlay = document.createElement('div');
  overlay.className = 'vp-sidebar-overlay';
  overlay.id = 'sidebar-overlay';

  /* ── Aside ── */
  const aside = document.createElement('aside');
  aside.className = 'vp-sidebar';
  aside.id = 'vp-sidebar';
  aside.setAttribute('aria-label', 'Navegación principal');
  aside.innerHTML = sidebarHTML;

  /* ── Inject hamburger into header ── */
  const header = document.querySelector('.vp-header');
  if (header) {
    const hamBtn = document.createElement('button');
    hamBtn.className = 'vp-hamburger';
    hamBtn.id = 'hamburger-btn';
    hamBtn.setAttribute('aria-label', 'Abrir menú');
    hamBtn.innerHTML = I.hamburger;
    header.insertBefore(hamBtn, header.firstChild);
  }

  /* ── Wrap existing <main> in .vp-app ── */
  const mainEl = document.querySelector('main');
  if (mainEl) {
    const app = document.createElement('div');
    app.className = 'vp-app';
    mainEl.parentNode.insertBefore(app, mainEl);

    const contentDiv = document.createElement('div');
    contentDiv.className = 'vp-content';
    contentDiv.appendChild(mainEl);

    app.appendChild(overlay);
    app.appendChild(aside);
    app.appendChild(contentDiv);
  }

  /* ── Active section detection ── */
  const path    = window.location.pathname;
  const file    = path.substring(path.lastIndexOf('/') + 1).replace('.html', '') || 'panel_principal';
  const section = window.VP_SECTION || file;

  document.querySelectorAll('.vp-nav-btn[data-section]').forEach(btn => {
    if (btn.dataset.section === section) btn.classList.add('active');
  });

  /* ── Hamburger toggle ── */
  const sidebar = document.getElementById('vp-sidebar');
  const hamBtn  = document.getElementById('hamburger-btn');

  const openSidebar  = () => { sidebar.classList.add('open');    overlay.classList.add('open');    document.body.style.overflow='hidden'; };
  const closeSidebar = () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow=''; };

  if (hamBtn) hamBtn.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
  overlay.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', e => { if (e.key==='Escape') closeSidebar(); });
  document.querySelectorAll('.vp-nav-btn').forEach(btn =>
    btn.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); }));

  /* ── Ripple on nav buttons ── */
  document.querySelectorAll('.vp-nav-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      const rect = btn.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const r = document.createElement('span');
      r.className = 'nav-ripple';
      r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
      btn.appendChild(r);
      r.addEventListener('animationend', () => r.remove());
    });
  });
})();