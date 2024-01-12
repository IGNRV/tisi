<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        $diferencia = $montoPagadoCliente - $totalConIva;
        $stmt->bind_param("iidddsss", $medioPago, $total, $iva, $totalConIva, $diferencia, $montoPagadoCliente, $idUsuario, $fechaActual);
        $stmt->execute();

        $productosVendidos = json_decode($_POST['productosVendidos'], true);
        foreach ($productosVendidos as &$producto) {
            $queryStockKg = "SELECT stock, kilogramos FROM productos WHERE nombre_px = ?";
            if ($stmtStockKg = $conn->prepare($queryStockKg)) {
                $stmtStockKg->bind_param("s", $producto['nombre']);
                $stmtStockKg->execute();
                $resultadoStockKg = $stmtStockKg->get_result();
                if ($filaStockKg = $resultadoStockKg->fetch_assoc()) {
                    if ($filaStockKg['stock'] != 0) {
                        $queryUpdateStock = "UPDATE productos SET stock = stock - ? WHERE nombre_px = ?";
                        if ($stmtUpdate = $conn->prepare($queryUpdateStock)) {
                            $stmtUpdate->bind_param("is", $producto['cantidadVendida'], $producto['nombre']);
                            $stmtUpdate->execute();
                            $stmtUpdate->close();
                        }
                    } elseif ($filaStockKg['kilogramos'] != null) {
                        $cantidadEnKg = $producto['cantidadVendida'] / 1000; // Convierte de gramos a kilogramos
                        $queryUpdateKg = "UPDATE productos SET kilogramos = kilogramos - ? WHERE nombre_px = ?";
                        if ($stmtUpdateKg = $conn->prepare($queryUpdateKg)) {
                            $stmtUpdateKg->bind_param("ds", $cantidadEnKg, $producto['nombre']);
                            $stmtUpdateKg->execute();
                            $stmtUpdateKg->close();
                        }
                    }
                }
                $stmtStockKg->close();
            }
        }
        unset($producto);

        $productosVendidosEncoded = urlencode(json_encode($productosVendidos));
        header("Location: generar_boleta.php?medioPago=$medioPago&total=$total&diferencia=$diferencia&productosVendidos=$productosVendidosEncoded");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>
