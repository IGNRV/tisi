<?php
// categorias.php
require_once 'db.php';
session_start();

$mensaje_exito = '';
$estadoSuscripcion = 0; // Estado de suscripción por defecto

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    // Comprobar el estado de suscripción del usuario
    $estadoSuscripcionQuery = "SELECT estado_suscripcion FROM usuarios WHERE id = ?";
    if ($estadoSuscripcionStmt = $conn->prepare($estadoSuscripcionQuery)) {
        $estadoSuscripcionStmt->bind_param("i", $id_usuario);
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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['id_categoria'], $_POST['nombre_categoria'])) {
            $id_categoria = $_POST['id_categoria'];
            $nuevo_nombre = $_POST['nombre_categoria'];

            $update_query = "UPDATE categorias SET nombre_categoria = ? WHERE id_categoria = ? AND id_usuario = ?";
                if ($update_stmt = $conn->prepare($update_query)) {
                    $update_stmt->bind_param("sii", $nuevo_nombre, $id_categoria, $id_usuario);
                    if ($update_stmt->execute()) {
                        // Insertar en la tabla historial_cambios
                        $descripcion = "Se edita categoría";
                        $historial_query = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_categoria) VALUES (?, NOW(), ?, ?)";
                        
                        if ($historial_stmt = $conn->prepare($historial_query)) {
                            $historial_stmt->bind_param("sii", $descripcion, $id_usuario, $id_categoria);
                            $historial_stmt->execute();
                            $historial_stmt->close();
                        }

                        $mensaje_exito = 'Categoría actualizada con éxito.';
                    }
                    $update_stmt->close();
            } else {
                echo "Error al preparar la consulta de actualización: " . $conn->error;
            }
        } elseif (isset($_POST['nueva_categoria'])) {
            $nueva_categoria = $_POST['nueva_categoria'];
            $conn->begin_transaction();
            $insert_query = "INSERT INTO categorias (nombre_categoria, id_usuario) VALUES (?, ?)";
            if ($insert_stmt = $conn->prepare($insert_query)) {
                $insert_stmt->bind_param("si", $nueva_categoria, $id_usuario);
                if ($insert_stmt->execute()) {
                    $id_categoria_nueva = $conn->insert_id; // Obtener el ID de la categoría recién insertada
                    $descripcion = "Se añade categoria";
                    $historial_query = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_categoria) VALUES (?, NOW(), ?, ?)";
                    if ($historial_stmt = $conn->prepare($historial_query)) {
                        $historial_stmt->bind_param("sii", $descripcion, $id_usuario, $id_categoria_nueva);
                        if (!$historial_stmt->execute()) {
                            echo "Error al registrar en historial de cambios: " . $conn->error;
                            $conn->rollback();
                        } else {
                            $conn->commit();
                            $mensaje_exito = 'Categoría agregada con éxito y registrada en el historial.';
                        }
                        $historial_stmt->close();
                    } else {
                        echo "Error al preparar la consulta del historial: " . $conn->error;
                        $conn->rollback();
                    }
                } else {
                    echo "Error al agregar la categoría: " . $conn->error;
                    $conn->rollback();
                }
                $insert_stmt->close();
            } else {
                echo "Error al preparar la consulta de inserción: " . $conn->error;
            }
        } elseif (isset($_POST['eliminar_categoria'])) {
          $id_categoria_eliminar = $_POST['eliminar_categoria'];
          $conn->begin_transaction();
          $delete_query = "DELETE FROM categorias WHERE id_categoria = ? AND id_usuario = ?";
          if ($delete_stmt = $conn->prepare($delete_query)) {
              $delete_stmt->bind_param("ii", $id_categoria_eliminar, $id_usuario);
              if ($delete_stmt->execute()) {
                  // Insertar en la tabla historial_cambios
                  $descripcion = "Se elimina categoria";
                  $historial_query = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_categoria) VALUES (?, NOW(), ?, ?)";
                  if ($historial_stmt = $conn->prepare($historial_query)) {
                      $historial_stmt->bind_param("sii", $descripcion, $id_usuario, $id_categoria_eliminar);
                      if (!$historial_stmt->execute()) {
                          echo "Error al registrar en historial de cambios: " . $conn->error;
                          $conn->rollback();
                      } else {
                          $conn->commit();
                          $mensaje_exito = 'Categoría eliminada con éxito.';
                      }
                      $historial_stmt->close();
                  } else {
                      echo "Error al preparar la consulta del historial: " . $conn->error;
                      $conn->rollback();
                  }
              } else {
                  echo "Error al eliminar la categoría: " . $conn->error;
                  $conn->rollback();
              }
              $delete_stmt->close();
            } else {
                echo "Error al preparar la consulta de eliminación: " . $conn->error;
            }
        }
    }

    $query = "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_usuario = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    // Mostrar las categorías en una tabla
    echo '<table class="table">';
    echo '<thead><tr><th>Nombre de la Categoría</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['nombre_categoria']) . '</td>';
        echo '<td>';
        echo '<button class="btn btn-primary" data-toggle="modal" data-target="#editCategoryModal" onclick="setEditModalValues(\'' . $row['id_categoria'] . '\', \'' . htmlspecialchars($row['nombre_categoria'], ENT_QUOTES) . '\')">Editar</button>';
        echo ' ';
        echo '<button class="btn btn-danger" onclick="eliminarCategoria(\'' . $row['id_categoria'] . '\')">Eliminar</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    $stmt->close();
} else {
    echo "Error al preparar la consulta: " . $conn->error;
}
    echo '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#addCategoryModal"' . ($estadoSuscripcion == 0 ? ' disabled' : '') . '>Agregar categoría</button>';
    echo '
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCategoryModalLabel">Agregar Nueva Categoría</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="addCategoryForm" method="post">
              <div class="form-group">
                <label for="newCategoryName">Nombre de la Nueva Categoría</label>
                <input type="text" class="form-control" id="newCategoryName" name="nueva_categoria">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success" id="agregarBtn">Agregar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>';

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
                        <button type="submit" class="btn btn-primary" id="saveChangesBtn">Guardar Cambios</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>';
} else {
    echo "Usuario no autenticado.";
}

