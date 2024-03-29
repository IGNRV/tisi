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

    // Prepara y ejecuta la consulta, incluyendo la verificación de cuenta_activada
    if ($stmt = $conn->prepare("SELECT id, usuario, nombre, pass, cuenta_activada FROM usuarios WHERE email = ?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Verifica si el usuario existe y si la cuenta está activada
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $usuario, $nombre, $hashed_password, $cuenta_activada);
            $stmt->fetch();

            if ($cuenta_activada != 1) {
                echo "<div class='alert alert-danger' role='alert'>Tu cuenta no está activada. Por favor, verifica tu correo electrónico o contacta al soporte.</div>";
            } else {
            // Verifica la contraseña
            if (password_verify($password, $hashed_password)) {
            // Crea las variables de sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $id;
            $_SESSION['nombre'] = $nombre;
            // Redirecciona al usuario a la página de bienvenida
            header("location: welcome.php");
        } else {
            echo "<div class='alert alert-danger' role='alert'>Contraseña incorrecta.</div>";
        }
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
    <title>Inicia sesión - Track It / Sell It</title>
    <link rel="alternate" href="https://trackitsellit.oralisisdataservice.cl/" hreflang="es"/>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Accede a Track It / Sell It para gestionar eficientemente tu inventario y ventas. Nuestro sistema seguro y fácil de usar está diseñado para maximizar tu productividad. ¡Inicia sesión ahora y lleva tu negocio al siguiente nivel!">
    <style>
        /* Estilo adicional para centrar verticalmente el formulario de inicio de sesión */
        .vertical-center {
            min-height: 100vh; /* Altura de la ventana del navegador */
            display: flex;
            align-items: center;
        }
        .etiquetah1 {
    font-size: 0.8rem; /* Ejemplo de tamaño de fuente, ajusta según sea necesario */
    color: #333; /* Ejemplo de color de texto, ajusta para que coincida con tu esquema de color */
    margin-bottom: 1rem; /* Espacio debajo del H1 */
    /* Otros estilos que podrías querer ajustar: peso de la fuente, espaciado de letras, etc. */
}

    </style>
</head>
<body>
<div class="container">
    <div class="row vertical-center justify-content-center">
        <div class="col-sm-8 col-md-6 col-lg-4">
            <div class="text-center">
                <img src="https://trackitsellit.oralisisdataservice.cl/images/logo.png" alt="Logo" class="img-fluid mb-4" style="max-height: 150px;">
                <h1 class="etiquetah1">Sistema online de inventario y punto de venta</h1>
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
                <a href="recuperar-contrasena.php" class="btn btn-warning btn-block">Recuperar Contraseña</a>
                </div>
        <div class="mt-3">
            <a href="https://trackitsellit.oralisisdataservice.cl/crear-usuario.php" class="btn btn-secondary btn-block">Crear Usuario</a>
        </div>
    </div>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
