<?php
// cuadratura_caja.php
require_once 'db.php';

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

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

// Consulta para obtener los nombres de los medios de pago
$query_medios_pago = "SELECT id_medios_de_pago, nombre_medio_pago FROM medios_de_pago";
$medios_pago = [];
$medios_pago_map = []; // Mapa para convertir ID de medio de pago en nombre
if ($stmt = $conn->prepare($query_medios_pago)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $medios_pago[] = $row;
        $medios_pago_map[$row['id_medios_de_pago']] = $row['nombre_medio_pago']; // Guardar en el mapa
    }
    $stmt->close();
} else {
    echo "Error al obtener los medios de pago: " . $conn->error;
}

// Pasar el mapa de medios de pago a JavaScript
echo "<script>var mediosPagoMap = " . json_encode($medios_pago_map) . ";</script>";
?>
<!-- Formulario para la cuadratura de caja -->
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Cuadratura de Caja</h5>
                    <form id="formCuadratura" onsubmit="buscarCuadratura(event)">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" name="fecha" class="form-control" id="fecha" required <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="medioPago">Medio de Pago</label>
                            <select class="form-control" id="medioPago" name="medioPago" required <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>
                                <?php foreach ($medios_pago as $medio): ?>
                                    <option value="<?php echo htmlspecialchars($medio['id_medios_de_pago']); ?>">
                                        <?php echo htmlspecialchars($medio['nombre_medio_pago']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>Buscar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-8 mx-auto">
            <button type="button" class="btn btn-secondary" id="generarPdf" <?php echo $estadoSuscripcion == 0 ? 'disabled' : ''; ?>>Generar PDF</button>
</div>
</div>
<div class="row mt-3" id="resultadosSeccion" style="display: none;">
    <div class="col-lg-8 mx-auto">
        <!-- Resultados con estilo de tarjeta y desplazamiento horizontal para tablas en dispositivos pequeños -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Resultados</h5>
                <div id="resultadosCuadratura" class="table-responsive"></div>
            </div>
        </div>
    </div>
</div>
</div>
<style>
    /* Estilos adicionales */
    .card {
        border-radius: 0.5rem;
    }

    .card-title {
        color: #333333;
        font-weight: bold;
    }

    .btn-primary {
        background-color: #4e73df;
        border: none;
    }

    .btn-secondary {
        background-color: #6c757d;
        border: none;
    }

    .table {
        margin-bottom: 0; /* Elimina el margen inferior de la tabla */
    }

    .table thead th {
        background-color: #f8f9fc;
        color: #4e73df;
        border-bottom: 2px solid #e3e6f0;
    }

    .table tbody td {
        color: #6e707e;
        border-bottom: 1px solid #e3e6f0;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }
</style>


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
        document.getElementById('resultadosSeccion').style.display = 'block';
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
                <td>${mediosPagoMap[transaccion.medio_de_pago]}</td>
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
        totalDiv.innerHTML = `<strong>Total acumulado con IVA: $${totalAcumulado.toFixed(0)}</strong>`;
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

        // Agregar el nombre del medio de pago al inicio de cada conjunto de datos de tabla
        let nombreMedioPago = tabla.previousSibling.textContent; // Asume que es el elemento inmediatamente antes de la tabla
        datosTabla.push([nombreMedioPago]);
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
