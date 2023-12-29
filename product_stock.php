<?php
// product_stock.php
require_once 'db.php';
session_start();

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    // Obtener categorías disponibles
    $categorias = [];
    if ($stmt_categorias = $conn->prepare("SELECT id_categoria, nombre_categoria FROM categorias WHERE id_usuario = ?")) {
        $stmt_categorias->bind_param("i", $id_usuario);
        $stmt_categorias->execute();
        $result_categorias = $stmt_categorias->get_result();
        while ($categoria = $result_categorias->fetch_assoc()) {
            $categorias[$categoria['id_categoria']] = $categoria['nombre_categoria'];
        }
        $stmt_categorias->close();
    }

    // Comprobar si hay un mensaje de éxito
    $mensaje_exito = '';
    if (isset($_GET['update_success'])) {
        $mensaje_exito = 'Operación realizada con éxito.';
    }

    $query = "SELECT id_producto, nombre_px, precio, id_categoria, stock FROM productos WHERE id_usuario = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        // Mostrar mensaje de éxito si existe
        if (!empty($mensaje_exito)) {
            echo "<div class='alert alert-success'>" . htmlspecialchars($mensaje_exito) . "</div>";
        }
?>

<div class="mb-3">
  <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProductModal">
    Agregar Producto
  </button>
</div>

<div class="table-responsive">
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nombre_px']); ?></td>
                <td><?php echo htmlspecialchars($row['precio']); ?></td>
                <td><?php echo htmlspecialchars($categorias[$row['id_categoria']]); ?></td>
                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?php echo $row['id_producto']; ?>">Editar</button>
                    <a href="delete_product.php?id=<?php echo $row['id_producto']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de querer eliminar este producto?');">Eliminar</a>
                </td>
            </tr>

            <!-- Modal de Edición -->
            <div class="modal fade" id="editModal<?php echo $row['id_producto']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $row['id_producto']; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Editar Producto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="update_product.php" method="POST">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Producto</label>
                                    <input type="text" name="nombre_px" class="form-control" value="<?php echo htmlspecialchars($row['nombre_px']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Precio</label>
                                    <input type="text" name="precio" class="form-control" value="<?php echo htmlspecialchars($row['precio']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select name="id_categoria" class="form-control">
                                        <?php foreach ($categorias as $id_cat => $nombre_cat) : ?>
                                            <option value="<?php echo $id_cat; ?>" <?php if ($id_cat == $row['id_categoria']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($nombre_cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="text" name="stock" class="form-control" value="<?php echo htmlspecialchars($row['stock']); ?>">
                                </div>
                                <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para Agregar Producto -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Agregar Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="add_product.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Producto</label>
                        <input type="text" name="nombre_px" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Precio</label>
                        <input type="text" name="precio" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="id_categoria" class="form-control">
                            <?php foreach ($categorias as $id_cat => $nombre_cat) : ?>
                                <option value="<?php echo $id_cat; ?>">
                                    <?php echo htmlspecialchars($nombre_cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="text" name="stock" class="form-control">
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

<?php
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    echo "Usuario no autenticado.";
}
$conn->close();
?>
