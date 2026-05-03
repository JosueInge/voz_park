<?php
// Configura aquí tu correo real de envío
$from = 'd4660140@gmail.com'; // Cambia esto por tu correo real
$fromName = 'VozPark';

// Recibe los datos del fetch
$data = json_decode(file_get_contents('php://input'), true);
$to = isset($data['email']) ? $data['email'] : '';
$nombre = isset($data['nombre']) ? $data['nombre'] : '';

// Mensaje básico de verificación
$subject = 'Verifica tu cuenta en VozPark';
$message = "Hola $nombre,\n\nGracias por registrarte en VozPark.\nPor favor, confirma tu cuenta haciendo clic en el enlace de verificación (esto es solo una prueba).\n\nSaludos,\nEl equipo de VozPark";

$headers = "From: $from\r\n" .
           "Reply-To: $from\r\n" .
           "X-Mailer: PHP/" . phpversion();

// Solo enviar si el correo es válido
if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
    mail($to, $subject, $message, $headers);
}

// Respuesta vacía
http_response_code(200);
exit;