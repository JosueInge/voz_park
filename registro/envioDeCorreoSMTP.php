<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Recibe los datos del fetch
$data = json_decode(file_get_contents('php://input'), true);
$to = isset($data['email']) ? $data['email'] : '';
$nombre = isset($data['nombre']) ? $data['nombre'] : '';

// Correo y contraseña de aplicación para Gmail SMTP
$from = 'd4660140@gmail.com'; // Correo de gmail 
$fromName = 'VozPark';
$gmailPassword = 'rakr gvgw njxc gfjx'; // Contraseña de aplicación de Gmail

// Mensaje básico de verificación
$subject = 'Verifica tu cuenta en VozPark';
$message = "Hola $nombre,\n\nGracias por registrarte en VozPark.\nPor favor, confirma tu cuenta haciendo clic en el enlace de verificación (esto es solo una prueba).\n\nSaludos,\nEl equipo de VozPark";

if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $from;
        $mail->Password = $gmailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($from, $fromName);
        $mail->addAddress($to, $nombre);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->CharSet = 'UTF-8';

        $mail->send();
        http_response_code(200);
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Error al enviar: ', $mail->ErrorInfo;
    }
} else {
    http_response_code(400);
    echo 'Correo inválido';
}
exit;
