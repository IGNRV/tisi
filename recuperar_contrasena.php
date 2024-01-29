<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar si el correo electrónico existe en la base de datos
    if ($stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE email = ?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // Usuario existe, generar token de recuperación
            $token_recuperacion = bin2hex(random_bytes(50));

            // Almacenar el token en la base de datos
            $stmt->bind_result($id, $nombre); // Asegúrate de obtener también el nombre
            $stmt->fetch();
            $stmt->close();

            $update_stmt = $conn->prepare("UPDATE usuarios SET token_recuperacion = ? WHERE id = ?");
            $update_stmt->bind_param("si", $token_recuperacion, $id);
            $update_stmt->execute();
            $update_stmt->close();

            // Enviar correo electrónico con PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor
                $mail->isSMTP();
                $mail->Host       = 'smtp-mail.outlook.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'notificacionestisi@outlook.com';
                $mail->Password   = '2JASnXqGgS4J';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Remitentes y destinatarios
                $mail->setFrom('notificacionestisi@outlook.com', 'TrackIt/SellIt');
                $mail->addAddress($email, $nombre);
                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Recupera tu password';
                $enlace_recuperacion = "https://trackitsellit.oralisisdataservice.cl/contrasena_nueva.php?token=" . $token_recuperacion;
                $mail->Body = "Por favor, haz clic en el siguiente enlace para recuperar tu contraseña: <a href='" . $enlace_recuperacion . "'>Recuperar Contraseña</a>";

                $mail->send();
                echo 'Se ha enviado un correo electrónico con las instrucciones de recuperación.';
            } catch (Exception $e) {
                echo "El mensaje no pudo ser enviado. Error de Mailer: {$mail->ErrorInfo}";
            }
        } else {
            echo "No se encontró ninguna cuenta con ese correo electrónico.";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Recuperar Contraseña</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <meta name="description" content="Restablece tu contraseña de Track It / Sell It con facilidad. Nuestro proceso seguro de recuperación de contraseñas te ayuda a retomar rápidamente el control de tu gestión de inventario y ventas. Recupera el acceso a tu cuenta de forma segura y continúa optimizando tu negocio.">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h1 class="mt-5">Recuperar Contraseña</h1>
            <form method="post" action="recuperar_contrasena.php" class="mt-4">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Instrucciones</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
