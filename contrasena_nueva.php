<?php
session_start();
require_once 'db.php';

// Inicializar variables
$error = '';
$success = '';

// Verificar si el token está presente en la URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Procesar el formulario cuando se envíe
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_pass = $_POST['new_pass'];
        $confirm_new_pass = $_POST['confirm_new_pass'];

        // Verificar que la nueva contraseña cumpla con los requisitos
        if (strlen($new_pass) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } elseif ($new_pass == $confirm_new_pass) {
            // Las contraseñas coinciden, actualizar la base de datos
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

            // Preparar la consulta para actualizar la contraseña
            if ($stmt = $conn->prepare("UPDATE usuarios SET pass = ?, token_recuperacion = NULL WHERE token_recuperacion = ?")) {
                $stmt->bind_param("ss", $hashed_password, $token);
                if ($stmt->execute()) {
                    $success = 'La contraseña ha sido actualizada correctamente.';
                } else {
                    $error = 'Error al actualizar la contraseña.';
                }
                $stmt->close();
            }
        } else {
            $error = "Las contraseñas no coinciden.";
        }
    }
} else {
    $error = 'No se ha proporcionado un token válido.';
}

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Establecer Nueva Contraseña</title>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Establecer Nueva Contraseña</h2>
    
    <?php if ($error): ?>
        <div class='alert alert-danger'><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class='alert alert-success'><?php echo $success; ?></div>
        <script>
            // Redirigir después de 5 segundos
            setTimeout(function() {
                window.location.href = "https://trackitsellit.oralisisdataservice.cl/";
            }, 5000);
        </script>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form action="contrasena_nueva.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
        <div class="form-group">
            <label>Nueva Contraseña:</label>
            <input type="password" name="new_pass" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirmar Nueva Contraseña:</label>
            <input type="password" name="confirm_new_pass" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
    </form>
    <?php endif; ?>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
