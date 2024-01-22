<?php
require_once 'fpdf/fpdf.php'; // Asegúrate de que este es el camino correcto a FPDF
require_once 'db.php'; // Asume que db.php contiene la conexión a la base de datos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Recoger los datos pasados desde registrar_pago.php
$medioPago = urldecode($_GET['medioPago']); // Asegúrate de decodificar el valor
$total = isset($_GET['total']) ? floatval($_GET['total']) : 0;
$diferencia = isset($_GET['diferencia']) ? floatval($_GET['diferencia']) : 0;
$productosVendidos = json_decode($_GET['productosVendidos'], true);
$montoPagadoCliente = $_SESSION['montoPagadoCliente'];
$montoAdicional = isset($_GET['montoAdicional']) ? floatval($_GET['montoAdicional']) : 0;





// Obtener los datos de la empresa
$idUsuario = $_SESSION['id'];
$query = "SELECT razon_social, rut, direccion, comuna FROM negocio WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$datosEmpresa = $result->fetch_assoc();
$stmt->close();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Imprimir la información de la empresa en la boleta
if ($datosEmpresa) {
    $pdf->Cell(0, 10, 'Datos de la Empresa:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Razon Social: ' . $datosEmpresa['razon_social'], 0, 1);
    $pdf->Cell(0, 10, 'RUT: ' . $datosEmpresa['rut'], 0, 1);
    $pdf->Cell(0, 10, 'Domicilio: ' . $datosEmpresa['direccion'], 0, 1);
    $pdf->Cell(0, 10, 'Comuna: ' . $datosEmpresa['comuna'], 0, 1);
    $pdf->Ln(10);
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Boleta de Venta', 0, 1, 'C');
$pdf->Ln(10);

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

    $pdf->Cell(40, 10, $producto['nombre']);
    $pdf->Cell(40, 10, '$' . number_format($producto['precio'], 0, '.',''));
    $pdf->Cell(40, 10, $producto['cantidadVendida']); // Ahora incluye la descripción de gramos o unidades
    $pdf->Cell(40, 10, '$' . number_format($producto['total'], 0, '.', ''));
    $pdf->Ln();
    }

// Mostrar total, medio de pago y diferencia
$pdf->Cell(40, 10, "Monto Adicional: $" . number_format($montoAdicional, 0, '.', ''));
$pdf->Ln();
$pdf->Ln(10);
$pdf->Cell(40, 10, "Total: $" . number_format($total, 0, '.',
''));
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
$pdf->Cell(40, 10, "Monto Pagado por Cliente: $" . number_format($montoPagadoCliente, 0, '.', ''));
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
