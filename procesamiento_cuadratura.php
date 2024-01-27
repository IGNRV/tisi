<?php
// procesamiento_cuadratura.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start(); // Asegurarse de que la sesión está iniciada

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        echo "Usuario no autenticado";
        exit;
    }

    $idUsuario = $_SESSION['id']; // Obtener el ID del usuario de la sesión
    $fecha = $_POST['fecha'];
    $medioPago = $_POST['medioPago'];

    $fechaFormateada = date('Y-m-d', strtotime($fecha));
    $query = "SELECT * FROM detalles_transaccion WHERE date_created = ? AND medio_de_pago = ? AND id_usuario = ?"; // Añadir la condición para id_usuario

    $resultados = [];
    $totalAcumulado = 0;

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sii", $fechaFormateada, $medioPago, $idUsuario); // Agregar el idUsuario a los parámetros
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
            $totalAcumulado += $row['total_con_iva'];
        }

        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
        exit;
    }

    echo json_encode(['resultados' => $resultados, 'totalAcumulado' => $totalAcumulado]);
} else {
    echo "Método no permitido";
}

?>
