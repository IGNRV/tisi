<?php
// buscar_productos.php
require_once 'db.php';
session_start();

if (isset($_POST['buscar']) && !empty($_POST['buscar'])) {
    $buscar = $_POST['buscar'];

    // Preparar consulta para buscar productos con detalles adicionales
    $query = "SELECT p.nombre_px, p.precio, p.stock, c.nombre_categoria 
              FROM productos p 
              INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
              WHERE p.id_usuario = ? AND p.nombre_px LIKE CONCAT('%', ?, '%')";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("is", $_SESSION['id'], $buscar);
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = [
                'nombre' => $row['nombre_px'],
                'precio' => $row['precio'],
                'stock' => $row['stock'],
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
        $medios_pago[] = $row['nombre_medio_pago'];
    }
    $stmt->close();
} else {
    // Manejo de error, por ejemplo:
    echo json_encode(["error" => $conn->error]);
}
?>



<!-- Formulario para buscar productos -->
<form action="welcome.php?page=buscar_productos" method="post">
    <div class="form-group">
        <label for="buscar">Buscar Producto</label>
        <input type="text" name="buscar" class="form-control" id="buscar">
        <ul id="resultados-busqueda"></ul>
    </div>
</form>
<table class="table" id="tabla-seleccionados">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Categoría</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
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
<div id="totalPrecio" style="font-weight: bold;">Total: $0</div>

<div class="form-group">
    <label for="medioPago">Medio de Pago</label>
    <select class="form-control" id="medioPago">
        <?php foreach ($medios_pago as $medio) : ?>
            <option value="<?php echo htmlspecialchars($medio); ?>"><?php echo htmlspecialchars($medio); ?></option>
        <?php endforeach; ?>
    </select>
</div>


<div>
    <label for="montopagar">Monto a pagar</label>
    <input type="text" name="montopagar" class="form-control" id="montopagar">
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" id="usarTotal">
    <label class="form-check-label" for="usarTotal">
        Usar total de la compra
    </label>
</div>

<div>
    <label for="diferencia">Diferencia</label>
    <input type="text" name="diferencia" class="form-control" id="diferencia" disabled>
</div>

<button type="button" class="btn btn-primary" id="registrarPago">REGISTRAR PAGO</button>

