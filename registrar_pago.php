<?php
require_once 'db.php'; // Asegúrate de que este es el camino correcto a tu script de conexión a la base de datos
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medioPago = $_POST['medioPago'];
    $total = $_POST['total'];
    $diferencia = abs($_POST['diferencia']);
    $idUsuario = $_POST['idUsuario'];
    $montoPagadoCliente = $_POST['montoPagadoCliente']; // Recibe el monto pagado por el cliente
    $fechaActual = date("Y-m-d"); // Obtener la fecha actual

    // Consulta SQL para insertar los datos en la base de datos
    $query = "INSERT INTO detalles_transaccion (medio_de_pago, total, diferencia, monto_pagado_cliente, id_usuario, date_created) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iidsis", $medioPago, $total, $diferencia, $montoPagadoCliente, $idUsuario, $fechaActual);
        $stmt->execute();

        // Actualizar el stock de los productos vendidos
        $productosVendidos = json_decode($_POST['productosVendidos'], true);
        foreach ($productosVendidos as $producto) {
            $queryUpdateStock = "UPDATE productos SET stock = stock - ? WHERE nombre_px = ?";
            if ($stmtUpdate = $conn->prepare($queryUpdateStock)) {
                $stmtUpdate->bind_param("is", $producto['cantidadVendida'], $producto['nombre']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
        }

        echo "Pago registrado con éxito";
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
