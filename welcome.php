<?php
session_start();

// Verifica si el usuario está logueado. Si no, redirige a index.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

require_once 'db.php';
?>


<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
    <!-- Incluir CSS de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
  body {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  }
  
  .navbar {
    z-index: 1030; /* Bootstrap generalmente utiliza 1030 para navbars */
  }
  
  .bg-secondary {
    background-color: #3f51b5 !important; /* Este es un color primario de la imagen */
  }
  
  .nav-link.active {
    background-color: #ff4081; /* Este es un color destacado de la imagen */
  }
  
  /* Ajustes para el sidebar */
  .sidebar {
    position: fixed; /* Fijo en la pantalla */
    top: 0; /* Alineado con la parte superior */
    left: 0; /* Alineado con la parte izquierda */
    width: 280px; /* Ancho del sidebar */
    height: 100%; /* Altura total de la pantalla */
    overflow-y: auto; /* Permite scroll dentro del sidebar si es necesario */
    z-index: 1040; /* Mayor que el z-index del navbar para superponerse sobre él */
  }
  #resultados-busqueda li {
    cursor: pointer;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

#resultados-busqueda li:hover {
    background-color: #f8f8f8;
}

/* Estilos para la tabla de productos seleccionados */
#tabla-seleccionados thead {
    background-color: #3f51b5;
    color: white;
}

#tabla-seleccionados tbody tr:nth-child(odd) {
    background-color: #f2f2f2;
}
#resultados-busqueda {
    max-height: 300px;
    overflow-y: auto;
}

.table {
    margin-bottom: 20px;
}
</style>


</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4 pt-md-4">
            <?php 
            if (isset($_GET['page'])) {
                switch ($_GET['page']) {
                    case 'products':
                        include 'product_stock.php';
                        break;
                    /* case 'result':
                        include 'result.php';
                        break; */
                    case 'configuracion':
                        include 'configuracion.php';
                        break;
                    case 'categorias':
                        include 'categorias.php';
                        break;
                    case 'cuadratura':
                        include 'cuadratura_caja.php';
                        break;
                    case 'suscripcion':
                        include 'suscripcion.php';
                        break;
                    default:
                        include 'buscar_productos.php';
                        break;
                }
            } else {
                // Si no hay ninguna página especificada, incluir buscar_productos.php por defecto
                include 'buscar_productos.php';
            }
            ?>
        </div>
    </div>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    var checkboxUsarTotal = document.getElementById('usarTotal');
    var inputMontoPagar = document.getElementById('montopagar');
    var divTotalConIva = document.getElementById('totalConIva');

    checkboxUsarTotal.addEventListener('change', function() {
        if (this.checked) {
            // Extraer el valor numérico del total con IVA y actualizar el input de monto a pagar
            var totalConIva = divTotalConIva.textContent.replace('Total con IVA: $', '');
            inputMontoPagar.value = totalConIva;
            inputMontoPagar.disabled = true; // Bloquear el input para que no se pueda editar
        } else {
            // Desbloquear el input y limpiar su valor
            inputMontoPagar.disabled = false;
            inputMontoPagar.value = '';
        }

        // Llamar a actualizarDiferencia para recalcular la diferencia
        actualizarDiferencia();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var inputBusqueda = document.getElementById('buscar');
    var resultadosDiv = document.getElementById('resultados-busqueda');
    var tablaSeleccionados = document.getElementById('tabla-seleccionados').querySelector('tbody');
    var productosSeleccionados = {}; // Objeto para rastrear los productos seleccionados

    inputBusqueda.addEventListener('input', function() {
        var buscarTexto = inputBusqueda.value.trim();
        if (buscarTexto.length > 2) {
            fetch('buscar_productos.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'buscar=' + encodeURIComponent(buscarTexto)
            })
            .then(response => response.json())
            .then(data => {
                resultadosDiv.innerHTML = '';
                data.forEach(function(producto) {
                    if (!productosSeleccionados[producto.nombre]) { // Verifica si el producto no ha sido seleccionado
                        var li = document.createElement('li');
                        li.textContent = producto.nombre + " - $" + producto.precio + " - Stock: " + producto.stock + " - Categoría: " + producto.categoria;
                        li.addEventListener('click', function() {
                            agregarProductoSeleccionado(producto);
                        });
                        li.style.fontSize = '15px'; // Aplicar el font-size 15px
                        resultadosDiv.appendChild(li);
                    }
                });
            })
            .catch(error => console.error('Error:', error));
        } else {
            resultadosDiv.innerHTML = '';
        }
    });
    function calcularTotal() {
    var filas = tablaSeleccionados.rows;
    var total = 0;

    for (var i = 0; i < filas.length; i++) {
        var precio = parseFloat(filas[i].cells[1].textContent.replace('$', ''));
        var cantidad = parseInt(filas[i].cells[2].textContent);
        total += precio * cantidad;
    }

    var iva = total * 0.19; // Calcula el 19% de IVA del total
    var totalConIva = total + iva; // Suma el IVA al total

    // Muestra el total, el IVA y el total con IVA
    document.getElementById('totalPrecio').textContent = 'Total: $' + total.toFixed(0);
    document.getElementById('iva').textContent = 'IVA (19%): $' + iva.toFixed(0);
    document.getElementById('totalConIva').textContent = 'Total con IVA: $' + totalConIva.toFixed(0);

        // Actualizar el monto a pagar si el checkbox está marcado
        if (document.getElementById('usarTotal').checked) {
            document.getElementById('montopagar').value = totalConIva.toFixed(0);
    }

    // Calcular y mostrar la diferencia
    actualizarDiferencia();
}

function actualizarDiferencia() {
    var totalConIva = parseFloat(document.getElementById('totalConIva').textContent.replace('Total con IVA: $', ''));
    var montoPagadoCliente = parseFloat(document.getElementById('montopagar').value);
    
    // Calcular la diferencia solo si el montoPagadoCliente es válido y no menor que el total con IVA
    if (!isNaN(montoPagadoCliente) && montoPagadoCliente >= totalConIva) {
        var diferencia = montoPagadoCliente - totalConIva;
        document.getElementById('diferencia').value = diferencia.toFixed(0);
    } else {
        document.getElementById('diferencia').value = '0.00';
    }
}

// Asegúrate de llamar a actualizarDiferencia() cuando cambie el valor de montopagar
document.getElementById('montopagar').addEventListener('input', actualizarDiferencia);


    var inputMontoPagar = document.getElementById('montopagar');
    inputMontoPagar.addEventListener('input', actualizarDiferencia);


    function agregarProductoSeleccionado(producto) {
        if (productosSeleccionados[producto.nombre]) return; // Evita agregar el producto si ya está seleccionado

        var fila = tablaSeleccionados.insertRow();
        fila.insertCell().textContent = producto.nombre;
        fila.insertCell().textContent = "$" + producto.precio;
        var celdaCantidad = fila.insertCell();
        celdaCantidad.textContent = '1'; // Cantidad inicial
        fila.insertCell().textContent = producto.categoria;
        
        var btnEditar = document.createElement('button');
        btnEditar.textContent = 'Editar Cantidad';
        btnEditar.className = 'btn btn-primary';
        btnEditar.style.fontSize = '12px'; // Reducir tamaño de letra del botón
        btnEditar.dataset.producto = JSON.stringify(producto); // Almacenar el producto en el botón
        btnEditar.onclick = function() {
            editarCantidad(this);
        };
        fila.insertCell().appendChild(btnEditar);

        productosSeleccionados[producto.nombre] = true; // Marca el producto como seleccionado
        calcularTotal();

    }

    function editarCantidad(btn) {
        var producto = JSON.parse(btn.dataset.producto);
        var inputCantidad = document.getElementById('inputCantidad');
        
        inputCantidad.value = producto.cantidad || '1';
        inputCantidad.max = producto.stock; // Establecer el máximo según el stock

        document.getElementById('productoSeleccionadoId').value = btn.dataset.producto;
        $('#modalCantidad').modal('show');
    }

    window.actualizarCantidad = function() {
        var cantidad = document.getElementById('inputCantidad').value;
        var producto = JSON.parse(document.getElementById('productoSeleccionadoId').value);
        
        // Verificar que la cantidad no exceda el stock
        if (cantidad > producto.stock) {
            alert("La cantidad no puede exceder el stock disponible.");
            return;
        }

        producto.cantidad = cantidad;
        
        var filas = tablaSeleccionados.rows;
        for (var i = 0; i < filas.length; i++) {
            var btn = filas[i].cells[4].firstChild;
            if (btn.dataset.producto === document.getElementById('productoSeleccionadoId').value) {
                filas[i].cells[2].textContent = cantidad; // Actualizar cantidad
                break;
            }
        }
        $('#modalCantidad').modal('hide');
        calcularTotal();

    };
});

