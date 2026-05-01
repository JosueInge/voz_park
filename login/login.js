
// Mostrar/ocultar contraseña
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
if (passwordInput && togglePassword) {
	togglePassword.addEventListener('click', function () {
		const isPassword = passwordInput.type === 'password';
		passwordInput.type = isPassword ? 'text' : 'password';
		togglePassword.src = isPassword ? 'imagenes/ojoCerrado.webp' : 'imagenes/ojoAbierto.webp';
		togglePassword.alt = isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña';
	});
	// Accesibilidad: permitir con Enter o barra espaciadora
	togglePassword.addEventListener('keydown', function (e) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			togglePassword.click();
		}
	});
}
