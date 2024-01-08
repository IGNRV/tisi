<?php
// result.php
ini_set('display_errors', 1);
require(__DIR__ . "/PHP-API-CLIENT/lib/FlowApi.class.php");
require_once 'db.php';

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "No estás logueado. Por favor, inicia sesión.";
    exit;
}

if (!isset($_SESSION['id'])) {
    echo "ID de sesión no encontrado.";
    exit;
}

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
    
    // Asegúrate de validar la respuesta y el estado del pago
    if ($response['status'] == 2) { // Suponiendo que 2 es un pago exitoso
        $userId = $_SESSION['id'];

        $updateQuery = "UPDATE usuarios SET estado_suscripcion = 1 WHERE id = ?";
        if ($stmt = $conn->prepare($updateQuery)) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            if ($stmt->affected_rows == 0) {
                echo "No se actualizó ningún registro. Verifica el ID del usuario.";
            } else {
                echo "Suscripción actualizada con éxito.";
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
