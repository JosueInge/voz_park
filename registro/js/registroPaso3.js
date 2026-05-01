
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
});
