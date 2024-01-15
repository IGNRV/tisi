<?php
// buscar_ajax.php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['buscar'])) {
    $buscar = $_POST['buscar'];

    $query = "SELECT nombre_px, codigo_producto FROM productos WHERE id_usuario = ? AND (nombre_px LIKE CONCAT('%', ?, '%') OR codigo_producto LIKE CONCAT('%', ?, '%'))";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iss", $_SESSION['id'], $buscar, $buscar);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<div>" . htmlspecialchars($row['nombre_px']) . " - " . htmlspecialchars($row['codigo_producto']) . "</div>";
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}
?>
