<?php
require_once 'db.php';
session_start();

// Verifica si el id del proveedor y el id del usuario están presentes
if (isset($_GET['id_proveedor']) && isset($_SESSION['id'])) {
    $id_proveedor = $_GET['id_proveedor'];
    $id_usuario = $_SESSION['id'];

    // Iniciar una transacción
    $conn->begin_transaction();

    // Prepara la consulta SQL para eliminar el proveedor
    $delete_query = "DELETE FROM proveedores WHERE id_proveedor = ?";
    if ($delete_stmt = $conn->prepare($delete_query)) {
        $delete_stmt->bind_param("i", $id_proveedor);
        if ($delete_stmt->execute()) {
            // Prepara la inserción en la tabla historial_cambios
            $descripcion = "Se elimina proveedor";
            $historial_query = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_proveedor) VALUES (?, NOW(), ?, ?)";
            if ($historial_stmt = $conn->prepare($historial_query)) {
                $historial_stmt->bind_param("sii", $descripcion, $id_usuario, $id_proveedor);
                if ($historial_stmt->execute()) {
                    // Confirma la transacción si ambas operaciones fueron exitosas
                    $conn->commit();
                    header("Location: https://trackitsellit.oralisisdataservice.cl/welcome.php?page=proveedores&delete_success=true");
                } else {
                    echo "Error al registrar en historial de cambios: " . $conn->error;
                    $conn->rollback();
                }
                $historial_stmt->close();
            } else {
                echo "Error al preparar la consulta para historial de cambios: " . $conn->error;
                $conn->rollback();
            }
        } else {
            echo "Error al eliminar el proveedor: " . $conn->error;
            $conn->rollback();
        }
        $delete_stmt->close();
    } else {
        echo "Error al preparar la consulta de eliminación: " . $conn->error;
        $conn->rollback();
    }
} else {
    echo "No se especificó el ID del proveedor para eliminar o usuario no autenticado.";
}

$conn->close();
?>
