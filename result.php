<?php
/**
 * Pagina del comercio para redireccion del pagador
 * A esta página Flow redirecciona al pagador pasando vía POST
 * el token de la transacción. En esta página el comercio puede
 * mostrar su propio comprobante de pago
 */
ini_set('display_errors', 1);
require(__DIR__ . "/PHP-API-CLIENT/lib/FlowApi.class.php");

// Incluir la hoja de estilos de Bootstrap
echo '<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">';

try {
    // Recibe el token enviado por Flow
    if (!isset($_POST["token"])) {
        throw new Exception("No se recibió el token", 1);
    }
    $token = filter_input(INPUT_POST, 'token');
    $params = array("token" => $token);

    // Indica el servicio a utilizar
    $serviceName = "payment/getStatus";
    $flowApi = new FlowApi();
    $response = $flowApi->send($serviceName, $params, "GET");
    
    // Procesar la respuesta aquí (envío de la boleta, etc.)

    // Informar al usuario
    echo '<div class="alert alert-success" role="alert">';
    echo 'Se ha enviado la boleta de pago a tu correo electrónico. Serás redirigido al inicio en unos segundos.';
    echo '</div>';

    // Redirigir al usuario después de 5 segundos
    echo '<script>';
    echo 'setTimeout(function(){ window.location.href = "https://trackitsellit.oralisisdataservice.cl/welcome.php"; }, 5000);';
    echo '</script>';

} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">';
    echo "Error: " . $e->getCode() . " - " . $e->getMessage();
    echo '</div>';
}
?>
