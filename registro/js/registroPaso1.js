
// Mensaje toast
function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    // Limpiar cualquier timeout anterior
    if (toast.hideTimeout) clearTimeout(toast.hideTimeout);
    toast.hideTimeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 10000); // tiempo que se muestra el mensaje
}

// Validaciones
const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');
const phoneInput = document.getElementById('phone');
const locationInput = document.getElementById('location');
const btnContinuar = document.querySelector('.btn-continuar');

//Nombre
function validarNombre(nombre) {
    return /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,50}$/.test(nombre.trim());
}

//Email
function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
}

//Teléfono
function validarTelefono(telefono) {
    return /^\d{8}$/.test(telefono.trim());
}

//Zona de residencia
function validarZona(zona) {
    return typeof zona === 'string' && zona.trim().length >= 10 && zona.trim().length <= 25 && /^[A-Za-zÁÉÍÓÚáéíóúÑñ 0-9.,-]+$/.test(zona.trim());
}

btnContinuar.addEventListener('click', function() {
    // Validaciones y mensajes toast SOLO al hacer clic
    if (!validarNombre(nameInput.value)) {
        showToast('Error: El nombre debe tener al menos 3 letras, máximo 50, solo ingrese letras.');
        return;
    }
    if (!validarEmail(emailInput.value)) {
        showToast('Error: El correo electrónico no es válido.');
        return;
    }
    if (!validarTelefono(phoneInput.value)) {
        showToast('Error: El numero de telefono solo debe contener 8 numeros.');
        return;
    }
    if (!validarZona(locationInput.value)) {
        showToast('Error: La zona de residencia debe tener como minimo 10 letras y como máximo 25, puede ingresar: letras, números, espacios y caracteres especiales como ., -');
        return;
    }
    // Si todo es válido, continuar
    window.location.href = 'registroPaso2.php';
});
