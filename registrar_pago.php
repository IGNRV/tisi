<?php
require_once 'db.php'; // Asegúrate de que este es el camino correcto a tu script de conexión a la base de datos
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medioPago = $_POST['medioPago'];
    $total = $_POST['total'];
    $diferencia = $_POST['diferencia'];
    $idUsuario = $_POST['idUsuario'];

    // Aquí debes escribir tu consulta SQL para insertar los datos en la base de datos
    // Por ejemplo:
    $query = "INSERT INTO detalles_transaccion (medio_de_pago, total, diferencia, id_usuario) VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iids", $medioPago, $total, $diferencia, $idUsuario);
        $stmt->execute();
        echo "Pago registrado con éxito";
        
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
