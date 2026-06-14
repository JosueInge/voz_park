
// Validaciones
const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');
const phoneInput = document.getElementById('phone');
const locationInput = document.getElementById('location');
const btnContinuar = document.querySelector('.btn-continuar');

function mostrarError(input, mensaje) {
    const container = input.closest('.grupoInput') || input.parentElement;
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
    }, 450);

    if (input.errorTimeout) {
        clearTimeout(input.errorTimeout);
    }
    input.errorTimeout = setTimeout(() => {
        limpiarError(input);
    }, 2500);
}

function limpiarError(input) {
    if (input.errorTimeout) {
        clearTimeout(input.errorTimeout);
        input.errorTimeout = null;
    }

    const container = input.closest('.grupoInput') || input.parentElement;
    const error = container.querySelector('.mensaje-error');
    if (error) {
        error.style.display = 'none';
        error.textContent = '';
    }
    input.classList.remove('input-error', 'shake');
}

function limpiarTodosLosErrores() {
    [nameInput, emailInput, phoneInput, locationInput].forEach((input) => limpiarError(input));
}

btnContinuar.addEventListener('click', function(event) {
    // Efecto onda (ripple)
    const createRipple = (event) => {
        const button = event.currentTarget;
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        circle.classList.add('ripple');
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.offsetX - radius}px`;
        circle.style.top = `${event.offsetY - radius}px`;
        // Eliminar ripples previos
        const oldRipple = button.querySelector('.ripple');
        if (oldRipple) oldRipple.remove();
        button.appendChild(circle);
    };
    createRipple(event);

    // Nombre
    function validarNombre(nombre) {
        return /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,50}$/.test(nombre.trim());
    }

    // Email
    function validarEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    }

    // Teléfono (8 dígitos)
    function validarTelefono(telefono) {
        return /^\d{8}$/.test(telefono.trim());
    }

    limpiarTodosLosErrores();

    // Validaciones y mensajes por campo SOLO al hacer clic
    if (!nameInput.value.trim() || !validarNombre(nameInput.value)) {
        mostrarError(nameInput, 'El nombre es obligatorio.');
        return;
    }
    if (!emailInput.value.trim()) {
        mostrarError(emailInput, 'El correo electrónico es obligatorio.');
        return;
    }
    if (!validarEmail(emailInput.value)) {
        mostrarError(emailInput, 'Formato de correo incorrecto.');
        return;
    }
    if (!phoneInput.value.trim()) {
        mostrarError(phoneInput, 'El número de teléfono es obligatorio.');
        return;
    }
    if (!validarTelefono(phoneInput.value)) {
        mostrarError(phoneInput, 'Formato de teléfono incorrecto.');
        return;
    }
    if (!locationInput.value.trim()) {
        mostrarError(locationInput, 'La zona de residencia es obligatoria.');
        return;
    }
    // Guardar datos en localStorage
    localStorage.setItem('vozpark_nombre', nameInput.value.trim());
    localStorage.setItem('vozpark_email', emailInput.value.trim());
    localStorage.setItem('vozpark_phone', phoneInput.value.trim());
    localStorage.setItem('vozpark_location', locationInput.value.trim());
    // Esperar un momento para mostrar el efecto ripple antes de redirigir
    setTimeout(function() {
        window.location.href = 'registroPaso2.php';
    }, 350); // 350ms para que el efecto se vea bien
});
