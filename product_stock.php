<?php
// product_stock.php
require_once 'db.php';
session_start();

$estadoSuscripcion = 0; // Valor predeterminado
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    $estadoSuscripcionQuery = "SELECT estado_suscripcion FROM usuarios WHERE id = ?";
    if ($estadoSuscripcionStmt = $conn->prepare($estadoSuscripcionQuery)) {
        $estadoSuscripcionStmt->bind_param("i", $_SESSION['id']);
        $estadoSuscripcionStmt->execute();
        $estadoSuscripcionStmt->bind_result($estadoSuscripcion);
        $estadoSuscripcionStmt->fetch();
        $estadoSuscripcionStmt->close();
    }

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

    $query = "SELECT id_producto, nombre_px, precio, id_categoria, stock, kilogramos FROM productos WHERE id_usuario = ?";
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
  <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProductModal" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
    Agregar Producto
  </button>
</div>

<!-- Botón para carga masiva -->
<div class="mb-3">
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#massUploadModal" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        Carga Masiva de Productos
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
                <th>Kilogramos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre_px']); ?></td>
                    <td><?php echo htmlspecialchars($row['precio']); ?></td>
                    <td><?php echo htmlspecialchars($categorias[$row['id_categoria']]); ?></td>
                    <td><?php echo $row['stock'] != 0 ? htmlspecialchars($row['stock']) : '-'; ?></td> <!-- Condición para 'stock' -->
                    <td><?php echo $row['kilogramos'] != 0 ? htmlspecialchars($row['kilogramos']) : '-'; ?></td> <!-- Condición para 'kilogramos' -->
                    <td>
                        <!-- Botones de acción -->
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
                                    <label>Kilogramos</label>
                                    <input type="text" name="kilogramos" class="form-control" value="<?php echo htmlspecialchars($row['kilogramos']); ?>">
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

<!-- Modal para Carga Masiva -->
<div class="modal fade" id="massUploadModal" tabindex="-1" role="dialog" aria-labelledby="massUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="massUploadModalLabel">Carga Masiva de Productos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="mass_upload.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fileUpload">Selecciona el archivo:</label>
                        <input type="file" name="fileUpload" id="fileUpload" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>
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
    <label>Tipo de Cantidad</label>
    <select name="tipo_cantidad" class="form-control" id="tipoCantidad">
        <option value="stock">Stock</option>
        <option value="kilogramos">Kilogramos</option>
    </select>
</div>
<div class="form-group">
    <label>Cantidad</label>
    <input type="text" name="cantidad" class="form-control">
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
