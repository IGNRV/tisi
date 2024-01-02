<?php
require_once 'db.php';
require 'vendor/autoload.php'; // Asegúrate de incluir el autoload de Composer

use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

if (isset($_SESSION['id']) && isset($_FILES['fileUpload'])) {
    $id_usuario = $_SESSION['id'];
    $filePath = $_FILES['fileUpload']['tmp_name'];

    // Leer el archivo .ods
    $reader = IOFactory::createReader('Ods');
    $spreadsheet = $reader->load($filePath);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    $conn->begin_transaction(); // Iniciar transacción

    try {
        // Recorrer las filas del archivo, comenzando por la fila 2
        for ($i = 2; $i <= count($sheetData); $i++) {
            $nombre_px = $sheetData[$i]['A']; // Asumiendo que nombre_px está en la columna A
            $precio = $sheetData[$i]['B']; // Asumiendo que precio está en la columna B
            $stock = $sheetData[$i]['C']; // Asumiendo que stock está en la columna C

            // Preparar y ejecutar la consulta SQL para insertar los datos
            $stmt = $conn->prepare("INSERT INTO productos (nombre_px, precio, stock, id_usuario) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $nombre_px, $precio, $stock, $id_usuario);
            $stmt->execute();
        }

        $conn->commit(); // Confirmar transacción
        header("Location: product_stock.php?update_success");
    } catch (Exception $e) {
        $conn->rollback(); // Revertir transacción en caso de error
        header("Location: product_stock.php?error");
    }
} else {
    header("Location: product_stock.php?error");
}
?>
