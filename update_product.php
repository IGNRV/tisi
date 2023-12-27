<?php
require_once 'db.php';

if (isset($_POST['product_id'])) {
  // Recoge los valores del formulario
  $nombre_px = $_POST['nombre_px'];
  // Repite para los otros campos
  $product_id = $_POST['product_id'];

  // Prepara la sentencia de actualización
  if ($stmt = $conn->prepare("UPDATE productos SET nombre_px = ?, precio = ?, id_categoria = ?, stock = ? WHERE id = ? AND id_usuario = ?")) {
    // Vincula los parámetros y ejecuta
    $stmt->bind_param("ssddii", $nombre_px, $precio, $id_categoria, $stock, $product_id, $_SESSION['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      echo "Producto actualizado.";
    } else {
      echo "No se pudo actualizar el producto o no hubo cambios.";
    }

    $stmt->close();
  } else {
    echo "Error al preparar la consulta: " . $conn->error;
  }

  $conn->close();
} else {
  echo "ID del producto no proporcionado.";
}
?>
