const form = document.getElementById('form-recuperar');
const emailInput = document.getElementById('email');
const btnEnviar = document.getElementById('btn-enviar');
const toast = document.getElementById('toast');

let toastTimer = null;

function mostrarToast(mensaje, tipo) {
    if (toastTimer) clearTimeout(toastTimer);
    toast.textContent = mensaje;
    toast.className = 'toast';
    toast.classList.add('show', tipo === 'success' ? 'toast-success' : 'toast-error');
    toastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
}

function limpiarError(input) {
    const container = input.closest('.gruposFormulario') || input.parentElement;
    const error = container.querySelector('.mensaje-error');
    if (error) error.style.display = 'none';
    input.classList.remove('input-error', 'shake');
}

function mostrarError(input, mensaje) {
    const container = input.closest('.gruposFormulario') || input.parentElement;
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

function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

btnEnviar.addEventListener('pointerdown', function (e) {
    const rect = btnEnviar.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    btnEnviar.style.setProperty('--wave-x', x + 'px');
    btnEnviar.style.setProperty('--wave-y', y + 'px');
    btnEnviar.classList.add('btn-wave');
    setTimeout(() => btnEnviar.classList.remove('btn-wave'), 500);
});

btnEnviar.addEventListener('mouseenter', function () {
    btnEnviar.classList.add('btn-hover');
});

btnEnviar.addEventListener('mouseleave', function () {
    btnEnviar.classList.remove('btn-hover');
});

btnEnviar.addEventListener('click', function () {
    btnEnviar.blur();
});

form.addEventListener('submit', async function (e) {
    e.preventDefault();
    limpiarError(emailInput);

    const email = emailInput.value.trim();

    if (!email) {
        mostrarError(emailInput, 'El correo electrónico es obligatorio.');
        return;
    }
    if (!validarEmail(email)) {
        mostrarError(emailInput, 'Formato de correo incorrecto.');
        return;
    }

    btnEnviar.disabled = true;
    btnEnviar.textContent = 'Enviando...';

    try {
        const response = await fetch('../backend/recuperar_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });
        const raw = await response.text();
        let data = {};
        try { data = raw ? JSON.parse(raw) : {}; } catch (e) { data = { raw }; }

        if (!response.ok) {
            throw new Error(data.message || 'No se pudo procesar la solicitud.');
        }

        mostrarToast('Revisá tu correo electrónico. Te hemos enviado las instrucciones.', 'success');

        setTimeout(() => {
            window.location.href = '../login/login.php';
        }, 2500);
    } catch (error) {
        mostrarToast(error.message || 'Error al enviar el correo.', 'error');
        btnEnviar.disabled = false;
        btnEnviar.textContent = 'Enviar';
    }
});
