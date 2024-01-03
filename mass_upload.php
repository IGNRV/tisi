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

    // Verificar si existe la categoría 'ETC' para el usuario
    $categoria_etc_id = null;
    $stmt = $conn->prepare("SELECT id_categoria FROM categorias WHERE nombre_categoria = 'ETC' AND id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Si la categoría existe, obtener el id_categoria
        $categoria = $result->fetch_assoc();
        $categoria_etc_id = $categoria['id_categoria'];
    } else {
        // Si no existe, insertar la nueva categoría 'ETC'
        $stmt = $conn->prepare("INSERT INTO categorias (nombre_categoria, id_usuario) VALUES ('ETC', ?)");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $categoria_etc_id = $conn->insert_id; // Obtener el id de la categoría recién insertada
    }

    $conn->begin_transaction(); // Iniciar transacción

    try {
        // Recorrer las filas del archivo, comenzando por la fila 2
        for ($i = 2; $i <= count($sheetData); $i++) {
            $nombre_px = $sheetData[$i]['A']; // Asumiendo que nombre_px está en la columna A
            $precio = $sheetData[$i]['B']; // Asumiendo que precio está en la columna B
            $stock = $sheetData[$i]['C']; // Asumiendo que stock está en la columna C

            // Preparar y ejecutar la consulta SQL para insertar los datos con la categoría 'ETC'
            $stmt = $conn->prepare("INSERT INTO productos (nombre_px, precio, stock, id_categoria, id_usuario) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siisi", $nombre_px, $precio, $stock, $categoria_etc_id, $id_usuario);
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
