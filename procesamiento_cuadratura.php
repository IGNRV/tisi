<?php
require_once 'db.php';

// Verificar si los datos fueron enviados por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $medioPago = $_POST['medioPago'];

    // Convertir la fecha al formato correcto para la base de datos (YYYY-MM-DD)
    $fechaFormateada = date('Y-m-d', strtotime($fecha));

    // Preparar la consulta SQL
    $query = "SELECT * FROM detalles_transaccion WHERE date_created = ? AND medio_de_pago = ?";

    $resultados = [];
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("si", $fechaFormateada, $medioPago);
        $stmt->execute();
        $result = $stmt->get_result();

        // Recoger los resultados
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }

        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
        exit;
    }

    // Devolver los resultados en formato JSON
    echo json_encode($resultados);
} else {
    echo "Método no permitido";
}
?>
