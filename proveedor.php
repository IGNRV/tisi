<?php
// proveedor.php
require_once 'db.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Verificar si el ID del usuario está disponible en la sesión
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    // Modificar la consulta para filtrar proveedores por id_usuario
    $query = "SELECT id_proveedor, nombre_proveedor, telefono_proveedor, rut_proveedor, tipo_proveedor FROM proveedores WHERE id_usuario = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
        exit;
    }
} else {
    echo "Usuario no autenticado.";
    exit;
}


// Verificar si se ha agregado un nuevo proveedor con éxito
if (isset($_GET['add_success']) && $_GET['add_success'] == 'true') {
    echo "<div class='alert alert-success'>Proveedor agregado con éxito.</div>";
}

// Verificar si se ha editado un proveedor con éxito
if (isset($_GET['edit_success']) && $_GET['edit_success'] == 'true') {
    echo "<div class='alert alert-success'>Proveedor editado con éxito.</div>";
}

// Verificar si se ha eliminado un proveedor con éxito
if (isset($_GET['delete_success']) && $_GET['delete_success'] == 'true') {
    echo "<div class='alert alert-success'>Proveedor eliminado con éxito.</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Proveedores</title>
    <!-- Aquí puedes incluir los estilos y scripts necesarios -->
</head>
<body>

<!-- Botón para agregar proveedor -->
<div class="mb-3">
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProveedorModal">
        Agregar Proveedor
    </button>
</div>

<!-- Modal para Agregar Proveedor -->
<div class="modal fade" id="addProveedorModal" tabindex="-1" role="dialog" aria-labelledby="addProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProveedorModalLabel">Agregar Proveedor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="add_proveedor.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del Proveedor</label>
                        <input type="text" name="nombre_proveedor" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teléfono del Proveedor</label>
                        <input type="text" name="telefono_proveedor" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>RUT del Proveedor</label>
                        <input type="text" name="rut_proveedor" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Tipo de Proveedor</label>
                        <input type="text" name="tipo_proveedor" class="form-control">
                    </div>
                    <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <h2>Listado de Proveedores</h2>
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>RUT</th>
                <th>Tipo de proveedor</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
    <?php while($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['nombre_proveedor']); ?></td>
        <td><?php echo htmlspecialchars($row['telefono_proveedor']); ?></td>
        <td><?php echo htmlspecialchars($row['rut_proveedor']); ?></td>
        <td><?php echo htmlspecialchars($row['tipo_proveedor']); ?></td>
        <td>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editProveedorModal-<?php echo $row['id_proveedor']; ?>">Editar</button>
            <button type="button" class="btn btn-danger" onclick="eliminarProveedor(<?php echo $row['id_proveedor']; ?>)">Eliminar</button>
        </td>
    </tr>
    <div class="modal fade" id="editProveedorModal-<?php echo $row['id_proveedor']; ?>" tabindex="-1" role="dialog" aria-labelledby="editProveedorModalLabel-<?php echo $row['id_proveedor']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProveedorModalLabel-<?php echo $row['id_proveedor']; ?>">Editar Proveedor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="edit_proveedor.php" method="POST">
                <div class="modal-body">
                    <!-- Campos del formulario de edición con los valores actuales -->
                    <input type="hidden" name="id_proveedor" value="<?php echo $row['id_proveedor']; ?>">
                    <div class="form-group">
                        <label>Nombre del Proveedor</label>
                        <input type="text" name="nombre_proveedor" class="form-control" value="<?php echo $row['nombre_proveedor']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono del Proveedor</label>
                        <input type="text" name="telefono_proveedor" class="form-control" value="<?php echo $row['telefono_proveedor']; ?>">
                    </div>
                    <div class="form-group">
                        <label>RUT del Proveedor</label>
                        <input type="text" name="rut_proveedor" class="form-control" value="<?php echo $row['rut_proveedor']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Tipo de Proveedor</label>
                        <input type="text" name="tipo_proveedor" class="form-control" value="<?php echo $row['tipo_proveedor']; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <?php } ?>
</tbody>
        </table>
    </div>
</div>

</body>
</html>
<script>
function eliminarProveedor(id_proveedor) {
    if (confirm('¿Estás seguro de que deseas eliminar este proveedor?')) {
        // Redirige a un script PHP para eliminar el proveedor
        window.location.href = 'delete_proveedor.php?id_proveedor=' + id_proveedor;
    }
}
</script>

<?php
$conn->close();
?>
