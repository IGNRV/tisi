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
?>



<!-- Formulario para buscar productos -->
<form action="welcome.php?page=buscar_productos" method="post">
    <div class="form-group">
        <label for="buscar">Buscar Producto</label>
        <input type="text" name="buscar" class="form-control" id="buscar">
        <ul id="resultados-busqueda"></ul>
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>
<table class="table" id="tabla-seleccionados">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Categoria</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>