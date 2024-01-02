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
$pdf->SetFont('Arial', '', 12);
foreach ($productosVendidos as $producto) {
    $pdf->Cell(40, 10, $producto['nombre']);
    $pdf->Cell(40, 10, '$' . number_format((float)$producto['precio'], 2, '.', '')); // Asegúrate de que el precio se formatee correctamente
    $pdf->Cell(40, 10, $producto['cantidadVendida']);
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
