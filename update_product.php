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
    $kilogramos = isset($_POST['kilogramos']) ? $_POST['kilogramos'] : null; // Permite valores nulos para kilogramos

    $query = "UPDATE productos SET nombre_px = ?, precio = ?, id_categoria = ?, stock = ?, kilogramos = ? WHERE id_producto = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssiiii", $nombre_px, $precio, $id_categoria, $stock, $kilogramos, $id_producto);

        if ($stmt->execute()) {
            header("Location: welcome.php?page=products&update_success=true");
            exit;
        } else {
            echo "Error al actualizar el producto: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>
