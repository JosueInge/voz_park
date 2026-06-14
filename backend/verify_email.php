<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/jwt.php';

$token = trim((string)($_GET['token'] ?? ''));
$statusTitle = 'Verificacion no valida';
$statusMessage = 'No se pudo verificar tu correo electronico.';
$statusColor = '#9b1c1c';

if ($token !== '') {
    $payload = jwtDecode($token);

    if (is_array($payload)
        && ($payload['purpose'] ?? '') === 'verify_email'
        && !empty($payload['correo'])
    ) {
        try {
            $pdo = getDB();
            $correo = strtolower(trim((string)$payload['correo']));

            $stmt = $pdo->prepare(
                "UPDATE usuarios
                 SET email_verificado = TRUE
                 WHERE LOWER(correo) = :correo
                   AND auth_provider = 'local'"
            );
            $stmt->execute([':correo' => $correo]);

            if ($stmt->rowCount() > 0) {
                $statusTitle = 'Correo verificado';
                $statusMessage = 'Tu cuenta ya fue verificada. Ya puedes iniciar sesion en VozPark.';
                $statusColor = '#2f6f18';
            } else {
                $checkStmt = $pdo->prepare(
                    "SELECT email_verificado
                     FROM usuarios
                     WHERE LOWER(correo) = :correo
                       AND auth_provider = 'local'
                     LIMIT 1"
                );
                $checkStmt->execute([':correo' => $correo]);
                $usuario = $checkStmt->fetch(PDO::FETCH_ASSOC) ?: null;

                if ($usuario && filter_var($usuario['email_verificado'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $statusTitle = 'Correo ya verificado';
                    $statusMessage = 'Tu cuenta ya estaba verificada. Puedes iniciar sesion en VozPark.';
                    $statusColor = '#2f6f18';
                }
            }
        } catch (Throwable $e) {
            $isDev = ($_ENV['APP_ENV'] ?? 'development') === 'development';
            $statusMessage = $isDev
                ? 'No se pudo verificar tu correo electronico: ' . $e->getMessage()
                : 'No se pudo verificar tu correo electronico.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificacion de correo</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f7faf4;
            font-family: 'Inter', sans-serif;
            color: #111827;
        }
        .card {
            width: min(92vw, 460px);
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 20px 45px rgba(0,0,0,.10);
            border-top: 6px solid <?php echo htmlspecialchars($statusColor, ENT_QUOTES, 'UTF-8'); ?>;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }
        p {
            margin: 0 0 20px;
            line-height: 1.5;
        }
        a {
            display: inline-block;
            background: #3B6D11;
            color: #ffffff;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 8px;
            font-weight: 600;
        }
        @media (max-width: 640px) {
            .card {
                width: calc(100vw - 24px);
                padding: 20px;
                border-radius: 12px;
            }
            h1 {
                font-size: 20px;
            }
            p {
                font-size: 14px;
            }
            a {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <main class="card">
        <h1><?php echo htmlspecialchars($statusTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="../login/login.php">Ir al inicio de sesion</a>
    </main>
</body>
</html>
