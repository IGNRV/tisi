<?php
require_once 'db.php';

// Seleccionar todos los usuarios con suscripción activa
$query = "SELECT id FROM usuarios WHERE estado_suscripcion = 1";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $userId = $row['id'];

    // Buscar la suscripción correspondiente en la tabla suscripcion_tisi
    $suscripcionQuery = "SELECT id, dia_suscripcion, suscripciones_pagadas FROM suscripcion_tisi WHERE id_usuario = ?";
    if ($suscripcionStmt = $conn->prepare($suscripcionQuery)) {
        $suscripcionStmt->bind_param("i", $userId);
        $suscripcionStmt->execute();
        $suscripcionStmt->store_result();
        $suscripcionStmt->bind_result($suscripcionId, $diaSuscripcion, $suscripcionesPagadas);
        
        if ($suscripcionStmt->fetch()) {
            if ($diaSuscripcion > 30) {
                // Reducir suscripciones pagadas o desactivar suscripción si necesario
                if ($suscripcionesPagadas > 0) {
                    $nuevoSuscripcionesPagadas = $suscripcionesPagadas - 1;
                    $updateSuscripcionQuery = "UPDATE suscripcion_tisi SET suscripciones_pagadas = ?, dia_suscripcion = 1 WHERE id = ?";
                    if ($updateSuscripcionStmt = $conn->prepare($updateSuscripcionQuery)) {
                        $updateSuscripcionStmt->bind_param("ii", $nuevoSuscripcionesPagadas, $suscripcionId);
                        $updateSuscripcionStmt->execute();
                        $updateSuscripcionStmt->close();
                    }
                } else {
                    // Desactivar suscripción
                    $updateUserQuery = "UPDATE usuarios SET estado_suscripcion = 0 WHERE id = ?";
                    if ($updateUserStmt = $conn->prepare($updateUserQuery)) {
                        $updateUserStmt->bind_param("i", $userId);
                        $updateUserStmt->execute();
                        $updateUserStmt->close();
                    }
                }
            } else {
                // Incrementar el día de suscripción
                $nuevoDiaSuscripcion = $diaSuscripcion + 1;
                $updateSuscripcionQuery = "UPDATE suscripcion_tisi SET dia_suscripcion = ? WHERE id = ?";
                if ($updateSuscripcionStmt = $conn->prepare($updateSuscripcionQuery)) {
                    $updateSuscripcionStmt->bind_param("ii", $nuevoDiaSuscripcion, $suscripcionId);
                    $updateSuscripcionStmt->execute();
                    $updateSuscripcionStmt->close();
                }
            }
        }
        $suscripcionStmt->close();
    }
}

$conn->close();
?>
