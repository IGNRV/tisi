<?php
// historial.php
require_once 'db.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

if (isset($_SESSION['id'])) {
    $id_usuario = $_SESSION['id'];

    $query = "SELECT descripcion, date_created FROM historial_cambios WHERE id_usuario = ? ORDER BY date_created DESC";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
        exit;
    }
} else {
    echo "Usuario no autenticado.";
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Historial de Cambios</title>
    <!-- Incluir CSS de Bootstrap y otros estilos si es necesario -->
</head>
<body>

<div class="container">
    <h2>Historial de Cambios</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                        <td>
                            <?php 
                                // Restar 3 horas a la fecha y hora
                                echo date('Y-m-d H:i:s', strtotime($row['date_created'] . ' - 3 hours'));
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tableBody = document.querySelector('.table tbody');
    var rowsPerPage = 5;  // Define la cantidad de filas por página
    var rows = tableBody.querySelectorAll('tr');
    var pagesCount = Math.ceil(rows.length / rowsPerPage);

    function displayPage(page) {
        var start = (page - 1) * rowsPerPage;
        var end = start + rowsPerPage;

        rows.forEach(row => row.style.display = 'none');
        for (var i = start; i < end && i < rows.length; i++) {
            rows[i].style.display = '';
        }
    }

    // Crear paginación
    var pagination = document.createElement('div');
    pagination.className = 'pagination';

    for (var i = 1; i <= pagesCount; i++) {
        var pageLink = document.createElement('a');
        pageLink.innerText = i;
        pageLink.href = '#';
        pageLink.dataset.page = i;
        pageLink.addEventListener('click', function(e) {
            e.preventDefault();
            displayPage(this.dataset.page);
        });
        pagination.appendChild(pageLink);
    }

    // Añadir paginación después de la tabla
    tableBody.closest('.table-responsive').after(pagination);

    // Mostrar la primera página inicialmente
    displayPage(1);
});
</script>


<?php $conn->close(); ?>
