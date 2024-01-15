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

    // Verificar si la categoría 'ETC' existe para el usuario
    $categoria_etc_id = null;
    $stmt = $conn->prepare("SELECT id_categoria FROM categorias WHERE nombre_categoria = 'ETC' AND id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $categoria_etc_id = $row['id_categoria'];
    } else {
        // Si no existe, insertar la categoría 'ETC' y obtener su id
        $stmt = $conn->prepare("INSERT INTO categorias (nombre_categoria, id_usuario) VALUES ('ETC', ?)");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $categoria_etc_id = $conn->insert_id;
    }

    $conn->begin_transaction(); // Iniciar transacción

    try {
        for ($i = 2; $i <= count($sheetData); $i++) {
            $nombre_px = $sheetData[$i]['A']; // Nombre del producto
            $precio = $sheetData[$i]['B']; // Precio
            $stock = isset($sheetData[$i]['C']) ? $sheetData[$i]['C'] : 0; // Stock
            $kilogramos = isset($sheetData[$i]['D']) ? $sheetData[$i]['D'] : 0; // Kilogramos
            $codigo_producto = isset($sheetData[$i]['E']) ? $sheetData[$i]['E'] : ''; // Código del producto

            // Insertar producto con la categoría 'ETC'
            $stmt = $conn->prepare("INSERT INTO productos (nombre_px, precio, stock, kilogramos, id_categoria, id_usuario, codigo_producto) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siidiis", $nombre_px, $precio, $stock, $kilogramos, $categoria_etc_id, $id_usuario, $codigo_producto);
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