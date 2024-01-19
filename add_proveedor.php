<?php
require_once 'db.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoge los datos del formulario
    $nombre_proveedor = $_POST['nombre_proveedor'];
    $telefono_proveedor = $_POST['telefono_proveedor'];
    $rut_proveedor = $_POST['rut_proveedor'];
    $tipo_proveedor = $_POST['tipo_proveedor'];
    $id_usuario = $_POST['id_usuario']; // Asegúrate de validar y limpiar este valor

    // Prepara la consulta para insertar el nuevo proveedor
    $query = "INSERT INTO proveedores (nombre_proveedor, telefono_proveedor, rut_proveedor, tipo_proveedor, id_usuario) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sssii", $nombre_proveedor, $telefono_proveedor, $rut_proveedor, $tipo_proveedor, $id_usuario);

        if ($stmt->execute()) {
            header("Location: https://trackitsellit.oralisisdataservice.cl/welcome.php?page=proveedores&add_success=true");
            exit;
        } else {
            echo "Error al agregar el proveedor: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>
