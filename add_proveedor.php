<?php
require_once 'db.php';
session_start();

// Verifica si el formulario ha sido enviado y si el usuario está autenticado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['id'])) {
    $nombre_proveedor = $_POST['nombre_proveedor'];
    $telefono_proveedor = $_POST['telefono_proveedor'];
    $rut_proveedor = $_POST['rut_proveedor'];
    $tipo_proveedor = $_POST['tipo_proveedor']; // Esto debería ser el id_tipo_proveedor seleccionado
    $id_usuario = $_SESSION['id']; // Usa el ID de usuario de la sesión

    // Iniciar una transacción
    $conn->begin_transaction();

    // Prepara la consulta para insertar el nuevo proveedor
    $insert_query = "INSERT INTO proveedores (nombre_proveedor, telefono_proveedor, rut_proveedor, tipo_proveedor, id_usuario) VALUES (?, ?, ?, ?, ?)";
    if ($insert_stmt = $conn->prepare($insert_query)) {
        $insert_stmt->bind_param("sssii", $nombre_proveedor, $telefono_proveedor, $rut_proveedor, $tipo_proveedor, $id_usuario);
        
        if ($insert_stmt->execute()) {
            // Obtener el ID del proveedor recién insertado
            $id_proveedor_nuevo = $conn->insert_id;

            // Preparar la inserción en la tabla historial_cambios
            $descripcion = "Se añade proveedor";
            $historial_query = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_proveedor) VALUES (?, NOW(), ?, ?)";
            if ($historial_stmt = $conn->prepare($historial_query)) {
                $historial_stmt->bind_param("sii", $descripcion, $id_usuario, $id_proveedor_nuevo);

                if ($historial_stmt->execute()) {
                    // Confirma la transacción
                    $conn->commit();
                    header("Location: https://trackitsellit.oralisisdataservice.cl/welcome.php?page=proveedores&add_success=true");
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
            echo "Error al agregar el proveedor: " . $conn->error;
            $conn->rollback();
        }
        $insert_stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
        $conn->rollback();
    }
} else {
    echo "Usuario no autenticado o datos incorrectos.";
}

$conn->close();
?>
