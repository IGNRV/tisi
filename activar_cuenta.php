<?php
session_start();
require_once 'db.php';

if(isset($_GET['token'])) {
    $token = $_GET['token'];

    // Buscar el token en la base de datos
    $query = "SELECT * FROM usuarios WHERE token_activacion = ?";
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if($resultado->num_rows == 1) {
            // Actualizar el estado de la cuenta a activado
            $update = "UPDATE usuarios SET cuenta_activada = 1 WHERE token_activacion = ?";
            if($update_stmt = $conn->prepare($update)) {
                $update_stmt->bind_param("s", $token);
                if($update_stmt->execute()) {
                    // Redirigir al usuario
                    header("Location: https://trackitsellit.oralisisdataservice.cl/");
                    exit();
                }
            }
        }
    }
}
?>
