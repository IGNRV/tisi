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
                // Actualizar estado_suscripcion en la tabla usuarios
                $updateQuery = "UPDATE usuarios SET estado_suscripcion = 1 WHERE id = ?";
                if ($updateStmt = $conn->prepare($updateQuery)) {
                    $updateStmt->bind_param("i", $oralisisUserId);
                    $updateStmt->execute();
                    if ($updateStmt->affected_rows == 0) {
                        echo "No se actualizó ningún registro. Verifica el ID del usuario.";
                    } else {
                        echo "Suscripción actualizada con éxito.";
                    }
                    $updateStmt->close();
                } else {
                    throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
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