$conn->close();
?>
<script>
// Este código se ejecutará una vez que se haya cargado completamente el DOM.
document.addEventListener('DOMContentLoaded', function() {
    // Selecciona el botón por su ID.
    var agregarBtn = document.getElementById('agregarBtn');

    // Asegúrate de que el botón exista en el DOM.
    if (agregarBtn) {
        // Agrega un controlador de eventos para el evento de 'submit' del formulario.
        agregarBtn.form.addEventListener('submit', function() {
            // Inmediatamente después de que el formulario se haya enviado, desactiva el botón.
            agregarBtn.disabled = true;
            // Opcional: Cambia el texto del botón para indicar que la acción está en proceso.
            agregarBtn.textContent = 'Agregando...';
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editButton = document.querySelector('button[data-target="#editCategoryModal"]');
    var addButton = document.querySelector('button[data-target="#addCategoryModal"]');
    var categorySelect = document.querySelector('#categoriaSelect');
    var categoryNameInput = document.querySelector('#categoryName');
    var categoryIdInput = document.querySelector('#categoryId');

    editButton.addEventListener('click', function() {
        var selectedOption = categorySelect.options[categorySelect.selectedIndex];
        categoryNameInput.value = selectedOption.text;
        categoryIdInput.value = selectedOption.value;
    });

    addButton.addEventListener('click', function() {
        document.getElementById('newCategoryName').value = '';
    });

    document.getElementById('editCategoryForm').addEventListener('submit', function(event) {
        // Lógica para el formulario de edición
    });

    document.getElementById('addCategoryForm').addEventListener('submit', function(event) {
        // Lógica para el formulario de agregar categoría
    });
});

function setEditModalValues(id, name) {
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
}

function eliminarCategoria(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = ''; // Aquí puedes establecer el script PHP que manejará la eliminación

        var inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'eliminar_categoria';
        inputId.value = id;

        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var saveChangesBtn = document.getElementById('saveChangesBtn');

    // Asegúrate de que el botón existe en el DOM
    if (saveChangesBtn) {
        saveChangesBtn.form.addEventListener('submit', function() {
            // Deshabilita el botón para prevenir envíos múltiples
            saveChangesBtn.disabled = true;
            // Cambia el texto del botón, opcionalmente
            saveChangesBtn.textContent = 'Guardando...';
        });
    }
});

</script>
