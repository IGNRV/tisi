<?php
require_once 'fpdf/fpdf.php'; // Asegúrate de que este es el camino correcto a FPDF

// Recoger los datos pasados desde registrar_pago.php
$medioPago = $_GET['medioPago'];
$total = $_GET['total'];
$diferencia = $_GET['diferencia'];
$productosVendidos = json_decode($_GET['productosVendidos'], true);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(40, 10, 'Boleta de Venta');
$pdf->Ln(20);

// Listar los productos vendidos
$pdf->SetFont('Arial', 'B', 12);

// Encabezados de las columnas
$pdf->Cell(40, 10, 'Nombre', 1);
$pdf->Cell(40, 10, 'Precio Unitario', 1);
$pdf->Cell(40, 10, 'Cantidad', 1);
$pdf->Cell(40, 10, 'Total', 1);
$pdf->Ln();

// Restablecer la fuente para el contenido
$pdf->SetFont('Arial', '', 12);

// Listar los productos vendidos
foreach ($productosVendidos as $producto) {
    $precio = (float)$producto['precio'];
    $cantidad = (int)$producto['cantidadVendida'];
    $totalProducto = $precio * $cantidad;

    $pdf->Cell(40, 10, $producto['nombre']);
    $pdf->Cell(40, 10, '$' . number_format($precio, 2, '.', ''));
    $pdf->Cell(40, 10, $cantidad);
    $pdf->Cell(40, 10, '$' . number_format($totalProducto, 2, '.', ''));
    $pdf->Ln();
}


// Mostrar total, medio de pago y diferencia
$pdf->Ln(10);
$pdf->Cell(40, 10, "Total: $" . $total);
$pdf->Ln();
$pdf->Cell(40, 10, "Medio de Pago: " . $medioPago);
$pdf->Ln();
$pdf->Cell(40, 10, "Diferencia: $" . $diferencia);

// Enviar encabezados para forzar la descarga del PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="boleta.pdf"');
header('Pragma: no-cache');
header('Expires: 0');

// Salida del PDF
$pdf->Output('D', 'boleta.pdf');
exit;
?>
