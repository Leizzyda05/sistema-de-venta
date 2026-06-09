<?php
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion, "utf8"); // <-- Agrega esta línea

// Alerta de conexión segura
if (!$conexion) {
    echo "<script>alert('Error de conexión: " . mysqli_connect_error() . "');</script>";
    die("Error de conexión con la base de datos.");
}

// PROCESAMIENTO DEL FORMULARIO ENVIADO POR POST
$alert = ''; 

if (!empty($_POST)) {
    // Validar únicamente que los campos solicitados no estén vacíos
    if (empty($_POST['nombre_producto']) || empty($_POST['precio_venta']) || empty($_POST['id_categoria'])) {
        $alert = '<p class="msg_error">Todos los campos son obligatorios.</p>';
    } else {
        $nombre_producto = mysqli_real_escape_string($conexion, $_POST['nombre_producto']);
        // Reemplazamos la coma por punto en caso de que el usuario escriba con coma
        $precio_venta    = str_replace(',', '.', $_POST['precio_venta']);
        $precio_venta    = mysqli_real_escape_string($conexion, $precio_venta);
        $id_categoria    = mysqli_real_escape_string($conexion, $_POST['id_categoria']);
        
        // Comprobar si el producto ya existe en la base de datos
        $query = mysqli_query($conexion, "SELECT * FROM productos WHERE nombre_producto = '$nombre_producto'");
        $result = mysqli_fetch_array($query);

        if ($result > 0) {
            $alert = '<p class="msg_error">El producto ya se encuentra registrado.</p>';
        } else {
            // Se inserta omitiendo la columna idusuario
            $query_insert = mysqli_query($conexion, "INSERT INTO productos(nombre_producto, precio_venta, id_categoria) 
                                                     VALUES('$nombre_producto', '$precio_venta', '$id_categoria')");
            
            if ($query_insert) {
                $alert = '<p class="msg_save">¡Producto guardado exitosamente!</p>';
            } else {
                $alert = '<p class="msg_error">Error al guardar el producto: ' . mysqli_error($conexion) . '</p>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Registro de Producto ($)</title>
</head>
<body>
    <?php include "include/header.php"; ?>
    
    <section id="container">
        <div class="form_register">
            <h1>Registro de Producto</h1>
            <hr>
            
            <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">
                <label for="nombre_producto">Nombre del Producto</label>
                <input type="text" name="nombre_producto" id="nombre_producto" placeholder="Nombre del producto" autocomplete="off">

                <label for="precio_venta">Precio de Venta (USD $)</label>
                <input type="number" step="0.01" min="0" name="precio_venta" id="precio_venta" placeholder="Ej. 10.50">

                <label for="id_categoria">Categoría</label>
                <select name="id_categoria" id="id_categoria">
                    <option value="">Seleccione una Categoría</option>
                    <?php
                    $query_cat = mysqli_query($conexion, "SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC");
                    
                    if ($query_cat) {
                        while ($cat = mysqli_fetch_array($query_cat)) {
                            echo '<option value="'.$cat['id_categoria'].'">'.$cat['nombre_categoria'].'</option>';
                        }
                    } else {
                        echo '<option value="" disabled>Error al cargar categorías en MySQL</option>';
                    }
                    ?>
                </select>

                <input type="submit" value="Guardar Producto" class="btn_save">
            </form>
        </div>
    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>