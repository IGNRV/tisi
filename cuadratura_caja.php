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

<button type="button" class="btn btn-primary" id="generarPdf">Generar PDF</button>


<!-- Aquí es donde se mostrarán los resultados de la búsqueda -->
<div id="resultadosCuadratura"></div>

<script>
let busquedasRealizadas = {};

function buscarCuadratura(event) {
    event.preventDefault(); // Evitar que el formulario se envíe de la manera tradicional
    
    var fecha = document.getElementById('fecha').value;
    var medioPagoSelect = document.getElementById('medioPago');
    var medioPago = medioPagoSelect.value;
    var medioPagoNombre = medioPagoSelect.options[medioPagoSelect.selectedIndex].text;
    var claveBusqueda = `${fecha}-${medioPago}`;

    // Verificar si ya se realizó esta búsqueda
    if (busquedasRealizadas[claveBusqueda]) {
        alert('Ya se mostraron los resultados para esta fecha y medio de pago.');
        return;
    }

    fetch('procesamiento_cuadratura.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'fecha=' + fecha + '&medioPago=' + medioPago
    })
    .then(response => response.json())
    .then(response => {
        const resultadosDiv = document.getElementById('resultadosCuadratura');

        // Crear y añadir un elemento para mostrar el nombre del medio de pago
        const medioPagoDiv = document.createElement('div');
        medioPagoDiv.innerHTML = `<strong>Medio de Pago: ${medioPagoNombre}</strong>`;
        resultadosDiv.appendChild(medioPagoDiv);

        const data = response.resultados;
        const totalAcumulado = response.totalAcumulado;

        // Crear la tabla
        const tabla = document.createElement('table');
        tabla.classList.add('table', 'table-striped'); // Agregar clases de Bootstrap para un estilo sencillo y elegante

        // Crear el encabezado de la tabla
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th>Medio de pago</th>
                <th>Total</th>
                <th>Total p. cliente</th>
                <th>Diferencia</th>
                <th>Fecha</th>
                <th>IVA</th>
                <th>Total + IVA</th>
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

        // Crear y añadir un elemento para mostrar el total acumulado
        const totalDiv = document.createElement('div');
        totalDiv.innerHTML = `<strong>Total acumulado con IVA: $${totalAcumulado.toFixed(2)}</strong>`;
        resultadosDiv.appendChild(totalDiv);

        // Marcar esta búsqueda como realizada
        busquedasRealizadas[claveBusqueda] = true;
    })
    .catch(error => console.error('Error:', error));
}

document.getElementById('generarPdf').addEventListener('click', function() {
    let tablas = document.querySelectorAll('#resultadosCuadratura table');
    let datosParaPdf = [];

    tablas.forEach(tabla => {
        let encabezados = tabla.querySelectorAll('thead th');
        let filas = tabla.querySelectorAll('tbody tr');
        let datosTabla = [];

        // Agregar encabezados
        let datosEncabezados = [];
        encabezados.forEach(encabezado => {
            datosEncabezados.push(encabezado.textContent);
        });
        datosTabla.push(datosEncabezados);

        // Agregar filas
        filas.forEach(fila => {
            let celdas = fila.querySelectorAll('td');
            let datosFila = [];
            celdas.forEach(celda => {
                datosFila.push(celda.textContent);
            });
            datosTabla.push(datosFila);
        });

        datosParaPdf.push(datosTabla);
    });

    // Enviar los datos al servidor para generar el PDF
    fetch('generar_pdf_cuadratura.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({tablas: datosParaPdf})
    })
    .then(response => response.blob())
    .then(blob => {
        // Crear un enlace temporal para descargar el PDF
        let url = window.URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'cuadratura.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
    })
    .catch(error => console.error('Error:', error));
});


</script>
