<?php
require_once 'db.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $numero_de_telefono = $_POST['numero_de_telefono'];

    // Encripta la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepara y ejecuta la consulta
    if ($stmt = $conn->prepare("INSERT INTO usuarios (usuario, nombre, email, pass, numero_de_telefono) VALUES (?, ?, ?, ?, ?)")) {
        $stmt->bind_param("sssss", $usuario, $nombre, $email, $hashed_password, $numero_de_telefono);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<div class='alert alert-success' role='alert'>Registro exitoso.</div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error en el registro.</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error en la consulta.</div>";
    }

    // Cierra la conexión
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h2 class="mt-5">Registro de Usuario</h2>

            <!-- Formulario de registro con estilos Bootstrap -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="mt-4">
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="numero_de_telefono">Número de Teléfono:</label>
                    <input type="text" id="numero_de_telefono" name="numero_de_telefono" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Registrar</button>
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
