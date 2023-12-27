<?php
// Asegúrate de incluir el archivo de conexión y de iniciar la sesión
require_once 'db.php';

// Verifica si hay una sesión de usuario iniciada
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id']; // Obtiene el ID del usuario de la sesión

    // Prepara la consulta SQL para obtener los productos del usuario
    $query = "SELECT nombre_px, precio, id_categoria, stock FROM productos WHERE id_usuario = ?";

    // Prepara la sentencia
    if ($stmt = $conn->prepare($query)) {
        // Vincula el parámetro 'id_usuario'
        $stmt->bind_param("i", $id_usuario);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene los resultados
        $result = $stmt->get_result();
?>
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
    // Recorre los resultados y los muestra en la tabla
    while ($row = $result->fetch_assoc()) {
        ?>
          <tr>
            <td><?php echo htmlspecialchars($row['nombre_px']); ?></td>
            <td><?php echo htmlspecialchars($row['precio']); ?></td>
            <td><?php echo htmlspecialchars($row['id_categoria']); ?></td>
            <td><?php echo htmlspecialchars($row['stock']); ?></td>
            <td>
              <!-- Botón para abrir el modal de edición -->
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?php echo $row['id']; ?>">
                Editar
              </button>
            </td>
          </tr>
          
          <!-- Modal de Edición -->
          <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editModalLabel">Editar Producto</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form>
                    <!-- Aquí irían los campos de edición para el producto, pre-llenados con los datos actuales -->
                    <div class="form-group">
                      <label>Producto</label>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['nombre_px']); ?>">
                    </div>
                    <div class="form-group">
                      <label>Precio</label>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['precio']); ?>">
                    </div>
                    <div class="form-group">
                      <label>Categoría</label>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['id_categoria']); ?>">
                    </div>
                    <div class="form-group">
                      <label>Stock</label>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['stock']); ?>">
                    </div>
                    <!-- Asumiendo que tienes un campo oculto para el ID del producto -->
                    <input type="hidden" value="<?php echo $row['id']; ?>">
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                  <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
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
