<?php
// 2. CONEXIÓN A LA BASE DE DATOS
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion, "utf8"); // <-- Agrega esta línea

if (!$conexion) {
    echo "<script>alert('Error de conexión: " . mysqli_connect_error() . "');</script>";
    die("Error de conexión con la base de datos.");
}

$alert = '';

// 3. PROCESAR ACTUALIZACIÓN (POST)
if (!empty($_POST)) {
    if (empty($_POST['nombre_producto']) || empty($_POST['precio_venta']) || empty($_POST['id_categoria']) || empty($_POST['id'])) {
        $alert = '<p class="msg_error">Todos los campos son obligatorios.</p>';
    } else {
        $id_producto     = intval($_POST['id']);
        $nombre_producto = mysqli_real_escape_string($conexion, $_POST['nombre_producto']);
        $precio_venta    = mysqli_real_escape_string($conexion, $_POST['precio_venta']);
        $id_categoria    = intval($_POST['id_categoria']);

        // Verificar que el nombre no pertenezca a OTRO producto ya registrado
        $query_check = mysqli_query($conexion, "SELECT * FROM productos WHERE nombre_producto = '$nombre_producto' AND id_producto != $id_producto");
        
        // CORREGIDO: Uso de mysqli_num_rows para evitar falsos positivos o advertencias en PHP moderno
        if (mysqli_num_rows($query_check) > 0) {
            $alert = '<p class="msg_error">El nombre de este producto ya está en uso por otro registro.</p>';
        } else {
            $query_update = mysqli_query($conexion, "UPDATE productos 
                                                     SET nombre_producto = '$nombre_producto', 
                                                         precio_venta = '$precio_venta', 
                                                         id_categoria = $id_categoria
                                                     WHERE id_producto = $id_producto");

            if ($query_update) {
                // REDIRECCIÓN AUTOMÁTICA: Envía una variable 'msg' con valor 'success' hacia la lista
                header("Location: lista_dproducto.php?msg=success");
                exit();
            } else {
                $alert = '<p class="msg_error">Error al actualizar el producto: ' . mysqli_error($conexion) . '</p>';
            }
        }
    }
}

// 4. CARGAR DATOS ACTUALES DEL PRODUCTO (GET)
if (empty($_GET['id'])) {
    header('Location: lista_dproducto.php');
    exit();
}

$id_prod = intval($_GET['id']);
$query_data = mysqli_query($conexion, "SELECT id_producto, nombre_producto, precio_venta, id_categoria FROM productos WHERE id_producto = $id_prod");
$result_data = mysqli_num_rows($query_data);

if ($result_data == 0) {
    header('Location: lista_dproducto.php');
    exit();
} else {
    $data_prod = mysqli_fetch_array($query_data);
    $prod_nombre = $data_prod['nombre_producto'];
    $prod_precio = $data_prod['precio_venta'];
    $prod_cat    = $data_prod['id_categoria'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Actualizar Producto</title>
</head>
<body>
    <?php include "include/header.php"; ?>
    
    <section id="container">
        <div class="form_register">
            <h1><i class="fas fa-edit"></i> Actualizar Producto</h1>
            <hr>
            <div class="alert"><?php echo $alert; ?></div>

            <form action="" method="post">
                <input type="hidden" name="id" value="<?php echo $id_prod; ?>">

                <label for="nombre_producto">Nombre del Producto</label>
                <input type="text" name="nombre_producto" id="nombre_producto" placeholder="Nombre del producto" value="<?php echo $prod_nombre; ?>" autocomplete="off">

                <label for="precio_venta">Precio de Venta</label>
                <input type="number" step="0.01" name="precio_venta" id="precio_venta" placeholder="0.00" value="<?php echo $prod_precio; ?>">

                <label for="id_categoria">Categoría</label>
                <select name="id_categoria" id="id_categoria">
                    <?php
                    $query_cat = mysqli_query($conexion, "SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC");
                    if ($query_cat) {
                        while ($cat = mysqli_fetch_array($query_cat)) {
                            $selected = ($cat['id_categoria'] == $prod_cat) ? 'selected' : '';
                            echo '<option value="'.$cat['id_categoria'].'" '.$selected.'>'.$cat['nombre_categoria'].'</option>';
                        }
                    }
                    ?>
                </select>

                <input type="submit" value="Actualizar Producto" class="btn_save">
                <a href="lista_dproducto.php" style="display: block; text-align: center; margin-top: 15px; color: #555; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver a la lista</a>
            </form>
        </div>
    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>