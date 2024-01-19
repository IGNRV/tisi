<?php
require_once 'db.php';

// Verifica si los datos necesarios están presentes
if (isset($_POST['id_proveedor'], $_POST['nombre_proveedor'], $_POST['telefono_proveedor'], $_POST['rut_proveedor'], $_POST['tipo_proveedor'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $nombre_proveedor = $_POST['nombre_proveedor'];
    $telefono_proveedor = $_POST['telefono_proveedor'];
    $rut_proveedor = $_POST['rut_proveedor'];
    $tipo_proveedor = $_POST['tipo_proveedor'];

    // Prepara la consulta SQL para actualizar los datos
    $query = "UPDATE proveedores SET nombre_proveedor = ?, telefono_proveedor = ?, rut_proveedor = ?, tipo_proveedor = ? WHERE id_proveedor = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssssi", $nombre_proveedor, $telefono_proveedor, $rut_proveedor, $tipo_proveedor, $id_proveedor);
        $stmt->execute();

        // Redirige de vuelta a la página de proveedores con un mensaje de éxito
        header("Location: https://trackitsellit.oralisisdataservice.cl/welcome.php?page=proveedores&edit_success=true");
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    echo "Datos insuficientes para editar.";
}

$conn->close();
?>
