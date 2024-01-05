<?php
require_once 'fpdf/fpdf.php'; // Asegúrate de que este es el camino correcto a FPDF

// Recoger los datos pasados desde registrar_pago.php
$medioPago = urldecode($_GET['medioPago']); // Asegúrate de decodificar el valor
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
    $pdf->Cell(40, 10, '$' . number_format($precio, 0, '.', ''));
    $pdf->Cell(40, 10, $cantidad);
    $pdf->Cell(40, 10, '$' . number_format($totalProducto, 0, '.', ''));
    $pdf->Ln();
}


// Mostrar total, medio de pago y diferencia
$pdf->Ln(10);
$pdf->Cell(40, 10, "Total: $" . number_format($total, 0, '.', ''));
$pdf->Ln();

// Calcular y mostrar IVA (19% del total)
$iva = $total * 0.19;
$pdf->Cell(40, 10, "IVA (19%): $" . number_format($iva, 0, '.', ''));
$pdf->Ln();

// Sumar el IVA al total y mostrar total con IVA
$totalConIVA = $total + $iva;
$pdf->Cell(40, 10, "Total con IVA: $" . number_format($totalConIVA, 0, '.', ''));
$pdf->Ln();

$pdf->Cell(40, 10, "Medio de Pago: " . $medioPago);
$pdf->Ln();
$pdf->Cell(40, 10, "Diferencia: $" . number_format($diferencia, 0, '.', ''));

// Enviar encabezados para forzar la descarga del PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="boleta.pdf"');
header('Pragma: no-cache');
header('Expires: 0');

// Salida del PDF
$pdf->Output('D', 'boleta.pdf');
exit;
?>
