<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/jwt.php';
require __DIR__ . '/../registro/PHPMailer/Exception.php';
require __DIR__ . '/../registro/PHPMailer/PHPMailer.php';
require __DIR__ . '/../registro/PHPMailer/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput ?: '', true);

if (!is_array($input)) {
    jsonResponse(['success' => false, 'message' => 'JSON inválido'], 400);
}

$email = strtolower(trim((string)($input['email'] ?? '')));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error('Correo electrónico inválido.');
}

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE LOWER(correo) = :correo LIMIT 1");
    $stmt->execute([':correo' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        error('No encontramos una cuenta con ese correo.');
    }

    $resetToken = jwtEncode([
        'correo' => $email,
        'purpose' => 'reset_password'
    ]);

    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? 'http://192.168.1.6:8080/voz_park/backend'), '/');
    $baseUrl = preg_replace('#/backend$#', '', $baseUrl);
    $resetUrl = $baseUrl . '/login/reset_password.php?token=' . urlencode($resetToken);

    $from = 'd4660140@gmail.com';
    $fromName = 'VozPark';
    $gmailPassword = 'rakr gvgw njxc gfjx';

    $subject = 'Recuperación de contraseña - VozPark';
    $message = "Hola {$usuario['nombre']},<br><br>"
        . "Recibimos una solicitud para restablecer tu contraseña en VozPark.<br><br>"
        . "Hacé clic en el botón de abajo para crear una nueva contraseña:<br><br>"
        . "<a href=\"$resetUrl\" style=\"display:inline-block;background:#3B6D11;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;\">Restablecer contraseña</a><br><br>"
        . "Si no solicitaste este cambio, podés ignorar este mensaje.<br><br>"
        . "Saludos,<br>El equipo de VozPark";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $from;
    $mail->Password = $gmailPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom($from, $fromName);
    $mail->addAddress($email, $usuario['nombre']);
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $message;
    $mail->CharSet = 'UTF-8';
    $mail->send();

    success(['email' => $email], 'Correo de recuperación enviado exitosamente.');
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'No se pudo enviar el correo de recuperación. Intentalo de nuevo más tarde.'
    ], 500);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Error al procesar la solicitud.'
    ], 500);
}
