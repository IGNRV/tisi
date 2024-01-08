<?php
// suscripcion.php
session_start();
require_once 'db.php';
require_once '/var/www/html/tisi/PHP-API-CLIENT/lib/FlowApi.class.php'; // Asegúrate de que esta ruta sea correcta.

// Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Por favor, inicia sesión para verificar el estado de tu suscripción.";
    exit;
}

$userId = $_SESSION['id']; // Obtiene el ID del usuario de la sesión

// Prepara la consulta SQL para verificar el estado de suscripción
$query = "SELECT estado_suscripcion, email FROM usuarios WHERE id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($estado_suscripcion, $userEmail);
        $stmt->fetch();

        if ($estado_suscripcion == 0) {
            echo "Suscripción no activa.";
            // Inicia una transacción de pago en Flow
            // Prepara el arreglo de datos
            $params = array(
                "commerceOrder" => rand(1100, 2000),
                "subject" => "Pago de suscripción",
                "currency" => "CLP",
                "amount" => 5000,
                "email" => $userEmail, // Utiliza el email recuperado de la base de datos
                "paymentMethod" => 9,
                "urlConfirmation" => Config::get("BASEURL") . "/confirm.php",
                "urlReturn" => Config::get("BASEURL") . "/welcome.php?page=result"
            );
            
            try {
                // Instancia la clase FlowApi
                $flowApi = new FlowApi();
                // Ejecuta el servicio
                $response = $flowApi->send("payment/create", $params, "POST");
                /* var_dump($response); */
                // Prepara url para redireccionar el browser del pagador
                $redirect = $response["url"] . "?token=" . $response["token"];
                echo "<button onclick=\"window.location.href = '$redirect';\">Pagar suscripción</button>";
            } catch (Exception $e) {
                echo "Error: " . $e->getCode() . " - " . $e->getMessage();
            }
        } elseif ($estado_suscripcion == 1) {
            echo "Suscripción activa.";
        } else {
            echo "Estado de suscripción desconocido.";
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
