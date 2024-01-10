<?php
// update_product.php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = $_POST['id_producto'];
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock = $_POST['stock'];
    $kilogramos = $_POST['kilogramos']; // Nuevo campo

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
