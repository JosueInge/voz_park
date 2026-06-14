const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
if (passwordInput && togglePassword) {
    togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        togglePassword.src = isPassword ? 'imagenes/ojoAbierto.webp' : 'imagenes/ojoCerrado.webp';
        togglePassword.alt = isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña';
    });
}

// Validaciones y efectos visuales
const form = document.querySelector('form');
const emailInput = document.getElementById('email');
const btnIniciar = document.querySelector('.btnIniciarSesion');
const vozparkConfig = window.VOZPARK_CONFIG || {};

function obtenerRutaRedireccionPorRol(usuario) {
    const rol = String((usuario && usuario.rol) || '').toLowerCase();
    if (rol === 'admin') {
        return '../home_admin/panel_principal.html';
    }
    return vozparkConfig.postLoginRedirect || '../home_usuarios/inicio_plataforma.html';
}

function mostrarError(input, mensaje) {
	let container = input.closest('.gruposFormulario') || input.parentElement;
	let error = container.querySelector('.mensaje-error');
	if (!error) {
		error = document.createElement('div');
		error.className = 'mensaje-error';
		container.appendChild(error);
	}
	error.textContent = mensaje;
	error.style.display = 'block';
	input.classList.add('input-error', 'shake');
	setTimeout(() => {
		input.classList.remove('shake');
		limpiarError(input);
	}, 2500);
}

function limpiarError(input) {
	let container = input.closest('.gruposFormulario') || input.parentElement;
	let error = container.querySelector('.mensaje-error');
	if (error) error.style.display = 'none';
	input.classList.remove('input-error', 'shake');
}

function validarEmail(email) {
	// Regex simple para validar formato de correo
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function limpiarCamposFormulario() {
    emailInput.value = '';
    passwordInput.value = '';
}

async function iniciarSesionLocal(correo, password) {
    const endpoint = vozparkConfig.localAuthEndpoint || '../backend/login_auth.php';
    const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ correo, password })
    });

    const raw = await response.text();
    let data = {};

    try {
        data = raw ? JSON.parse(raw) : {};
    } catch (e) {
        data = { raw };
    }

    if (!response.ok) {
        throw new Error((data && data.message) || 'Correo o contraseña incorrectos');
    }

    return data;
}

form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const email = emailInput.value.trim();
    const password = passwordInput.value;

    limpiarError(emailInput);
    limpiarError(passwordInput);

    // Validar primero el correo
    if (!email) {
        mostrarError(emailInput, 'El correo electrónico es obligatorio.');
        return;
    } else if (!validarEmail(email)) {
        mostrarError(emailInput, 'Formato de correo incorrecto');
        return;
    }

    // Si el correo es válido, validar la contraseña
    if (!password) {
        mostrarError(passwordInput, 'La contraseña es obligatoria.');
        return;
    }

    try {
        const resultado = await iniciarSesionLocal(email, password);
        const authData = (resultado && resultado.data) || {};

        if (authData.token) {
            localStorage.setItem('vozpark_auth_token', authData.token);
        }
        if (authData.usuario) {
            localStorage.setItem('vozpark_usuario', JSON.stringify(authData.usuario));
        }

        limpiarCamposFormulario();
        window.location.href = obtenerRutaRedireccionPorRol(authData.usuario);
    } catch (error) {
        const mensaje = error && error.message
            ? error.message
            : 'Correo electronico o contraseña incorrectos';
        mostrarError(emailInput, mensaje);
        mostrarError(passwordInput, mensaje);
    }
});

// Efectos visuales en campos
[emailInput, passwordInput].forEach(input => {
    input.addEventListener('focus', () => {
        input.parentElement.classList.add('input-focus');
    });
    input.addEventListener('blur', () => {
        input.parentElement.classList.remove('input-focus');
    });
    input.addEventListener('mouseenter', () => {
        input.parentElement.classList.add('input-hover');
    });
    input.addEventListener('mouseleave', () => {
        input.parentElement.classList.remove('input-hover');
    });
});

