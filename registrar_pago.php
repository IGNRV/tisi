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
    $_SESSION['montoPagadoCliente'] = $montoPagadoCliente;
    $fechaActual = date("Y-m-d");
    $iva = $_POST['iva'];
    $totalConIva = $_POST['totalConIva'];

    $conn->begin_transaction(); // Iniciar una transacción

    $query = "INSERT INTO detalles_transaccion (medio_de_pago, total, iva, total_con_iva, diferencia, monto_pagado_cliente, id_usuario, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $diferencia = $montoPagadoCliente - $totalConIva;

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iidddsss", $medioPago, $total, $iva, $totalConIva, $diferencia, $montoPagadoCliente, $idUsuario, $fechaActual);
        if ($stmt->execute()) {
            $idTransaccion = $conn->insert_id; // Obtener el ID de la transacción insertada

            // Insertar en historial_cambios
            $descripcionHistorial = "Se realizo una venta";
            $historialQuery = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_transaccion) VALUES (?, NOW(), ?, ?)";
            if ($historialStmt = $conn->prepare($historialQuery)) {
                $historialStmt->bind_param("sii", $descripcionHistorial, $idUsuario, $idTransaccion);
                if (!$historialStmt->execute()) {
                    echo "Error al registrar en historial de cambios: " . $conn->error;
                    $conn->rollback();
                    exit;
                }
                $historialStmt->close();
            }

            // Continuar con la actualización de stock y kilogramos
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

            $conn->commit(); // Confirmar la transacción
            header("Location: generar_boleta.php?medioPago=$medioPago&total=$total&diferencia=$diferencia&productosVendidos=$productosVendidosEncoded");
            exit;
        } else {
            echo "Error: " . $conn->error;
            $conn->rollback();
        }
        $stmt->close();
    }
}
?>
