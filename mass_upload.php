<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

if (isset($_SESSION['id']) && isset($_FILES['fileUpload'])) {
    $id_usuario = $_SESSION['id'];
    $filePath = $_FILES['fileUpload']['tmp_name'];

    $reader = IOFactory::createReader('Ods');
    $spreadsheet = $reader->load($filePath);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    $conn->begin_transaction(); // Iniciar transacción

    try {
        for ($i = 2; $i <= count($sheetData); $i++) {
            $nombre_px = $sheetData[$i]['A']; // Nombre del producto
            $precio = $sheetData[$i]['B']; // Precio
            $stock = isset($sheetData[$i]['C']) ? $sheetData[$i]['C'] : 0; // Stock, con 0 como valor predeterminado
            $kilogramos = isset($sheetData[$i]['D']) ? $sheetData[$i]['D'] : 0; // Kilogramos, con 0 como valor predeterminado

            // Preparar consulta SQL
            $stmt = $conn->prepare("INSERT INTO productos (nombre_px, precio, stock, kilogramos, id_categoria, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiiii", $nombre_px, $precio, $stock, $kilogramos, $categoria_etc_id, $id_usuario);
            $stmt->execute();
        }

        $conn->commit(); // Confirmar transacción
        header("Location: welcome.php?page=products&update_success");
    } catch (Exception $e) {
        $conn->rollback(); // Revertir transacción en caso de error
        header("Location: welcome.php?page=products&error");
    }
} else {
    header("Location: welcome.php?page=products&error");
}
?>
