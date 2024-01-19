<?php
// buscar_productos.php
require_once 'db.php';
session_start();

// Consultar el estado de suscripción del usuario actual
$estadoSuscripcion = 0; // Valor predeterminado
if (isset($_SESSION['id'])) {
    $estadoSuscripcionQuery = "SELECT estado_suscripcion FROM usuarios WHERE id = ?";
    if ($estadoSuscripcionStmt = $conn->prepare($estadoSuscripcionQuery)) {
        $estadoSuscripcionStmt->bind_param("i", $_SESSION['id']);
        $estadoSuscripcionStmt->execute();
        $estadoSuscripcionStmt->bind_result($estadoSuscripcion);
        $estadoSuscripcionStmt->fetch();
        $estadoSuscripcionStmt->close();
    }
}

// Si el estado de suscripción es 0, muestra el mensaje
if ($estadoSuscripcion == 0) {
    echo "<div class='alert alert-warning' role='alert'>
            No tienes una suscripción activa. Por favor, activa tu suscripción en el menú Suscripción por $20.000.
          </div>";
}

if (isset($_POST['buscar']) && !empty($_POST['buscar'])) {
    $buscar = $_POST['buscar'];

    $query = "SELECT p.nombre_px, p.precio, p.stock, p.kilogramos, c.nombre_categoria 
              FROM productos p 
              INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
              WHERE p.id_usuario = ? AND (p.nombre_px LIKE CONCAT('%', ?, '%') OR p.codigo_producto LIKE CONCAT('%', ?, '%'))";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iss", $_SESSION['id'], $buscar, $buscar);
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = [
                'nombre' => $row['nombre_px'],
                'precio' => $row['precio'],
                'stock' => $row['stock'],
                'kilogramos' => $row['kilogramos'], // Agrega esta línea
                'categoria' => $row['nombre_categoria']
            ];
        }
        echo json_encode($productos);
        $stmt->close();
    } else {
        echo json_encode(["error" => $conn->error]);
    }
    exit;
}
$query_medios_pago = "SELECT id_medios_de_pago, nombre_medio_pago FROM medios_de_pago";
$medios_pago = [];
if ($stmt = $conn->prepare($query_medios_pago)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $medios_pago[] = $row;
    }
    $stmt->close();
} else {
    echo json_encode(["error" => $conn->error]);
}
?>


<div class="container-fluid">
    <div class="row">

        <!-- Columna izquierda: Formulario de búsqueda y tabla de productos seleccionados -->
        <div class="col-sm-12 col-lg-6">
<!-- Formulario para buscar productos -->
<form action="welcome.php?page=buscar_productos" method="post" class="my-4">
<div class="form-group">
                    <label for="buscar" class="h4">Buscar Producto</label>
                    <input type="text" name="buscar" class="form-control" id="buscar" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
                </div>
    <!-- Lista para mostrar resultados de búsqueda -->
    <ul id="resultados-busqueda" class="list-group"></ul>
</form>

<table class="table table-striped" id="tabla-seleccionados">
<thead class="thead-dark" style="font-size: 15px;">
    <tr>
        <th>PX</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Total</th> <!-- Nueva columna para Total -->
        <th>Categoría</th>
        <th>Acciones</th>
    </tr>
</thead>
    <tbody style="font-size: 15px;">
    </tbody>
</table>
</div>
<div class="col-sm-12 col-lg-6">

<div class="modal fade" id="modalCantidad" tabindex="-1" role="dialog" aria-labelledby="modalCantidadLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCantidadLabel">Modificar Cantidad</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="number" id="inputCantidad" class="form-control" min="1">
                <input type="hidden" id="productoSeleccionadoId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="actualizarCantidad()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
<div id="totalPrecio" style="font-weight: bold; font-size: 18px;">Total: $0</div>
            <div id="iva" style="font-weight: bold; font-size: 18px;">IVA (19%): $0</div>
            <div id="totalConIva" style="font-weight: bold; font-size: 18px;">Total con IVA: $0</div>


            <div class="form-group">
                <label for="medioPago" style="font-size: 18px;">Medio de Pago</label>
                <select class="form-control" id="medioPago" style="font-size: 15px;" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
        <?php foreach ($medios_pago as $medio) : ?>
            <option value="<?php echo htmlspecialchars($medio['id_medios_de_pago']); ?>"><?php echo htmlspecialchars($medio['nombre_medio_pago']); ?></option>
        <?php endforeach; ?>
    </select>
</div>


<div>
                <label for="montopagar" style="font-size: 18px;">Monto a pagar</label>
                <input type="text" name="montopagar" class="form-control" id="montopagar" style="font-size: 15px;" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="usarTotal" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
                <label class="form-check-label" for="usarTotal" style="font-size: 15px;">Usar total de la compra</label>
            </div>


            <div>
                <label for="diferencia" style="font-size: 18px;">Diferencia</label>
                <input type="text" name="diferencia" class="form-control" id="diferencia" disabled style="font-size: 15px;">
            </div>

<button type="button" class="btn btn-primary" id="registrarPago" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>REGISTRAR PAGO</button>

</div>

</div>
</div>