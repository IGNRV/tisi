<?php
require_once 'db.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoge los datos del formulario
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock = $_POST['stock'];
    $id_usuario = $_POST['id_usuario']; // Asegúrate de validar y limpiar este valor

    // Prepara la consulta para insertar el nuevo producto
    $query = "INSERT INTO productos (nombre_px, precio, id_categoria, stock, id_usuario) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sdiis", $nombre_px, $precio, $id_categoria, $stock, $id_usuario);

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
