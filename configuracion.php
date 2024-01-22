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

$estadoSuscripcion = 0;
if ($estadoSuscripcionStmt = $conn->prepare("SELECT estado_suscripcion FROM usuarios WHERE id = ?")) {
    $estadoSuscripcionStmt->bind_param("i", $idUsuario);
    $estadoSuscripcionStmt->execute();
    $estadoSuscripcionStmt->bind_result($estadoSuscripcion);
    $estadoSuscripcionStmt->fetch();
    $estadoSuscripcionStmt->close();
}
if ($estadoSuscripcion == 0) {
    echo "<div class='alert alert-warning' role='alert'>
            No tienes una suscripción activa. Por favor, activa tu suscripción en el menú Suscripción por $20.000.
          </div>";
}

// Intentar obtener los datos existentes de la empresa
$query = "SELECT razon_social, rut, direccion, comuna, giro FROM negocio WHERE id_usuario = ?";
$datosNegocio = [
    'razon_social' => '',
    'rut' => '',
    'direccion' => '',
    'comuna' => '',
    'giro' => ''
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
    $giro = $_POST['giro'] ?? ''; // Nuevo campo para el giro

    // Verificar si ya existen datos y elegir la consulta adecuada
    $query = $result->num_rows > 0 ? 
        "UPDATE negocio SET razon_social = ?, rut = ?, direccion = ?, comuna = ?, giro = ? WHERE id_usuario = ?" :
        "INSERT INTO negocio (razon_social, rut, direccion, comuna, giro, id_usuario) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sssssi", $razonSocial, $rut, $direccion, $comuna, $giro, $idUsuario);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $mensaje = "Datos guardados correctamente.";
            // Actualizar los datos del negocio con los nuevos valores
            $datosNegocio = [
                'razon_social' => $razonSocial,
                'rut' => $rut,
                'direccion' => $direccion,
                'comuna' => $comuna,
                'giro' => $giro
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
    <form action="welcome.php?page=configuracion" method="post">
        <div class="form-group">
            <label for="razon_social">Razón Social:</label>
            <input type="text" class="form-control" id="razon_social" name="razon_social" required value="<?php echo htmlspecialchars($datosNegocio['razon_social']); ?>" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="giro">Giro:</label>
            <input type="text" class="form-control" id="giro" name="giro" required value="<?php echo htmlspecialchars($datosNegocio['giro'] ?? ''); ?>" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="rut">RUT:</label>
            <input type="text" class="form-control" id="rut" name="rut" required value="<?php echo htmlspecialchars($datosNegocio['rut']); ?>" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required value="<?php echo htmlspecialchars($datosNegocio['direccion']); ?>" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="comuna">Comuna:</label>
            <input type="text" class="form-control" id="comuna" name="comuna" required value="<?php echo htmlspecialchars($datosNegocio['comuna']); ?>" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        </div>
        <button type="submit" class="btn btn-primary" id="guardarBtn" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>Guardar</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var guardarBtn = document.getElementById('guardarBtn');
    if (guardarBtn) {
        guardarBtn.addEventListener('click', function() {
            // Comprueba si el botón no está deshabilitado
            if (!guardarBtn.disabled) {
                guardarBtn.disabled = true; // Deshabilita el botón para evitar clics adicionales
                guardarBtn.textContent = 'Guardando...'; // Opcional: cambia el texto del botón
                var form = guardarBtn.closest('form');
                if (form) {
                    form.submit(); // Envía el formulario
                }
            }
        });
    }
});
</script>


</body>
</html>
