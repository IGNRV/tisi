<?php
// categorias.php
require_once 'db.php';

// Suponiendo que existe una relación entre el usuario y las categorías en tu esquema de base de datos
if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    // Modifica esta consulta según tu esquema de base de datos y la relación entre usuarios y categorías
    $query = "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_usuario = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<select class="form-control" name="categoria">';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nombre_categoria']) . '</option>';
            }
            echo '</select>';
        } else {
            echo 'No hay categorías disponibles.';
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    echo "Usuario no autenticado.";
}

$conn->close();
?>
