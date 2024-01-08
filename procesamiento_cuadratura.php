<?php
// procesamiento_cuadratura.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $medioPago = $_POST['medioPago'];

    $fechaFormateada = date('Y-m-d', strtotime($fecha));
    $query = "SELECT * FROM detalles_transaccion WHERE date_created = ? AND medio_de_pago = ?";

    $resultados = [];
    $totalAcumulado = 0; // Inicializar el total acumulado

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("si", $fechaFormateada, $medioPago);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
            $totalAcumulado += $row['total_con_iva']; // Acumular el total
        }

        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
        exit;
    }

    // Enviar los resultados y el total acumulado
    echo json_encode(['resultados' => $resultados, 'totalAcumulado' => $totalAcumulado]);
} else {
    echo "Método no permitido";
}

?>
