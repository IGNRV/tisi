<?php
function setActive($pageName) {
  $current_page = $_GET['page'] ?? ''; // El operador ?? se usa para PHP 7.0+, para versiones anteriores usa `isset`
  return $current_page == $pageName ? 'active' : '';
}
?>
<div class="sidebar d-flex flex-column flex-shrink-0 p-3 bg-white" style="width: 220px; height: 100vh;">
<div style="display: flex; justify-content: center;">
  <img src="https://trackitsellit.oralisisdataservice.cl/images/logo.png" style="margin-bottom: 20px; width: 100px;" alt="Logo de la empresa">
</div>
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
    <a href="welcome.php" class="nav-link text-black <?php echo setActive(''); ?>" style="color: black; font-size: 16px; margin-left: 18px">
  <img src="https://trackitsellit.oralisisdataservice.cl/images/storefront_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Venta">Venta
</a>
    </li>
    <li class="nav-item">
      <a href="welcome.php?page=products" class="nav-link text-black <?php echo setActive('products'); ?>" style="color: black; font-size: 16px; margin-left: 18px">
      <img src="https://trackitsellit.oralisisdataservice.cl/images/inventory_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Productos">Productos
      </a>
    </li>
    <li class="nav-item">
      <a href="welcome.php?page=categorias" class="nav-link text-black <?php echo setActive('categorias'); ?>" style="color: black; font-size: 16px; margin-left: 18px">
      <img src="https://trackitsellit.oralisisdataservice.cl/images/category_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Categorias">Categorias
      </a>
    </li>
    <li class="nav-item"> 
      <a href="welcome.php?page=cuadratura" class="nav-link text-black <?php echo setActive('cuadratura'); ?>" style="color: black; font-size: 16px; margin-left: 18px">
      <img src="https://trackitsellit.oralisisdataservice.cl/images/payments_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Cuadratura de Cajas">Cuadratura de Caja
      </a>
    </li>
    <li class="nav-item" style="padding-left: 18px;"> <!-- Añade el padding aquí -->
      <a href="welcome.php?page=proveedores" class="nav-link text-black <?php echo setActive('proveedores'); ?>" style="color: black; font-size: 16px;">
        <img src="https://trackitsellit.oralisisdataservice.cl/images/local_shipping_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Proveedores">Proveedores
      </a>
    </li>
    <li class="nav-item">
      <a href="welcome.php?page=configuracion" class="nav-link text-black <?php echo setActive('configuracion'); ?>" style="color: black; font-size: 16px; margin-left: 18px">
        <img src="https://trackitsellit.oralisisdataservice.cl/images/manufacturing_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Cuadratura de Cajas">Configuracion
      </a>
    </li>
    <li class="nav-item">
      <a href="welcome.php?page=historial" class="nav-link text-black <?php echo setActive('historial'); ?>" style="color: black; font-size: 16px; margin-left: 18px">
        <img src="https://trackitsellit.oralisisdataservice.cl/images/schedule_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Cuadratura de Cajas">Historial
      </a>
    </li>
    <li class="nav-item">
      <a href="welcome.php?page=suscripcion" class="nav-link text-black <?php echo setActive('suscripcion'); ?>" style="color: black; font-size: 16px; margin-left: 18px">
        <img src="https://trackitsellit.oralisisdataservice.cl/images/event_repeat_FILL0_wght400_GRAD0_opsz24.svg" class="icono-venta" alt="Ícono Cuadratura de Cajas">Suscripción
      </a>
    </li>
  </ul>
</div>
<style>
  .nav-link.active {
    color: white !important; /* Asegura que el texto sea blanco para el enlace activo */
    background-color: #4e73df !important; /* Color de fondo para el enlace activo */
  }

  .nav-link.active .icono-venta {
    filter: invert(100%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(100%) contrast(100%);
    /* Este filtro cambia el color del icono a blanco. Ajusta si es necesario. */
  }

  /* Estilos para el estado hover que no afectan al enlace activo */
  .nav-link:not(.active):hover {
    background-color: #f8f9fa; /* Color de fondo al pasar el cursor */
    color: black; /* Color del texto al pasar el cursor */
  }

  .nav-link:not(.active):hover .icono-venta {
    filter: brightness(90%); /* Opcional: cambia el brillo del ícono al pasar el cursor */
  }


/* Estilos existentes... */
.icono-venta {
  height: 1em;
  width: auto;
  margin-right: 5px;
  vertical-align: middle;
}

/* Agregar estilo para la paginación */
.pagination a {
  margin: 0 10px;
  text-decoration: none;
  color: #4e73df;
}

/* Otras reglas de estilo que ya tengas... */

</style>
