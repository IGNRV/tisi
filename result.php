<?php
session_start();

ini_set('display_errors', 1);
require(__DIR__ . "/PHP-API-CLIENT/lib/FlowApi.class.php");
require_once 'db.php';

echo '<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">';

try {
    if (!isset($_POST["token"])) {
        throw new Exception("No se recibió el token", 1);
    }
    $token = filter_input(INPUT_POST, 'token');
    $params = array("token" => $token);

    $serviceName = "payment/getStatus";
    $flowApi = new FlowApi();
    $response = $flowApi->send($serviceName, $params, "GET");

    if ($response['status'] == 2) { // Suponiendo que 2 es un pago exitoso
        $commerceOrder = $response['commerceOrder'];

        // Buscar el usuario correspondiente en la tabla registro_de_pagos
        $query = "SELECT oralisis_user_id FROM registro_de_pagos WHERE orden_comercio = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $commerceOrder);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($oralisisUserId);
            if ($stmt->fetch()) {

                // Actualizar estado_suscripcion y descuento_inicial en la tabla usuarios
                $updateUserQuery = "UPDATE usuarios SET estado_suscripcion = 1, descuento_inicial = ? WHERE id = ?";
                if ($updateUserStmt = $conn->prepare($updateUserQuery)) {
                    $nuevoDescuento = 1; // Define aquí el valor que quieres insertar
                    $updateUserStmt->bind_param("ii", $nuevoDescuento, $oralisisUserId);
                    $updateUserStmt->execute();
                    $updateUserStmt->close();
                } else {
                    throw new Exception("Error al preparar la consulta de actualización de usuario: " . $conn->error);
                }

                // Verificar si ya existe una suscripción para el usuario
                $checkSuscripcionQuery = "SELECT id FROM suscripcion_tisi WHERE id_usuario = ?";
                if ($checkStmt = $conn->prepare($checkSuscripcionQuery)) {
                    $checkStmt->bind_param("i", $oralisisUserId);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows > 0) {
                        // Actualizar suscripciones_pagadas en la tabla suscripcion_tisi
                        $updateSuscripcionQuery = "UPDATE suscripcion_tisi SET suscripciones_pagadas = suscripciones_pagadas + 1 WHERE id_usuario = ?";
                        if ($updateSuscripcionStmt = $conn->prepare($updateSuscripcionQuery)) {
                            $updateSuscripcionStmt->bind_param("i", $oralisisUserId);
                            $updateSuscripcionStmt->execute();
                            $updateSuscripcionStmt->close();
                        }
                    } else {
                        // Insertar nueva suscripción en la tabla suscripcion_tisi
                        $insertSuscripcionQuery = "INSERT INTO suscripcion_tisi (dia_suscripcion, tipo_suscripcion, fecha_activacion, id_usuario, suscripciones_pagadas) VALUES (1, 1, NOW(), ?, 1)";
                        if ($insertSuscripcionStmt = $conn->prepare($insertSuscripcionQuery)) {
                            $insertSuscripcionStmt->bind_param("i", $oralisisUserId);
                            $insertSuscripcionStmt->execute();
                            $insertSuscripcionStmt->close();
                        }
                    }
                    $checkStmt->close();
                }
            } else {
                echo "No se encontró el registro correspondiente en registro_de_pagos.";
            }
            $stmt->close();
        } else {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
    } else {
        echo "El pago no fue exitoso.";
    }

    echo '<div class="alert alert-success" role="alert">';
    echo 'Se ha enviado la boleta de pago a tu correo electrónico. Serás redirigido al inicio en unos segundos.';
    echo '</div>';

    echo '<script>';
    echo 'setTimeout(function(){ window.location.href = "https://trackitsellit.oralisisdataservice.cl/welcome.php"; }, 5000);';
    echo '</script>';

} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">';
    echo "Error: " . $e->getCode() . " - " . $e->getMessage();
    echo '</div>';
}

$conn->close();
?>
