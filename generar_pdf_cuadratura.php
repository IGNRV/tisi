<?php
require_once 'fpdf/fpdf.php';

$datos = json_decode(file_get_contents('php://input'), true);

$pdf = new FPDF('L');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

foreach ($datos['tablas'] as $tablaDatos) {
    $nombreMedioPago = array_shift($tablaDatos[0]);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, $nombreMedioPago, 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $esEncabezado = true;
    foreach ($tablaDatos as $fila) {
        if ($esEncabezado) {
            foreach ($fila as $indice => $celda) {
                $ancho = $indice < 4 ? 30 : 20;
                $pdf->Cell($ancho, 6, $celda, 1);
            }
            $pdf->Ln();
        } else {
            foreach ($fila as $indice => $celda) {
                $ancho = $indice < 4 ? 30 : 20;
                $pdf->Cell($ancho, 6, $celda, 1);
            }
            $pdf->Ln();
        }
        $esEncabezado = false;
    }

    // Agregar el total acumulado al final de cada tabla
    $totalAcumulado = array_pop($tablaDatos);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, $totalAcumulado[1], 0, 1, 'R'); // Asumiendo que el total está en la segunda posición del array
    $pdf->Ln(10);
}

$pdf->Output('D', 'cuadratura.pdf');
exit;
?>