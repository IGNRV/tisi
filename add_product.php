<?php
require_once 'db.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoge los datos del formulario
    $codigo_producto = $_POST['codigo_producto']; // Nuevo campo para el código del producto
    $nombre_px = $_POST['nombre_px'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $tipo_cantidad = $_POST['tipo_cantidad'];
    $cantidad = $_POST['cantidad'];
    $id_usuario = $_POST['id_usuario']; // Asegúrate de validar y limpiar este valor
    $id_proveedor = $_POST['id_proveedor']; // Captura el id_proveedor seleccionado
    $impuesto_adicional = isset($_POST['impuesto_adicional']) ? $_POST['impuesto_adicional'] : '0';



    // Prepara la consulta para insertar el nuevo producto
    if ($tipo_cantidad == 'kilogramos') {
        $query = "INSERT INTO productos (codigo_producto, nombre_px, precio, id_categoria, kilogramos, id_usuario, id_proveedor, impuesto_adicional) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    } else {
        $query = "INSERT INTO productos (codigo_producto, nombre_px, precio, id_categoria, stock, id_usuario, id_proveedor, impuesto_adicional) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    }

    if ($stmt = $conn->prepare($query)) {
        if ($tipo_cantidad == 'kilogramos') {
            $stmt->bind_param("ssdiisii", $codigo_producto, $nombre_px, $precio, $id_categoria, $cantidad, $id_usuario, $id_proveedor, $impuesto_adicional);
        } else {
            $stmt->bind_param("ssdiisii", $codigo_producto, $nombre_px, $precio, $id_categoria, $cantidad, $id_usuario, $id_proveedor, $impuesto_adicional);
        }

        if ($stmt->execute()) {
            // Obtén el último ID insertado, que corresponde al producto recién añadido
            $id_producto_insertado = $conn->insert_id;

            // Prepara la inserción en la tabla historial_cambios
            $query_historial = "INSERT INTO historial_cambios (descripcion, date_created, id_usuario, id_producto) VALUES (?, NOW(), ?, ?)";
            if ($stmt_historial = $conn->prepare($query_historial)) {
                $descripcion = 'Se agrega un nuevo producto';
                $stmt_historial->bind_param("sii", $descripcion, $id_usuario, $id_producto_insertado);

                if (!$stmt_historial->execute()) {
                    // Manejar error al insertar en la tabla historial_cambios
                    echo "Error al registrar en historial de cambios: " . $conn->error;
                }
                $stmt_historial->close();
            } else {
                echo "Error al preparar la consulta para historial de cambios: " . $conn->error;
            }

            header("Location: https://trackitsellit.oralisisdataservice.cl/welcome.php?page=products&add_success=true");
            exit;
        } else {
            echo "Error al agregar el producto: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>