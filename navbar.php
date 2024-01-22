<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-white">
  <a class="navbar-brand" href="#">Mi Aplicación</a>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ml-auto">
      <!-- Reemplazar con ícono de cierre de sesión -->
      <span class="navbar-text mr-3" style="color: black;">
    ¡Hola, <?php echo htmlspecialchars($nombreUsuario); ?>!
  </span>
      <li class="nav-item active">
        <a class="nav-link" href="logout.php">
          <img src="https://trackitsellit.oralisisdataservice.cl/images/logout.svg" alt="Cerrar Sesión" style="height: 30px;">
        </a>
      </li>
    </ul>
  </div>
</nav>
