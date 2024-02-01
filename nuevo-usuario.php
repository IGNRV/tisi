<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que hay una sesión de usuario activa
if (!isset($_SESSION['id'])) {
    die("Acceso denegado: Usuario no logueado.");
}

$id_usuario_padre = $_SESSION['id']; // ID del usuario actual

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y validar los datos del formulario para usuarios_hijos
    $nombre_hijo = trim($_POST['nombre_hijo']);
    $apellido_hijo = trim($_POST['apellido_hijo']);
    $email_hijo = trim($_POST['email_hijo']);
    $fecha_nacimiento_hijo = trim($_POST['fecha_nacimiento_hijo']);
    $password_hijo = trim($_POST['password_hijo']);
    $confirm_password_hijo = trim($_POST['confirm_password_hijo']);

    if ($password_hijo === $confirm_password_hijo) {
        $password_hijo = password_hash($password_hijo, PASSWORD_DEFAULT); // Encriptar contraseña
    }

    // Fecha actual para 'date_created'
    $date_created = date('Y-m-d H:i:s');

    // Preparar la consulta SQL para insertar el usuario hijo
    $insert_hijo_query = "INSERT INTO usuarios_hijos (id_usuario_padre, nombre, apellido, email, fecha_nacimiento, date_created, password) VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt_hijo = $conn->prepare($insert_hijo_query)) {
        $stmt_hijo->bind_param("issssss", $id_usuario_padre, $nombre_hijo, $apellido_hijo, $email_hijo, $fecha_nacimiento_hijo, $date_created, $password_hijo);
        if ($stmt_hijo->execute()) {
            // Establecer mensaje de éxito
            $_SESSION['success_message'] = "Usuario hijo creado con éxito.";
            // Redirigir a buscar_productos.php
            header("Location: welcome.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error al crear el usuario hijo: " . $conn->error;
        }
        $stmt_hijo->close();
    } else {
        $_SESSION['error_message'] = "Error al preparar la consulta para usuarios_hijos: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nuevo Usuario Hijo</title>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto">
            <h1 class="mb-4 text-center">Crear Usuario Hijo</h1>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class='alert alert-danger'><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            <form action="nuevo-usuario.php" method="post">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre_hijo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Apellido:</label>
                    <input type="text" name="apellido_hijo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email_hijo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento_hijo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="password_hijo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirmar Contraseña:</label>
                    <input type="password" name="confirm_password_hijo" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Crear Usuario Hijo</button>
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
