<?php
require_once 'db.php';

// Verifica si el id del proveedor está presente
if (isset($_GET['id_proveedor'])) {
    $id_proveedor = $_GET['id_proveedor'];

    // Prepara la consulta SQL para eliminar el proveedor
    $query = "DELETE FROM proveedores WHERE id_proveedor = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_proveedor);
        $stmt->execute();

        // Redirige de vuelta a la página de proveedores con un mensaje de éxito
        header("Location: http://localhost:8000/welcome.php?page=proveedores&delete_success=true");
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    echo "No se especificó el ID del proveedor para eliminar.";
}

$conn->close();
?>
