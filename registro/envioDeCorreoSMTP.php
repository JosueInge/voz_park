<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../backend/database.php';
require_once __DIR__ . '/../backend/jwt.php';
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

$appUrl = rtrim((string)($_ENV['APP_URL'] ?? 'http://192.168.1.63:8080/voz_park/backend'), '/');
$verificationToken = jwtEncode([
    'correo' => strtolower(trim($to)),
    'purpose' => 'verify_email'
]);
$verificationUrl = $appUrl . '/verify_email.php?token=' . urlencode($verificationToken);

// Mensaje básico de verificación
$subject = 'Verifica tu cuenta en VozPark';
$message = "Hola $nombre,<br><br>Gracias por registrarte en VozPark.<br>Por favor, confirma tu cuenta haciendo clic en el boton de verificacion.<br><br><a href=\"$verificationUrl\" style=\"display:inline-block;background:#3B6D11;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600;\">Verificar</a><br><br>Saludos,<br>El equipo de VozPark";

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
        $mail->isHTML(true);
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
