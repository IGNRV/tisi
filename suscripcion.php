<?php
// suscripcion.php
session_start();
require_once 'db.php';
require_once '/var/www/html/tisi/PHP-API-CLIENT/lib/FlowApi.class.php';
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

// Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p class='alert alert-warning'>Por favor, inicia sesión para verificar el estado de tu suscripción.</p>";
    exit;
}

$userId = $_SESSION['id']; // Obtiene el ID del usuario de la sesión

// Verificar si el usuario tiene un descuento inicial
$descuentoQuery = "SELECT descuento_inicial FROM usuarios WHERE id = ?";
$descuentoInicial = 0; // Valor predeterminado

if ($descuentoStmt = $conn->prepare($descuentoQuery)) {
    $descuentoStmt->bind_param("i", $userId);
    $descuentoStmt->execute();
    $descuentoStmt->bind_result($descuentoInicial);
    $descuentoStmt->fetch();
    $descuentoStmt->close();
}

// Ajustar monto de acuerdo al descuento inicial
$monto = ($descuentoInicial == 0) ? 10000 : 20000;

if ($descuentoInicial == 0) {
    echo "<div class='alert alert-info' role='alert'>";
    echo "¡Buenas noticias! Tu primera suscripción tiene un valor especial de 10.000 CLP.";
    echo "</div>";
}

// Prepara la consulta SQL para verificar el estado de suscripción y suscripciones pagadas
$query = "SELECT u.estado_suscripcion, u.email, s.suscripciones_pagadas FROM usuarios u LEFT JOIN suscripcion_tisi s ON u.id = s.id_usuario WHERE u.id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($estado_suscripcion, $userEmail, $suscripcionesPagadas);
        $stmt->fetch();

        echo "<div class='container'>";
        echo "<div class='card mt-5'>";
        echo "<div class='card-body'>";
        echo "<h3 class='card-title'>Estado de Suscripción</h3>";
        echo "<p class='card-text'>" . ($estado_suscripcion == 1 ? "Suscripción activa." : "Suscripción no activa.") . "</p>";
        echo "<p class='card-text'>Suscripciones pagadas: " . ($suscripcionesPagadas ?? 0) . "</p>";

        // Prepara el arreglo de datos
        $commerceOrder = rand(1100, 2000);
        $params = array(
            "commerceOrder" => $commerceOrder,
            "subject" => "Pago de suscripción",
            "currency" => "CLP",
            "amount" => $monto,
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
        echo "</div>"; // Cierre card-body
        echo "</div>"; // Cierre card
        echo "</div>"; // Cierre container
    } else {
        echo "<p class='alert alert-danger'>No se encontró el usuario.</p>";
    }
    $stmt->close();
} else {
    echo "<p class='alert alert-danger'>Error al preparar la consulta: " . $conn->error . "</p>";
}

$conn->close();
?>

<!-- Incluir CSS de Bootstrap -->
<link href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' rel='stylesheet'>

<!-- Estilos personalizados -->
<style>
    .container {
        max-width: 600px;
        margin-top: 50px;
    }
    .card-title {
        color: #333;
    }
    .card-text {
        color: #555;
    }
</style>