// Login con Google (Google Identity Services)
const googleContainer = document.getElementById('google-signin-container');
const mensajeGoogle = document.getElementById('mensajeGoogle');
let googleMsgTimer = null;

function solicitarConfirmacionGoogle(mensaje, titulo = 'Confirmacion requerida') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.background = 'rgba(0, 0, 0, 0.45)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = '9999';
        overlay.style.padding = '16px';

        const dialog = document.createElement('div');
        dialog.style.width = '100%';
        dialog.style.maxWidth = '420px';
        dialog.style.background = '#ffffff';
        dialog.style.borderRadius = '12px';
        dialog.style.padding = '18px';
        dialog.style.boxShadow = '0 20px 50px rgba(0, 0, 0, 0.25)';
        dialog.style.fontFamily = "'Inter', sans-serif";

        const titleEl = document.createElement('h4');
        titleEl.textContent = titulo;
        titleEl.style.margin = '0 0 10px 0';
        titleEl.style.color = '#2d4f0e';
        titleEl.style.fontSize = '18px';

        const messageEl = document.createElement('p');
        messageEl.textContent = mensaje;
        messageEl.style.margin = '0 0 14px 0';
        messageEl.style.color = '#1f2937';
        messageEl.style.fontSize = '14px';
        messageEl.style.lineHeight = '1.5';

        const actions = document.createElement('div');
        actions.style.display = 'flex';
        actions.style.gap = '10px';
        actions.style.justifyContent = 'flex-end';

        const btnCancelar = document.createElement('button');
        btnCancelar.type = 'button';
        btnCancelar.textContent = 'Cancelar';
        btnCancelar.style.padding = '8px 14px';
        btnCancelar.style.border = '1px solid #c7d2c0';
        btnCancelar.style.borderRadius = '8px';
        btnCancelar.style.background = '#f8faf7';
        btnCancelar.style.cursor = 'pointer';

        const btnContinuar = document.createElement('button');
        btnContinuar.type = 'button';
        btnContinuar.textContent = 'Continuar';
        btnContinuar.style.padding = '8px 14px';
        btnContinuar.style.border = 'none';
        btnContinuar.style.borderRadius = '8px';
        btnContinuar.style.background = '#3B6D11';
        btnContinuar.style.color = '#ffffff';
        btnContinuar.style.cursor = 'pointer';

        const cleanup = () => {
            overlay.remove();
            document.removeEventListener('keydown', onKeyDown);
        };

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                cleanup();
                resolve(false);
            }
        };

        btnCancelar.addEventListener('click', () => {
            cleanup();
            resolve(false);
        });

        btnContinuar.addEventListener('click', () => {
            cleanup();
            resolve(true);
        });

        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                cleanup();
                resolve(false);
            }
        });

        actions.appendChild(btnCancelar);
        actions.appendChild(btnContinuar);
        dialog.appendChild(titleEl);
        dialog.appendChild(messageEl);
        dialog.appendChild(actions);
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);

        document.addEventListener('keydown', onKeyDown);
        btnContinuar.focus();
    });
}

function mostrarMensajeGoogle(mensaje, opciones = {}) {
    const {
        esError = false,
        duracion = 3200,
        persistir = false
    } = opciones;

    if (esError) {
        console.warn('[Google Auth]', mensaje);
    }

    if (!mensajeGoogle) return;

    mensajeGoogle.textContent = mensaje;
    if (googleMsgTimer) {
        clearTimeout(googleMsgTimer);
    }

    const mensajeEsError = esError || /error|invalido|no se pudo|falta|problema|conexion|required/i.test(mensaje);
    if (!mensajeEsError && !persistir) {
        googleMsgTimer = setTimeout(() => {
            mensajeGoogle.textContent = '';
        }, duracion);
    }
}

