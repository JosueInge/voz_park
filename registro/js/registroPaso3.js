
// Mostrar datos del usuario en el paso 3
document.addEventListener('DOMContentLoaded', function() {
	const nombre = localStorage.getItem('vozpark_nombre') || 'Nombre';
	const email = localStorage.getItem('vozpark_email') || 'Correo';
	const location = localStorage.getItem('vozpark_location') || 'Lugar de residencia';
	const password = localStorage.getItem('vozpark_password') || '';

	const liNombre = document.getElementById('dato-nombre');
	const liEmail = document.getElementById('dato-email');
	const liLocation = document.getElementById('dato-location');

	if (liNombre) liNombre.innerHTML = '👤 ' + nombre;
	if (liEmail) liEmail.innerHTML = '📧 ' + email;
	if (liLocation) liLocation.innerHTML = '📍 ' + location;

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

	// Habilitar/deshabilitar botón continuar según el checkbox
	const checkbox = document.getElementById('confirm');
	const btnContinue = document.querySelector('.btn-continue');
	const btnAtras = document.querySelector('.btn-atras');
	const mensajeTerminos = document.getElementById('mensaje-terminos');
	const rippleDelayMs = 180;
	let mensajeTerminosTimer = null;

	if (checkbox && mensajeTerminos) {
		checkbox.addEventListener('change', function() {
			if (checkbox.checked) {
				mensajeTerminos.textContent = '';
			}
		});
	}

	if (btnAtras) {
		btnAtras.addEventListener('click', function(event) {
			crearRipple(event);
			setTimeout(() => {
				history.back();
			}, rippleDelayMs);
		});
	}

	// Envío de correo de verificación y toast
	if (btnContinue) {
		btnContinue.addEventListener('click', function(event) {
			crearRipple(event);
			setTimeout(() => {
				if (!checkbox || !checkbox.checked) {
					if (mensajeTerminos) {
						mensajeTerminos.textContent = 'Debes aceptar los términos y condiciones';
						if (mensajeTerminosTimer) {
							clearTimeout(mensajeTerminosTimer);
						}
						mensajeTerminosTimer = setTimeout(() => {
							mensajeTerminos.textContent = '';
						}, 2500);
					}
					return;
				}

				if (!password) {
					if (mensajeTerminos) {
						mensajeTerminos.textContent = 'Faltan datos del registro. Vuelve al paso anterior.';
						if (mensajeTerminosTimer) {
							clearTimeout(mensajeTerminosTimer);
						}
						mensajeTerminosTimer = setTimeout(() => {
							mensajeTerminos.textContent = '';
						}, 2800);
					}
					return;
				}

				btnContinue.disabled = true;

				// Mostrar toast
				const toast = document.getElementById('toast-verificacion');
				if (toast) {
					toast.textContent = 'Revisa tu correo electrónico para confirmar tu cuenta.';
					toast.style.display = 'block';
					setTimeout(() => {
						toast.classList.add('show');
					}, 10);
				}

				// 1) Registrar usuario local en BD.
				fetch('../backend/register_local.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						nombre: nombre,
						correo: email,
						zona: location,
						password: password
					})
				})
				.then(async (response) => {
					const raw = await response.text();
					let data = {};
					try {
						data = raw ? JSON.parse(raw) : {};
					} catch (e) {
						data = { raw };
					}

					if (!response.ok) {
						throw new Error(data.message || 'No se pudo guardar el registro');
					}

					return data;
				})
				.then(() => {
					// 2) Mantener envío de correo actual.
					return fetch('envioDeCorreoSMTP.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({
							email: email,
							nombre: nombre
						})
					});
				})
				.then(() => {
					// Limpiar datos temporales del registro.
					localStorage.removeItem('vozpark_password');

					// Ocultar toast y redirigir después de 2.5s
					setTimeout(() => {
						if (toast) {
							toast.classList.remove('show');
							setTimeout(() => { toast.style.display = 'none'; }, 300);
						}
						window.location.href = '../login/login.php';
					}, 2500);
				})
				.catch((error) => {
					if (toast) {
						toast.classList.remove('show');
						toast.style.display = 'none';
					}

					if (mensajeTerminos) {
						mensajeTerminos.textContent = error.message || 'No se pudo completar el registro';
						if (mensajeTerminosTimer) {
							clearTimeout(mensajeTerminosTimer);
						}
						mensajeTerminosTimer = setTimeout(() => {
							mensajeTerminos.textContent = '';
						}, 3200);
					}
				})
				.finally(() => {
					btnContinue.disabled = false;
				});
			}, rippleDelayMs);
		});
	}
});
