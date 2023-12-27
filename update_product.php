<?php
// update_product.php

require_once 'db.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtén los datos del formulario
    $id_producto = $_POST['id_producto'];
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock = $_POST['stock'];

    // Prepara la consulta de actualización
    $query = "UPDATE productos SET nombre_px = ?, precio = ?, id_categoria = ?, stock = ? WHERE id_producto = ?";

    // Prepara la sentencia
    if ($stmt = $conn->prepare($query)) {
        // Vincula los parámetros
        $stmt->bind_param("ssiii", $nombre_px, $precio, $id_categoria, $stock, $id_producto);

        // Ejecuta la consulta
        if ($stmt->execute()) {
            // Redirige de nuevo a la página principal o muestra un mensaje de éxito
            header("Location: welcome.php?update_success=true");
            exit;
        } else {
            // Manejo del error
            echo "Error al actualizar el producto: " . $conn->error;
        }

        // Cierra la sentencia
        $stmt->close();
    } else {
        // Manejo del error
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

// Cierra la conexión
$conn->close();
?>
