<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medioPago = $_POST['medioPago'];
    $total = $_POST['total'];
    $idUsuario = $_POST['idUsuario'];
    $montoPagadoCliente = $_POST['montoPagadoCliente'];
    $fechaActual = date("Y-m-d");
    $iva = $_POST['iva'];
    $totalConIva = $_POST['totalConIva'];

    $query = "INSERT INTO detalles_transaccion (medio_de_pago, total, iva, total_con_iva, diferencia, monto_pagado_cliente, id_usuario, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $diferencia = $montoPagadoCliente - $total; // Calcular la diferencia
        // Asegúrate de pasar $iva y $totalConIva a la consulta
        $stmt->bind_param("iidddssi", $medioPago, $total, $iva, $totalConIva, $diferencia, $montoPagadoCliente, $idUsuario, $fechaActual);
        $stmt->execute();

        $productosVendidos = json_decode($_POST['productosVendidos'], true);
        foreach ($productosVendidos as &$producto) {
            $queryPrecio = "SELECT precio FROM productos WHERE nombre_px = ?";
            if ($stmtPrecio = $conn->prepare($queryPrecio)) {
                $stmtPrecio->bind_param("s", $producto['nombre']);
                $stmtPrecio->execute();
                $resultadoPrecio = $stmtPrecio->get_result();
                if ($filaPrecio = $resultadoPrecio->fetch_assoc()) {
                    $producto['precio'] = $filaPrecio['precio'];
                } else {
                    $producto['precio'] = 0; // o manejarlo de otra manera si el producto no tiene precio
                }
                $stmtPrecio->close();
            }

            $queryUpdateStock = "UPDATE productos SET stock = stock - ? WHERE nombre_px = ?";
            if ($stmtUpdate = $conn->prepare($queryUpdateStock)) {
                $stmtUpdate->bind_param("is", $producto['cantidadVendida'], $producto['nombre']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
        }
        unset($producto); // Rompe la referencia con el último elemento

        $productosVendidosEncoded = urlencode(json_encode($productosVendidos));
        header("Location: generar_boleta.php?medioPago=$medioPago&total=$total&diferencia=$diferencia&productosVendidos=$productosVendidosEncoded");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>
