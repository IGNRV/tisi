<?php
require_once 'fpdf/fpdf.php'; // Asegúrate de que este es el camino correcto a FPDF

// Recibir los datos enviados desde el script JavaScript
$datos = json_decode(file_get_contents('php://input'), true);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

foreach ($datos['tablas'] as $tablaDatos) {
    $esEncabezado = true;
    foreach ($tablaDatos as $fila) {
        $pdf->SetFont('Arial', $esEncabezado ? 'B' : '', 10);
        foreach ($fila as $indice => $celda) {
            // Ajustar el tamaño de la celda según la columna
            $ancho = $indice < 4 ? 30 : 20; // Ejemplo de ajuste de ancho
            $pdf->Cell($ancho, 6, $celda, 1);
        }
        $pdf->Ln();
        $esEncabezado = false; // No es encabezado para las siguientes filas
    }
    $pdf->Ln(10); // Espacio entre tablas
}

$pdf->Output('D', 'cuadratura.pdf');
exit;

?>
