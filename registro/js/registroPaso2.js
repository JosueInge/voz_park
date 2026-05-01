
// Validación en tiempo real de la contraseña
document.addEventListener('DOMContentLoaded', function() {
	const passwordInput = document.getElementById('password');
	const confirmarInput = document.getElementById('confirmarPassword');
	const li8 = document.querySelector('.val-8caracteres');
	const liMayus = document.querySelector('.val-mayuscula');
	const liNum = document.querySelector('.val-numero');
	const btnContinue = document.querySelector('.btn-continue');

	function validarPassword() {
		const value = passwordInput.value;
		// 8 caracteres
		if (value.length >= 8) {
			li8.classList.add('cumplido');
		} else {
			li8.classList.remove('cumplido');
		}
		// Mayúscula
		if (/[A-ZÁÉÍÓÚÜÑ]/.test(value)) {
			liMayus.classList.add('cumplido');
		} else {
			liMayus.classList.remove('cumplido');
		}
		// Número
		if (/[0-9]/.test(value)) {
			liNum.classList.add('cumplido');
		} else {
			liNum.classList.remove('cumplido');
		}
	}

	passwordInput.addEventListener('input', validarPassword);

	// Toast
	function mostrarToast(mensaje) {
		let toast = document.createElement('div');
		toast.className = 'toast-vozpark';
		toast.textContent = mensaje;
		document.body.appendChild(toast);
		setTimeout(() => {
			toast.classList.add('show');
		}, 10);
		setTimeout(() => {
			toast.classList.remove('show');
			setTimeout(() => toast.remove(), 300);
		}, 2500);
	}

	btnContinue.addEventListener('click', function(e) {
		// Validar requisitos de contraseña
		if (!(li8.classList.contains('cumplido') && liMayus.classList.contains('cumplido') && liNum.classList.contains('cumplido'))) {
			mostrarToast('Completa los requisitos de la contraseña.');
			return;
		}
		// Validar coincidencia de confirmación
		if (passwordInput.value !== confirmarInput.value) {
			mostrarToast('La contraseña de confirmación no coincide.');
			return;
		}
		// Si todo está bien, redirige
		window.location.href = 'registroPaso3.php';
	});
});