function aplicarEfectosBotonGoogle(contenedor) {
    if (!contenedor) return;

    const botonVisual = contenedor.querySelector('.btnGoogle');
    if (!botonVisual) return;

    const resetHover = () => {
        botonVisual.classList.remove('btn-hover');
    };

    contenedor.addEventListener('pointerdown', function (e) {
        const rect = botonVisual.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        botonVisual.style.setProperty('--wave-x', x + 'px');
        botonVisual.style.setProperty('--wave-y', y + 'px');
        botonVisual.classList.add('btn-wave');
        setTimeout(() => botonVisual.classList.remove('btn-wave'), 450);

        // Evita que el hover quede pegado al volver del popup de Google.
        setTimeout(resetHover, 180);
    });

    contenedor.addEventListener('mouseenter', function () {
        botonVisual.classList.add('btn-hover');
    });

    contenedor.addEventListener('mouseleave', function () {
        botonVisual.classList.remove('btn-hover');
    });

    window.addEventListener('focus', resetHover);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            resetHover();
        }
    });
}

async function enviarTokenGoogle(credential, confirmarPropiedad = false) {
    const endpoint = vozparkConfig.googleAuthEndpoint || '../backend/google_auth.php';

    const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            credential,
            confirm_ownership: confirmarPropiedad
        })
    });

    const raw = await response.text();
    let data = {};

    try {
        data = raw ? JSON.parse(raw) : {};
    } catch (e) {
        data = { raw };
    }

    if (!response.ok) {
        if (response.status === 409 && data.code === 'OWNER_CONFIRMATION_REQUIRED') {
            const confirmar = await solicitarConfirmacionGoogle(
                'Se encontro una cuenta con este correo. Confirma que eres el propietario para vincular Google e iniciar sesion.',
                'Vincular cuenta existente'
            );
            if (confirmar) {
                return enviarTokenGoogle(credential, true);
            }
        }

        const detalle = data.debug ? ` (${data.debug})` : '';
        throw new Error((data.message || 'No se pudo iniciar sesion con Google.') + detalle);
    }

    return data;
}

async function manejarRespuestaGoogle(googleResponse) {
    try {
        if (!googleResponse || !googleResponse.credential) {
            throw new Error('No se recibio credencial de Google.');
        }

        const confirmar = await solicitarConfirmacionGoogle(
            'Confirma que eres el propietario de esta cuenta de Google para continuar.',
            'Confirmar propietario'
        );
        if (!confirmar) {
            mostrarMensajeGoogle('Proceso cancelado por el usuario antes de enviar al servidor.', { esError: true, persistir: true });
            return;
        }

        const resultado = await enviarTokenGoogle(googleResponse.credential, false);
        const authData = resultado.data || {};

        if (authData.token) {
            localStorage.setItem('vozpark_auth_token', authData.token);
        }
        if (authData.usuario) {
            localStorage.setItem('vozpark_usuario', JSON.stringify(authData.usuario));
        }

        setTimeout(() => {
            window.location.href = obtenerRutaRedireccionPorRol(authData.usuario);
        }, 600);
    } catch (error) {
        console.error('Google Sign-In fallo:', error);
        mostrarMensajeGoogle(error.message || 'Error al iniciar sesion con Google.', { esError: true });
    }
}

