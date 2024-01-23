<?php
// update_product.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id']; // Asegúrate de que esto está correctamente asignado desde la sesión
} else {
    // Manejar el caso en que el ID del usuario no esté establecido
    exit('Usuario no autenticado.');
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = $_POST['id_producto'];
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $id_proveedor = $_POST['id_proveedor']; // Añadir esta línea para capturar el id_proveedor seleccionado
    $stock = isset($_POST['stock']) ? $_POST['stock'] : 0; // Asigna 0 si no se proporciona stock
    $kilogramos = isset($_POST['kilogramos']) ? floatval($_POST['kilogramos']) : null;
    $codigo_producto = $_POST['codigo_producto'];

    // Incluir id_proveedor en la consulta de actualización
    $query = "UPDATE productos SET nombre_px = ?, precio = ?, id_categoria = ?, id_proveedor = ?, stock = ?, kilogramos = ?, codigo_producto = ? WHERE id_producto = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssiiidsi", $nombre_px, $precio, $id_categoria, $id_proveedor, $stock, $kilogramos, $codigo_producto, $id_producto);

        if ($stmt->execute()) {
            // ID del usuario que está realizando la actualización
            $id_usuario = $_SESSION['id'];

            // Descripción de la acción realizada
            $descripcion = 'Se edita información de producto';

            // Prepara la consulta para la tabla historial_cambios
            $query_historial = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_producto) VALUES (?, NOW(), ?, ?)";

            if ($stmt_historial = $conn->prepare($query_historial)) {
                $stmt_historial->bind_param("sii", $descripcion, $id_usuario, $id_producto);

                if (!$stmt_historial->execute()) {
                    // Manejar error al insertar en la tabla historial_cambios
                    echo "Error al registrar en historial de cambios: " . $conn->error;
                }
                $stmt_historial->close();
            } else {
                echo "Error al preparar la consulta para historial de cambios: " . $conn->error;
            }

            // Redirecciona o maneja la respuesta como prefieras
            header("Location: welcome.php?page=products&update_success=true");
            exit;
        } else {
            // Manejo del error
            echo "Error al actualizar el producto: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>