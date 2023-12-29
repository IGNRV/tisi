<?php
require_once 'db.php';

if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];

    // Prepara la consulta de eliminación
    $query = "DELETE FROM productos WHERE id_producto = ?";

    // Prepara la sentencia
    if ($stmt = $conn->prepare($query)) {
        // Vincula los parámetros
        $stmt->bind_param("i", $id_producto);

        // Ejecuta la consulta
        if ($stmt->execute()) {
            // Redirige de nuevo a la página principal o muestra un mensaje de éxito
            header("Location: welcome.php?page=products&update_success=true");
            exit;
        } else {
            // Manejo del error
            echo "Error al eliminar el producto: " . $conn->error;
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
