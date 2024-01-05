<?php
// cuadratura_caja.php
require_once 'db.php';

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
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
    echo "Error al obtener los medios de pago: " . $conn->error;
}

?>

<!-- Formulario para la cuadratura de caja -->
<form id="formCuadratura" onsubmit="buscarCuadratura(event)">
    <div class="form-group">
        <label for="fecha">Fecha</label>
        <input type="date" name="fecha" class="form-control" id="fecha" required>
    </div>

    <div class="form-group">
        <label for="medioPago">Medio de Pago</label>
        <select class="form-control" id="medioPago" name="medioPago" required>
            <?php foreach ($medios_pago as $medio): ?>
                <option value="<?php echo htmlspecialchars($medio['id_medios_de_pago']); ?>">
                    <?php echo htmlspecialchars($medio['nombre_medio_pago']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<!-- Aquí es donde se mostrarán los resultados de la búsqueda -->
<div id="resultadosCuadratura"></div>

<script>
function buscarCuadratura(event) {
    event.preventDefault(); // Evitar que el formulario se envíe de la manera tradicional
    
    var fecha = document.getElementById('fecha').value;
    var medioPago = document.getElementById('medioPago').value;
    
    fetch('procesamiento_cuadratura.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'fecha=' + fecha + '&medioPago=' + medioPago
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('resultadosCuadratura');
        resultadosDiv.innerHTML = ''; // Limpiar resultados anteriores

        // Crear la tabla
        const tabla = document.createElement('table');
        tabla.classList.add('table', 'table-striped'); // Agregar clases de Bootstrap para un estilo sencillo y elegante

        // Crear el encabezado de la tabla
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th>Medio de pago</th>
                <th>Total</th>
                <th>Monto pagado por cliente</th>
                <th>Diferencia</th>
                <th>Fecha</th>
                <th>IVA</th>
                <th>Total con IVA</th>
            </tr>
        `;
        tabla.appendChild(thead);

        // Crear el cuerpo de la tabla
        const tbody = document.createElement('tbody');
        data.forEach(transaccion => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${transaccion.medio_de_pago}</td>
                <td>${transaccion.total}</td>
                <td>${transaccion.monto_pagado_cliente}</td>
                <td>${transaccion.diferencia}</td>
                <td>${transaccion.date_created}</td>
                <td>${transaccion.iva}</td>
                <td>${transaccion.total_con_iva}</td>
            `;
            tbody.appendChild(tr);
        });
        tabla.appendChild(tbody);

        // Añadir la tabla al div de resultados
        resultadosDiv.appendChild(tabla);
    })
    .catch(error => console.error('Error:', error));
}
</script>