document.getElementById('registrarPago').addEventListener('click', function() {
    var medioPago = document.getElementById('medioPago').value;
    var medioPagoSelect = document.getElementById('medioPago');
    var medioPagoNombre = medioPagoSelect.options[medioPagoSelect.selectedIndex].text;
    var total = parseFloat(document.getElementById('totalPrecio').textContent.replace('Total: $', ''));
    var montoPagadoCliente = parseFloat(document.getElementById('montopagar').value);
    var idUsuario = '<?php echo $_SESSION['id']; ?>';
    var iva = parseFloat(document.getElementById('iva').textContent.replace('IVA (19%): $', ''));
    var totalConIva = parseFloat(document.getElementById('totalConIva').textContent.replace('Total con IVA: $', ''));

    if (montoPagadoCliente < total) {
        // Si el monto pagado es menor que el total, mostrar un mensaje y no proceder
        alert("El monto a pagar debe ser igual o mayor al total de la compra.");
        return;
    }

    // Crear un array con la información de los productos vendidos
    var productosVendidos = [];
    var filas = document.getElementById('tabla-seleccionados').querySelector('tbody').rows;
    for (var i = 0; i < filas.length; i++) {
        productosVendidos.push({
            nombre: filas[i].cells[0].textContent,
            precio: filas[i].cells[1].textContent.replace('$', ''),
            cantidadVendida: parseInt(filas[i].cells[2].textContent)
        });
    }

    // Agregar productosVendidos al cuerpo de la solicitud
    var formData = new FormData();
    var diferencia = document.getElementById('diferencia').value;
    formData.append('iva', iva);
    formData.append('totalConIva', totalConIva);
    formData.append('medioPago', medioPago);
    formData.append('total', total);
    formData.append('diferencia', total - montoPagadoCliente);
    formData.append('montoPagadoCliente', montoPagadoCliente);
    formData.append('idUsuario', idUsuario);
    formData.append('productosVendidos', JSON.stringify(productosVendidos));

    // Realiza la solicitud AJAX
    fetch('registrar_pago.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Manejar la respuesta
        alert("Pago registrado con éxito.");
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Error al registrar el pago.");
    });
    window.open('generar_boleta.php?medioPago=' + encodeURIComponent(medioPagoNombre) + '&total=' + total + '&diferencia=' + encodeURIComponent(diferencia) + '&productosVendidos=' + encodeURIComponent(JSON.stringify(productosVendidos)), '_blank');

});


</script>


</body>
</html>
