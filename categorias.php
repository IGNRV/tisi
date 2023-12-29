<?php
// categorias.php
require_once 'db.php';
session_start();

// Variable para almacenar el mensaje de éxito
$mensaje_exito = '';

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    // Procesar el formulario de edición si se ha enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_categoria'], $_POST['nombre_categoria'])) {
        $id_categoria = $_POST['id_categoria'];
        $nuevo_nombre = $_POST['nombre_categoria'];

        // Consulta para actualizar la categoría
        $update_query = "UPDATE categorias SET nombre_categoria = ? WHERE id_categoria = ? AND id_usuario = ?";
        if ($update_stmt = $conn->prepare($update_query)) {
            $update_stmt->bind_param("sii", $nuevo_nombre, $id_categoria, $id_usuario);
            if ($update_stmt->execute()) {
                // Establecer el mensaje de éxito
                $mensaje_exito = 'Categoría actualizada con éxito.';
            }
            $update_stmt->close();
        } else {
            echo "Error al preparar la consulta de actualización: " . $conn->error;
        }
    }

    // Consulta para obtener las categorías del usuario
    $query = "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_usuario = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        // Mostrar el mensaje de éxito si está establecido
        if ($mensaje_exito != '') {
            echo "<p class='alert alert-success'>" . $mensaje_exito . "</p>";
        }

        if ($result->num_rows > 0) {
            echo '<div class="form-group">';
            echo '<select class="form-control" id="categoriaSelect" name="categoria">';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['id_categoria'] . '">' . htmlspecialchars($row['nombre_categoria']) . '</option>';
            }
            echo '</select>';
            echo '</div>';
        
            // Botón para abrir el modal de edición
            echo '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editCategoryModal">Editar</button>';

            // Modal para editar la categoría
            echo '
            <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Editar Categoría</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <form id="editCategoryForm" method="post">
                      <div class="form-group">
                        <label for="categoryName">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="categoryName" name="nombre_categoria">
                        <input type="hidden" id="categoryId" name="id_categoria">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>';
        } else {
            echo 'No hay categorías disponibles.';
        }
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    echo "Usuario no autenticado.";
}

$conn->close();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editButton = document.querySelector('button[data-target="#editCategoryModal"]');
    var categorySelect = document.querySelector('#categoriaSelect');
    var categoryNameInput = document.querySelector('#categoryName');
    var categoryIdInput = document.querySelector('#categoryId');

    editButton.addEventListener('click', function() {
        var selectedOption = categorySelect.options[categorySelect.selectedIndex];
        categoryNameInput.value = selectedOption.text;
        categoryIdInput.value = selectedOption.value;
    });

    // Añadir un event listener para el formulario de edición
    document.getElementById('editCategoryForm').addEventListener('submit', function(event) {
        // Aquí puedes añadir cualquier lógica adicional que necesites ejecutar cuando se envía el formulario
    });
});
</script>
