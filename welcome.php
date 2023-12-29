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
                    case 'categorias':
                        include 'categorias.php';
                        break;
                    // Agrega más casos según sea necesario
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
    var inputBusqueda = document.getElementById('buscar');
    var resultadosDiv = document.getElementById('resultados-busqueda');
    var tablaSeleccionados = document.getElementById('tabla-seleccionados').querySelector('tbody');

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
                    var li = document.createElement('li');
                    li.textContent = producto.nombre + " - $" + producto.precio + " - Stock: " + producto.stock + " - Categoría: " + producto.categoria;
                    li.addEventListener('click', function() {
                        agregarProductoSeleccionado(producto);
                    });
                    resultadosDiv.appendChild(li);
                });
            })
            .catch(error => console.error('Error:', error));
        } else {
            resultadosDiv.innerHTML = '';
        }
    });

    function agregarProductoSeleccionado(producto) {
        var fila = tablaSeleccionados.insertRow();
        fila.insertCell().textContent = producto.nombre;
        fila.insertCell().textContent = "$" + producto.precio;
        fila.insertCell().textContent = producto.stock;
        fila.insertCell().textContent = producto.categoria;
    }
});
</script>


</body>
</html>
