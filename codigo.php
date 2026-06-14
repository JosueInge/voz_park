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
      max-width: 900px;
      margin: 0 auto;
      gap: 24px;
    }
 
    /* Fila superior: línea continua con círculos encima */
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
 
    /* Cada paso */
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
 
    /* Círculo numerado — encima de la línea */
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

══════════════════════════════════════ -->
<section class="vp-how">
  <h2 class="vp-how__title">¿Cómo funciona?</h2>
  <p class="vp-how__sub">Tres pasos para mejorar tu parque</p>
  <div class="vp-how__steps">
 
    <!-- Fila superior: línea continua con círculos encima -->
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