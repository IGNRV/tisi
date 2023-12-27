<?php
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = 'Polaco12345$$##';
$dbName = 'trackitsellit';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>