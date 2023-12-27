<?php
// Asegúrate de incluir el archivo de conexión y de iniciar la sesión
require_once 'db.php';

// Verifica si hay una sesión de usuario iniciada
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id']; // Obtiene el ID del usuario de la sesión

    // Prepara la consulta SQL para obtener los productos del usuario
    $query = "SELECT id_producto, nombre_px, precio, id_categoria, stock FROM productos WHERE id_usuario = ?";

    // Prepara la sentencia
    if ($stmt = $conn->prepare($query)) {
        // Vincula el parámetro 'id_usuario'
        $stmt->bind_param("i", $id_usuario);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene los resultados
        $result = $stmt->get_result();
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
  <?php
    while ($row = $result->fetch_assoc()) {
  ?>
    <tr>
      <td><?php echo htmlspecialchars($row['nombre_px']); ?></td>
      <td><?php echo htmlspecialchars($row['precio']); ?></td>
      <td><?php echo htmlspecialchars($row['id_categoria']); ?></td>
      <td><?php echo htmlspecialchars($row['stock']); ?></td>
      <td>
        <!-- Botón para abrir el modal de edición -->
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?php echo $row['id_producto']; ?>">
          Editar
        </button>
        <a href="delete_product.php?id=<?php echo $row['id_producto']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de querer eliminar este producto?');">
            Eliminar
        </a>
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
      <form action="update_product.php" method="POST"> <!-- Añade la acción para enviar al archivo de actualización -->
        <div class="modal-body">
          <!-- Campos de edición para el producto, pre-llenados con los datos actuales -->
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
            <input type="text" name="id_categoria" class="form-control" value="<?php echo htmlspecialchars($row['id_categoria']); ?>">
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
      <form action="add_product.php" method="POST"> <!-- Acción para enviar al archivo de agregación -->
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
            <input type="text" name="id_categoria" class="form-control">
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
        }
        ?>
</tbody>

    </table>
</div>
<?php
        // Cierra la sentencia
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    echo "Usuario no autenticado.";
}

// Cierra la conexión
$conn->close();
?>
