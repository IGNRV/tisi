<?php
// buscar_ajax.php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['buscar'])) {
    $buscar = $_POST['buscar'];

    $query = "SELECT nombre_px FROM productos WHERE id_usuario = ? AND nombre_px LIKE CONCAT('%', ?, '%')";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("is", $_SESSION['id'], $buscar);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<div>" . htmlspecialchars($row['nombre_px']) . "</div>";
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}
?>
