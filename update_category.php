<?php
// update_category.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_categoria'], $_POST['nombre_categoria'])) {
    $id_categoria = $_POST['id_categoria'];
    $nombre_categoria = $_POST['nombre_categoria'];

    // Prepara la consulta de actualización
    $query = "UPDATE categorias SET nombre_categoria = ? WHERE id_categoria = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("si", $nombre_categoria, $id_categoria);

        if ($stmt->execute()) {
            header("Location: welcome.php?update_success=true");
            exit;
        } else {
            echo "Error al actualizar la categoría: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>
