<?php
require_once 'db.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoge los datos del formulario
    $codigo_producto = $_POST['codigo_producto']; // Nuevo campo para el código del producto
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $tipo_cantidad = $_POST['tipo_cantidad'];
    $cantidad = $_POST['cantidad'];
    $id_usuario = $_POST['id_usuario']; // Asegúrate de validar y limpiar este valor

    // Prepara la consulta para insertar el nuevo producto
    if ($tipo_cantidad == 'kilogramos') {
        $query = "INSERT INTO productos (codigo_producto, nombre_px, precio, id_categoria, kilogramos, id_usuario) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        $query = "INSERT INTO productos (codigo_producto, nombre_px, precio, id_categoria, stock, id_usuario) VALUES (?, ?, ?, ?, ?, ?)";
    }

    if ($stmt = $conn->prepare($query)) {
        if ($tipo_cantidad == 'kilogramos') {
            $stmt->bind_param("ssdiis", $codigo_producto, $nombre_px, $precio, $id_categoria, $cantidad, $id_usuario);
        } else {
            $stmt->bind_param("ssdiis", $codigo_producto, $nombre_px, $precio, $id_categoria, $cantidad, $id_usuario);
        }

        if ($stmt->execute()) {
            header("Location: welcome.php?add_success=true");
            exit;
        } else {
            echo "Error al agregar el producto: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>
