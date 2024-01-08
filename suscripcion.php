<?php
// suscripcion.php
session_start();
require_once 'db.php';
require_once '/var/www/html/tisi/PHP-API-CLIENT/lib/FlowApi.class.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Por favor, inicia sesión para verificar el estado de tu suscripción.";
    exit;
}

$userId = $_SESSION['id']; // Obtiene el ID del usuario de la sesión

// Prepara la consulta SQL para verificar el estado de suscripción y suscripciones pagadas
$query = "SELECT u.estado_suscripcion, u.email, s.suscripciones_pagadas FROM usuarios u LEFT JOIN suscripcion_tisi s ON u.id = s.id_usuario WHERE u.id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($estado_suscripcion, $userEmail, $suscripcionesPagadas);
        $stmt->fetch();

        echo $estado_suscripcion == 1 ? "Suscripción activa." : "Suscripción no activa.";
        echo "<br>Suscripciones pagadas: " . ($suscripcionesPagadas ?? 0);

        // Prepara el arreglo de datos
        $commerceOrder = rand(1100, 2000);
        $params = array(
            "commerceOrder" => $commerceOrder,
            "subject" => "Pago de suscripción",
            "currency" => "CLP",
            "amount" => 5000,
            "email" => $userEmail,
            "paymentMethod" => 9,
            "urlConfirmation" => Config::get("BASEURL") . "/confirm.php",
            "urlReturn" => Config::get("BASEURL") . "/result.php"
        );
        
        try {
            // Instancia la clase FlowApi
            $flowApi = new FlowApi();
            // Ejecuta el servicio
            $response = $flowApi->send("payment/create", $params, "POST");

            // Prepara la consulta SQL para registrar el pago
            $insertQuery = "INSERT INTO registro_de_pagos (orden_comercio, asunto, monto_del_pago, token, oralisis_user_id, estado) VALUES (?, ?, ?, ?, ?, ?)";
            if ($insertStmt = $conn->prepare($insertQuery)) {
                $estado = 1;
                $insertStmt->bind_param("isisii", $commerceOrder, $params["subject"], $params["amount"], $response["token"], $userId, $estado);
                $insertStmt->execute();
                $insertStmt->close();
            }


            // Prepara url para redireccionar el browser del pagador
            $redirect = $response["url"] . "?token=" . $response["token"];
            echo "<button onclick=\"window.location.href = '$redirect';\">Pagar suscripción</button>";
        } catch (Exception $e) {
            echo "Error: " . $e->getCode() . " - " . $e->getMessage();
        }
    } else {
        echo "No se encontró el usuario.";
    }
    $stmt->close();
} else {
    echo "Error al preparar la consulta: " . $conn->error;
}

$conn->close();
?>
