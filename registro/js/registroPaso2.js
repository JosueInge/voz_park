
// Validación en tiempo real de la contraseña
document.addEventListener('DOMContentLoaded', function() {
	const passwordInput = document.getElementById('password');
	const confirmarInput = document.getElementById('confirmarPassword');
	const li8 = document.querySelector('.val-8caracteres');
	const liMayus = document.querySelector('.val-mayuscula');
	const liNum = document.querySelector('.val-numero');
	const btnContinue = document.querySelector('.btn-continue');
	const btnAtras = document.querySelector('.btn-atras');

	function crearRipple(event) {
		const button = event.currentTarget;
		const circle = document.createElement('span');
		const diameter = Math.max(button.clientWidth, button.clientHeight);
		const radius = diameter / 2;

		circle.classList.add('ripple');
		circle.style.width = circle.style.height = `${diameter}px`;
		circle.style.left = `${event.offsetX - radius}px`;
		circle.style.top = `${event.offsetY - radius}px`;

		const oldRipple = button.querySelector('.ripple');
		if (oldRipple) oldRipple.remove();

		button.appendChild(circle);
	}

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
		[passwordInput, confirmarInput].forEach((input) => limpiarError(input));
	}

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
	btnContinue.addEventListener('pointerdown', crearRipple);
	if (btnAtras) {
		btnAtras.addEventListener('pointerdown', crearRipple);
	}
	passwordInput.addEventListener('input', function() {
		limpiarError(passwordInput);
	});
	confirmarInput.addEventListener('input', function() {
		limpiarError(confirmarInput);
	});

	btnContinue.addEventListener('click', function(e) {
		const password = passwordInput.value;
		const confirmacion = confirmarInput.value;

		limpiarTodosLosErrores();

		if (!password.trim()) {
			mostrarError(passwordInput, 'La contraseña es obligatoria.');
			return;
		}
		if (password.length < 8) {
			mostrarError(passwordInput, 'Debe tener al menos 8 caracteres.');
			return;
		}
		if (!/[A-ZÁÉÍÓÚÜÑ]/.test(password)) {
			mostrarError(passwordInput, 'Debe incluir una mayúscula.');
			return;
		}
		if (!/[0-9]/.test(password)) {
			mostrarError(passwordInput, 'Debe incluir un numero.');
			return;
		}

		if (password !== confirmacion) {
			mostrarError(confirmarInput, 'Las contraseñas no coinciden.');
			return;
		}

		// Guardar contraseña validada para el registro final en paso 3.
		localStorage.setItem('vozpark_password', password);

		// Si todo está bien, redirige
		window.location.href = 'registroPaso3.php';
	});

	// Toggle visibilidad de contraseñas
	document.querySelectorAll('.iconoOjo').forEach(function(img) {
		img.addEventListener('click', function () {
			const input = this.closest('.contenedorContraseña').querySelector('input');
			if (input) {
				const isPassword = input.type === 'password';
				input.type = isPassword ? 'text' : 'password';
				this.src = isPassword ? 'imagenes/ojoAbierto.webp' : 'imagenes/ojoCerrado.webp';
				this.alt = isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña';
			}
		});
	});
});
