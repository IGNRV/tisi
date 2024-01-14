<?php
require_once 'db.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mostrar mensaje de éxito si existe
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    // Limpiar mensaje de éxito
    unset($_SESSION['success_message']);
}

// Mostrar mensaje de error si existe
if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    // Limpiar mensaje de error
    unset($_SESSION['error_message']);
}


// Verifica si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepara y ejecuta la consulta
    if ($stmt = $conn->prepare("SELECT id, usuario, nombre, pass FROM usuarios WHERE email = ?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Verifica si el usuario existe
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $usuario, $nombre, $hashed_password);
            $stmt->fetch();

            // Verifica la contraseña
            if (password_verify($password, $hashed_password)) {
                // Crea las variables de sesión
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['nombre'] = $nombre;

                // Redirecciona al usuario a la página de inicio
                header("location: welcome.php");
            } else {
                echo "<div class='alert alert-danger' role='alert'>Contraseña incorrecta.</div>";
            }
        } else {
            echo "<div class='alert alert-danger' role='alert'>No se encontró ninguna cuenta con ese correo electrónico.</div>";
        }

        $stmt->close();
    }
    // Cierra la conexión
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
        /* Estilo adicional para centrar verticalmente el formulario de inicio de sesión */
        .vertical-center {
            min-height: 100vh; /* Altura de la ventana del navegador */
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row vertical-center justify-content-center">
        <div class="col-sm-8 col-md-6 col-lg-4">
            <div class="text-center">
                <img src="https://trackitsellit.oralisisdataservice.cl/images/logo.png" alt="Logo" class="img-fluid mb-4" style="max-height: 150px;">
            </div>

            <!-- Formulario de inicio de sesión con estilos Bootstrap -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="mt-4">
                <div class="form-group">
                    <input placeholder="Ingresa tu correo electrónico" type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <input placeholder="Ingresa tu contraseña" type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>
            <div class="mt-3">
                <a href="recuperar_contrasena.php" class="btn btn-warning btn-block">Recuperar Contraseña</a>
                </div>
        <div class="mt-3">
            <a href="https://trackitsellit.oralisisdataservice.cl/crear_usuario.php" class="btn btn-secondary btn-block">Crear Usuario</a>
        </div>
    </div>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
