<?php
require_once 'db.php'; // Asegúrate de que este es el camino correcto a tu script de conexión a la base de datos
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medioPago = $_POST['medioPago'];
    $total = $_POST['total'];
    $diferencia = abs($_POST['diferencia']);
    $idUsuario = $_POST['idUsuario'];
    $montoPagadoCliente = $_POST['montoPagadoCliente']; // Recibe el monto pagado por el cliente

    // Aquí debes escribir tu consulta SQL para insertar los datos en la base de datos
    // Por ejemplo:
    $query = "INSERT INTO detalles_transaccion (medio_de_pago, total, diferencia, monto_pagado_cliente, id_usuario) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iidsi", $medioPago, $total, $diferencia, $montoPagadoCliente, $idUsuario);
        $stmt->execute();
        echo "Pago registrado con éxito"; // Respuesta en caso de éxito
    
        $stmt->close();
    } else {
        echo "Error: " . $conn->error; // Respuesta en caso de error
    }
}
?>
