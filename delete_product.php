<?php

session_start();
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id']; // Asegúrate de que esto está correctamente asignado desde la sesión
} else {
    // Manejar el caso en que el ID del usuario no esté establecido
    exit('Usuario no autenticado.');
}

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
            // Prepara la inserción en la tabla de historial_cambios
            $descripcion = "Se elimina producto";
            $query_historial = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_producto) VALUES (?, NOW(), ?, ?)";

            if ($stmt_historial = $conn->prepare($query_historial)) {
                // Vincula los parámetros
                $stmt_historial->bind_param("sii", $descripcion, $id_usuario, $id_producto);
                
                // Ejecuta la consulta de inserción en historial_cambios
                if ($stmt_historial->execute()) {
                    // Si ambas operaciones fueron exitosas, confirma la transacción
                    $conn->commit();
                    // Redirige de nuevo a la página principal o muestra un mensaje de éxito
                    header("Location: welcome.php?page=products&delete_success=true");
                    exit;
                } else {
                    // Manejo del error en la inserción en historial_cambios
                    echo "Error al registrar en historial de cambios: " . $conn->error;
                    $conn->rollback(); // Revierte la transacción si falla la inserción en historial
                }

                // Cierra la sentencia de historial
                $stmt_historial->close();
            } else {
                // Manejo del error en la preparación de la consulta de historial
                echo "Error al preparar la consulta para historial de cambios: " . $conn->error;
                $conn->rollback();
            }
        } else {
            // Manejo del error en la eliminación del producto
            echo "Error al eliminar el producto: " . $conn->error;
            $conn->rollback();
        }

        // Cierra la sentencia de eliminación del producto
        $stmt->close();
    } else {
        // Manejo del error en la preparación de la consulta de eliminación
        echo "Error al preparar la consulta de eliminación: " . $conn->error;
        $conn->rollback();
    }
} else {
    echo "No se proporcionó ID de producto o el usuario no está autenticado.";
}

// Cierra la conexión
$conn->close();
?>