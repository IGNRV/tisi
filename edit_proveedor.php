<?php
require_once 'db.php';
session_start();

// Verifica si los datos necesarios están presentes y si el usuario está autenticado
if (isset($_POST['id_proveedor'], $_POST['nombre_proveedor'], $_POST['telefono_proveedor'], $_POST['rut_proveedor'], $_POST['tipo_proveedor']) && isset($_SESSION['id'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $nombre_proveedor = $_POST['nombre_proveedor'];
    $telefono_proveedor = $_POST['telefono_proveedor'];
    $rut_proveedor = $_POST['rut_proveedor'];
    $tipo_proveedor_id = $_POST['tipo_proveedor'];
    $id_usuario = $_SESSION['id'];

    // Iniciar transacción
    $conn->begin_transaction();

    // Prepara la consulta SQL para actualizar los datos del proveedor
    $update_query = "UPDATE proveedores SET nombre_proveedor = ?, telefono_proveedor = ?, rut_proveedor = ?, tipo_proveedor = ? WHERE id_proveedor = ?";
    if ($update_stmt = $conn->prepare($update_query)) {
        $update_stmt->bind_param("ssssi", $nombre_proveedor, $telefono_proveedor, $rut_proveedor, $tipo_proveedor_id, $id_proveedor);

        if ($update_stmt->execute()) {
            // Preparar inserción en la tabla historial_cambios
            $descripcion = "Se edito informacion de proveedor";
            $historial_query = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_proveedor) VALUES (?, NOW(), ?, ?)";
            if ($historial_stmt = $conn->prepare($historial_query)) {
                $historial_stmt->bind_param("sii", $descripcion, $id_usuario, $id_proveedor);

                if ($historial_stmt->execute()) {
                    // Confirmar transacción
                    $conn->commit();
                    header("Location: https://trackitsellit.oralisisdataservice.cl/welcome.php?page=proveedores&edit_success=true");
                    exit;
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
            echo "Error al actualizar el proveedor: " . $conn->error;
            $conn->rollback();
        }
        $update_stmt->close();
    } else {
        echo "Error al preparar la consulta de actualización: " . $conn->error;
        $conn->rollback();
    }
} else {
    echo "Datos insuficientes para editar o usuario no autenticado.";
}

$conn->close();
?>
