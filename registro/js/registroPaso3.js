
// Mostrar datos del usuario en el paso 3
document.addEventListener('DOMContentLoaded', function() {
	const nombre = localStorage.getItem('vozpark_nombre') || 'Nombre';
	const email = localStorage.getItem('vozpark_email') || 'Correo';
	const location = localStorage.getItem('vozpark_location') || 'Lugar de residencia';

	const liNombre = document.getElementById('dato-nombre');
	const liEmail = document.getElementById('dato-email');
	const liLocation = document.getElementById('dato-location');

	if (liNombre) liNombre.innerHTML = '👤 ' + nombre;
	if (liEmail) liEmail.innerHTML = '📧 ' + email;
	if (liLocation) liLocation.innerHTML = '📍 ' + location;

	// Habilitar/deshabilitar botón continuar según el checkbox
	const checkbox = document.getElementById('confirm');
	const btnContinue = document.querySelector('.btn-continue');
	if (checkbox && btnContinue) {
		btnContinue.disabled = !checkbox.checked;
		checkbox.addEventListener('change', function() {
			btnContinue.disabled = !checkbox.checked;
		});
	}
	// Envío de correo de verificación y toast
	if (btnContinue) {
		btnContinue.addEventListener('click', function() {
			// Mostrar toast
			const toast = document.getElementById('toast-verificacion');
			if (toast) {
				toast.textContent = 'Revisa tu correo electrónico para confirmar tu cuenta.';
				toast.style.display = 'block';
				setTimeout(() => {
					toast.classList.add('show');
				}, 10);
			}

			// Enviar correo de verificación (AJAX a PHP)
			fetch('envioDeCorreoSMTP.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					email: email,
					nombre: nombre
				})
			})
			.then(() => {
				// Ocultar toast y redirigir después de 2.5s
				setTimeout(() => {
					if (toast) {
						toast.classList.remove('show');
						setTimeout(() => { toast.style.display = 'none'; }, 300);
					}
					window.location.href = '../login/login.php';
				}, 2500);
			});
		});
	}
});
