<?php
// update_product.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = $_POST['id_producto'];
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock = isset($_POST['stock']) ? $_POST['stock'] : 0; // Asigna 0 si no se proporciona stock
    $kilogramos = isset($_POST['kilogramos']) ? floatval($_POST['kilogramos']) : null;
    $codigo_producto = $_POST['codigo_producto'];

    $query = "UPDATE productos SET nombre_px = ?, precio = ?, id_categoria = ?, stock = ?, kilogramos = ?, codigo_producto = ? WHERE id_producto = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssiidsi", $nombre_px, $precio, $id_categoria, $stock, $kilogramos, $codigo_producto, $id_producto);

        if ($stmt->execute()) {
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
