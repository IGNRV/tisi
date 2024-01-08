<?php
require_once 'db.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h2 class="mt-5">Iniciar Sesión</h2>

            <!-- Formulario de inicio de sesión con estilos Bootstrap -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="mt-4">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
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
