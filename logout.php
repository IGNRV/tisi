<?php
session_start();
session_destroy(); // Destruye la sesión
header("Location: https://trackitsellit.oralisisdataservice.cl/"); // Redirige al usuario
exit;
?>
