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
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <!-- Verifica si el parámetro 'page' se ha pasado a través de GET y si es igual a 'products' -->
    <?php if (isset($_GET['page']) && $_GET['page'] == 'products') {
        include 'product_stock.php'; 
    } ?>
</div>
  </div>
</div>

<!-- Incluir JS de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
