<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);

if (!$conexion) {
    echo "<script>alert('Error de conexión: " . mysqli_connect_error() . "');</script>";
    die("Error de conexión con la base de datos.");
}

$alert = '';

// 2. ACTUALIZAR LOS DATOS (Cuando el usuario presiona el botón)
if (!empty($_POST)) {
    if (empty($_POST['nombre_categoria']) || empty($_POST['id'])) {
        $alert = '<p class="msg_error">El nombre de la categoría es obligatorio.</p>';
    } else {
        $id_categoria = intval($_POST['id']);
        $nombre_categoria = mysqli_real_escape_string($conexion, $_POST['nombre_categoria']);

        // Verificar que el nuevo nombre no exista ya en OTRA categoría diferente
        $query_check = mysqli_query($conexion, "SELECT * FROM categorias WHERE nombre_categoria = '$nombre_categoria' AND id_categoria != $id_categoria");
        $result_check = mysqli_fetch_array($query_check);

        if ($result_check > 0) {
            $alert = '<p class="msg_error">Ese nombre de categoría ya está en uso.</p>';
        } else {
            // Actualizar registro
            $query_update = mysqli_query($conexion, "UPDATE categorias SET nombre_categoria = '$nombre_categoria' WHERE id_categoria = $id_categoria");

            if ($query_update) {
                $alert = '<p class="msg_save">¡Categoría actualizada exitosamente!</p>';
            } else {
                $alert = '<p class="msg_error">Error de MySQL: No se pudo actualizar la categoría.</p>';
            }
        }
    }
}

// 3. CARGAR LOS DATOS ACTUALES (Para mostrarlos en los inputs al abrir la página)
if (empty($_GET['id'])) {
    header('Location: lista_categoria.php');
    exit();
}

$id_cat = intval($_GET['id']);
$query_data = mysqli_query($conexion, "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_categoria = $id_cat");
$result_data = mysqli_num_rows($query_data);

if ($result_data == 0) {
    header('Location: lista_categoria.php');
    exit();
} else {
    $data = mysqli_fetch_array($query_data);
    $cat_nombre = $data['nombre_categoria'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Actualizar Categoría</title>
</head>
<body>
    <?php include "include/header.php"; ?>
    
    <section id="container">
        <div class="form_register">
            <h1><i class="fas fa-edit"></i> Actualizar Categoría</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">
                <input type="hidden" name="id" value="<?php echo $id_cat; ?>">

                <label for="nombre_categoria">Nombre de la Categoría</label>
                <input type="text" name="nombre_categoria" id="nombre_categoria" placeholder="Nombre de la categoría" value="<?php echo $cat_nombre; ?>" autocomplete="off">

                <input type="submit" value="Actualizar Categoría" class="btn_save">
                <br>
                <a href="lista_categoria.php" style="display: block; text-align: center; margin-top: 15px; color: #555; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver a la lista</a>
            </form>
        </div>
    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>