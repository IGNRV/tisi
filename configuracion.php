<?php
// configuracion.php
require_once 'db.php'; // Asume que db.php contiene la conexión a la base de datos

session_start();

// Verificar si el usuario está logueado. Si no, redirige a index.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

$idUsuario = $_SESSION['id']; // Asume que 'id' es la clave de sesión donde se almacena el id del usuario

// Intentar obtener los datos existentes de la empresa
$query = "SELECT razon_social, rut, direccion, comuna FROM negocio WHERE id_usuario = ?";
$datosNegocio = [
    'razon_social' => '',
    'rut' => '',
    'direccion' => '',
    'comuna' => ''
];

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $datosNegocio = $result->fetch_assoc();
    }
    $stmt->close();
}

// Si se ha enviado el formulario, procesar la entrada
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los valores del formulario
    $razonSocial = $_POST['razon_social'] ?? '';
    $rut = $_POST['rut'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $comuna = $_POST['comuna'] ?? '';

    // Preparar la sentencia SQL para insertar o actualizar
    $query = "REPLACE INTO negocio (razon_social, rut, direccion, comuna, id_usuario) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssssi", $razonSocial, $rut, $direccion, $comuna, $idUsuario);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $mensaje = "Datos guardados correctamente.";
            // Actualizar los datos del negocio con los nuevos valores
            $datosNegocio = [
                'razon_social' => $razonSocial,
                'rut' => $rut,
                'direccion' => $direccion,
                'comuna' => $comuna
            ];
        } else {
            $mensaje = "No se pudieron guardar los datos.";
        }
        $stmt->close();
    } else {
        $mensaje = "Error al preparar la consulta: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración del negocio</title>
    <!-- Incluir CSS de Bootstrap y cualquier otro archivo de estilo relevante -->
</head>
<body>

<div class="container">
    <h2>Configuración del negocio</h2>
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-info"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    <form action="configuracion.php" method="post">
        <div class="form-group">
            <label for="razon_social">Razón Social:</label>
            <input type="text" class="form-control" id="razon_social" name="razon_social" required value="<?php echo htmlspecialchars($datosNegocio['razon_social']); ?>">
        </div>
        <div class="form-group">
            <label for="rut">RUT:</label>
            <input type="text" class="form-control" id="rut" name="rut" required value="<?php echo htmlspecialchars($datosNegocio['rut']); ?>">
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required value="<?php echo htmlspecialchars($datosNegocio['direccion']); ?>">
        </div>
        <div class="form-group">
            <label for="comuna">Comuna:</label>
            <input type="text" class="form-control" id="comuna" name="comuna" required value="<?php echo htmlspecialchars($datosNegocio['comuna']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>

<!-- Incluir JS de Bootstrap y cualquier otro archivo de script relevante -->

</body>
</html>
