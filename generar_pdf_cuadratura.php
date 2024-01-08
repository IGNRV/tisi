<?php
require_once 'fpdf/fpdf.php';

$datos = json_decode(file_get_contents('php://input'), true);

$pdf = new FPDF('L'); // Cambia la orientación a horizontal si necesitas más espacio
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

foreach ($datos['tablas'] as $tablaDatos) {
    // Asumir que el primer elemento contiene el nombre del medio de pago y los encabezados
    $nombreMedioPago = array_shift($tablaDatos[0]); // Extrae el nombre del medio de pago
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, $nombreMedioPago, 0, 1, 'C'); // Título del medio de pago
    $pdf->Ln(5);

    // Asumir que ahora el primer elemento contiene los encabezados después de quitar el nombre del medio de pago
    $esEncabezado = true;
    $pdf->SetFont('Arial', 'B', 10);
    foreach ($tablaDatos as $fila) {
        if ($esEncabezado) {
            foreach ($fila as $indice => $celda) {
                $ancho = $indice < 4 ? 30 : 20; // Ajusta el ancho de las celdas para los encabezados
                $pdf->Cell($ancho, 6, $celda, 1);
            }
            $pdf->Ln();
        } else {
            foreach ($fila as $indice => $celda) {
                $ancho = $indice < 4 ? 30 : 20; // Ajusta el ancho de las celdas para el resto de las filas
                $pdf->Cell($ancho, 6, $celda, 1);
            }
            $pdf->Ln();
        }
        $esEncabezado = false; // Solo la primera fila es el encabezado
    }
    $pdf->Ln(10); // Espacio entre tablas
}

$pdf->Output('D', 'cuadratura.pdf');
exit;
?>
