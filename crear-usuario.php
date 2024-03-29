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

                // Obtener el ID del usuario recién insertado
        $id_usuario = $conn->insert_id;

        // Preparar la consulta SQL para insertar en la tabla categorias
        $insert_categoria_query = "INSERT INTO categorias (nombre_categoria, id_usuario) VALUES (?, ?)";

        if ($stmt_categoria = $conn->prepare($insert_categoria_query)) {
            // Establecer 'ETC' como nombre_categoria y el ID del usuario recién creado
            $nombre_categoria = 'ETC';
            $stmt_categoria->bind_param("si", $nombre_categoria, $id_usuario);

            if (!$stmt_categoria->execute()) {
                // Manejar error al insertar en la tabla categorias
                $_SESSION['error_message'] .= " Error al insertar en la tabla categorias.";
            }
            $stmt_categoria->close();
        } else {
            $_SESSION['error_message'] .= " Error al preparar la consulta para categorias: " . $conn->error;
        }
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
                    $_SESSION['success_message'] = 'Usuario creado con éxito. Se ha enviado un correo electrónico para activar la cuenta. Revisa el "Spam" o los "Correos no deseados" en caso de que no aparezca en tu bandeja de entrada.';
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
    <link rel="alternate" href="https://trackitsellit.oralisisdataservice.cl/crear-usuario.php" hreflang="es"/>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Asegúrate de incluir esta etiqueta para la responsividad -->
    <meta name="description" content="Únete a Track It / Sell It y comienza a transformar la manera en que gestionas tu inventario y procesas tus ventas. Regístrate en minutos y descubre herramientas potentes y personalizables para impulsar el éxito de tu negocio. ¡Crea tu cuenta hoy y simplifica tus operaciones comerciales!">
</head>
<body>

<div class="container mt-5"> <!-- mt-5 añade un poco de espacio en la parte superior -->
    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto"> <!-- Centrar el formulario y hacerlo más estrecho en pantallas grandes -->
            <h1 class="mb-4 text-center">Crear Usuario</h1>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class='alert alert-danger'><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            <form action="crear-usuario.php" method="post">
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
                <button type="submit" class="btn btn-primary btn-block">Crear Usuario</button> <!-- btn-block hace que el botón se extienda al ancho completo en pantallas pequeñas -->
            </form>
        </div>
    </div>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
