<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y validar los datos del formulario
    $usuario = trim($_POST['usuario']);
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $numero_de_telefono = trim($_POST['numero_de_telefono']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $pass = trim($_POST['pass']);
    $confirm_pass = trim($_POST['confirm_pass']);
    $estado_suscripcion = 0; // Valor por defecto
    $cuenta_activada = 0;
    $token_activacion = bin2hex(random_bytes(50));

    // Verificar la longitud de la contraseña
    if (strlen($pass) < 8) {
        $_SESSION['error_message'] = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($pass === $confirm_pass) {
        // Verificar si las contraseñas coinciden
        $pass = password_hash($pass, PASSWORD_DEFAULT); // Encriptar contraseña

        // Preparar la consulta SQL para insertar el usuario
        $insert_query = "INSERT INTO usuarios (usuario, nombre, apellidos, email, numero_de_telefono, fecha_nacimiento, pass, estado_suscripcion, cuenta_activada, token_activacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($insert_query)) {
            $stmt->bind_param("sssssssiss", $usuario, $nombre, $apellidos, $email, $numero_de_telefono, $fecha_nacimiento, $pass, $estado_suscripcion, $cuenta_activada, $token_activacion);
            if($stmt->execute()){
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
                    $mail->Subject = 'Activa tu Cuenta';
                    $enlace_activacion = "https://trackitsellit.oralisisdataservice.cl/activar_cuenta.php?token=" . $token_activacion;
                    $mail->Body    = "Hola " . $nombre . ",<br>Para activar tu cuenta, por favor haz clic en el siguiente enlace: <a href='" . $enlace_activacion . "'>Activar Cuenta</a>";

                    $mail->send();
                    $_SESSION['success_message'] = 'Usuario creado con éxito. Se ha enviado un correo electrónico para activar la cuenta.';
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "El mensaje no pudo ser enviado. Error de Mailer: {$mail->ErrorInfo}";
                }

                header("Location: https://trackitsellit.oralisisdataservice.cl/");
                exit();
            } else {
                $_SESSION['error_message'] = "Error al crear el usuario.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Error al preparar la consulta: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crear Usuario</title>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h2>Crear Usuario</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class='alert alert-danger'><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <form action="crear_usuario.php" method="post">
        <div class="form-group">
            <label>Usuario:</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Apellidos:</label>
            <input type="text" name="apellidos" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Número de Teléfono:</label>
            <input type="text" name="numero_de_telefono" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Fecha de Nacimiento:</label>
            <input type="date" name="fecha_nacimiento" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Contraseña:</label>
            <input type="password" name="pass" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirmar Contraseña:</label>
            <input type="password" name="confirm_pass" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Usuario</button>
    </form>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
