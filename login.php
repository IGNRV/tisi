<?php
require_once 'db.php';

session_start();

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
                echo "Contraseña incorrecta.";
            }
        } else {
            echo "No se encontró ninguna cuenta con ese correo electrónico.";
        }

        $stmt->close();
    }
}

// Cierra la conexión
$conn->close();
?>

<!-- Formulario de inicio de sesión -->
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="email">Correo Electrónico:</label><br>
    <input type="email" id="email" name="email" required><br>
    <label for="password">Contraseña:</label><br>
    <input type="password" id="password" name="password" required><br>
    <input type="submit" value="Iniciar Sesión">
</form>