function inicializarGoogleSignIn() {
    if (!googleContainer) return;

    if (!vozparkConfig.googleClientId) {
        googleContainer.innerHTML = `
            <button type="button" class="btnGoogle" id="btnGoogleFallback">
                <img src="imagenes/imagenGoogle.png" alt="Google">
                <span>Ingresar con Google</span>
            </button>
        `;

        const btnGoogleFallback = document.getElementById('btnGoogleFallback');
        if (btnGoogleFallback) {
            btnGoogleFallback.addEventListener('click', function() {
                mostrarMensajeGoogle('Falta configurar GOOGLE_CLIENT_ID en backend/.env');
            });
            aplicarEfectosBotonGoogle(googleContainer);
        }

        mostrarMensajeGoogle('Falta configurar GOOGLE_CLIENT_ID en backend/.env');
        return;
    }

    if (!(window.google && window.google.accounts && window.google.accounts.id)) {
        mostrarMensajeGoogle('No se pudo cargar Google. Recarga la pagina.');
        return;
    }

    window.google.accounts.id.initialize({
        client_id: vozparkConfig.googleClientId,
        callback: manejarRespuestaGoogle,
        auto_select: false,
        ux_mode: 'popup'
    });

    googleContainer.innerHTML = `
        <button type="button" class="btnGoogle" id="btnGoogleCustom">
            <img src="imagenes/imagenGoogle.png" alt="Google">
            <span>Ingresar con Google</span>
        </button>
    `;

    const googleHitArea = document.createElement('div');
    googleHitArea.className = 'google-hit-area';
    googleHitArea.setAttribute('aria-label', 'Ingresar con Google');
    googleContainer.appendChild(googleHitArea);

    window.google.accounts.id.renderButton(googleHitArea, {
        theme: 'outline',
        type: 'standard',
        size: 'large',
        width: 310,
        text: 'continue_with',
        shape: 'rectangular',
        logo_alignment: 'left',
        locale: 'es'
    });

    aplicarEfectosBotonGoogle(googleContainer);
}

window.addEventListener('load', inicializarGoogleSignIn);

// Efectos visuales en el enlace de registro (noTienesCuenta a)
const linkRegistro = document.querySelector('.noTienesCuenta a');
if (linkRegistro) {
    linkRegistro.addEventListener('pointerdown', function (e) {
        const rect = linkRegistro.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        linkRegistro.style.setProperty('--wave-x', x + 'px');
        linkRegistro.style.setProperty('--wave-y', y + 'px');
        linkRegistro.classList.add('btn-wave');
        setTimeout(() => linkRegistro.classList.remove('btn-wave'), 400);
    });
    linkRegistro.addEventListener('mouseenter', function () {
        linkRegistro.classList.add('btn-hover');
    });
    linkRegistro.addEventListener('mouseleave', function () {
        linkRegistro.classList.remove('btn-hover');
    });
    linkRegistro.addEventListener('click', function (e) {
        e.preventDefault();
        linkRegistro.blur();
        // Esperar para mostrar el efecto de onda antes de redirigir
        const href = linkRegistro.getAttribute('href');
        setTimeout(() => {
            window.location.href = href;
        }, 380); // igual o menor al tiempo de la animación CSS
    });
}

// Efectos visuales en el enlace "¿Olvidaste tu contraseña?"
const linkOlvidaste = document.querySelector('.olvidasteContraseña');
if (linkOlvidaste) {
    linkOlvidaste.addEventListener('pointerdown', function (e) {
        const rect = linkOlvidaste.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        linkOlvidaste.style.setProperty('--wave-x', x + 'px');
        linkOlvidaste.style.setProperty('--wave-y', y + 'px');
        linkOlvidaste.classList.add('btn-wave');
        setTimeout(() => linkOlvidaste.classList.remove('btn-wave'), 400);
    });
    linkOlvidaste.addEventListener('mouseenter', function () {
        linkOlvidaste.classList.add('btn-hover');
    });
    linkOlvidaste.addEventListener('mouseleave', function () {
        linkOlvidaste.classList.remove('btn-hover');
    });
    // Quitar focus después del click
    linkOlvidaste.addEventListener('click', function () {
        linkOlvidaste.blur();
    });
}

// Efectos visuales en el botón Iniciar Sesión
btnIniciar.addEventListener('pointerdown', function (e) {
    // Efecto de onda
    const rect = btnIniciar.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    btnIniciar.style.setProperty('--wave-x', x + 'px');
    btnIniciar.style.setProperty('--wave-y', y + 'px');
    btnIniciar.classList.add('btn-wave');
    setTimeout(() => btnIniciar.classList.remove('btn-wave'), 500);
});
btnIniciar.addEventListener('mouseenter', function () {
    btnIniciar.classList.add('btn-hover');
});
btnIniciar.addEventListener('mouseleave', function () {
    btnIniciar.classList.remove('btn-hover');
});
// Quitar focus después del click
btnIniciar.addEventListener('click', function () {
    btnIniciar.blur();
});
