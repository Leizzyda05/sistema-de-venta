<?php
// 1. CONEXIÓN A LA BASE DE DATOS (Configuración estándar de phpMyAdmin / XAMPP)
$servername = 'localhost';
$username = 'root';
$password = ''; // Por defecto en phpMyAdmin viene vacío
$database = 'sistema_de_venta'; // Nombre exacto de tu Base de Datos

$conexion = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion, "utf8"); // <-- Agrega esta línea

// Alerta de conexión mediante JavaScript
if (!$conexion) {
    echo "<script>alert('Error crítico: No se pudo conectar a MySQL en phpMyAdmin. " . mysqli_connect_error() . "');</script>";
    die("Error de conexión con la base de datos.");
}

// 2. PROCESAMIENTO DEL FORMULARIO ENVIADO POR POST
$alert = ''; // Aquí guardaremos los mensajes que se verán en pantalla

if (!empty($_POST)) {
    // Validar únicamente que el nombre de la categoría no esté vacío
    if (empty($_POST['nombre_categoria'])) {
        $alert = '<p class="msg_error">El campo Nombre de la Categoría es obligatorio.</p>';
    } else {
        // Almacenar dato en variable y limpiarlo para evitar errores de sintaxis o inyecciones SQL
        $nombre_categoria = mysqli_real_escape_string($conexion, $_POST['nombre_categoria']);

        // Comprobar si la categoría ya existe en phpMyAdmin para no duplicarla
        $query = mysqli_query($conexion, "SELECT * FROM categorias WHERE nombre_categoria = '$nombre_categoria'");
        
        // Contamos directamente si hay registros duplicados
        if (mysqli_num_rows($query) > 0) {
            $alert = '<p class="msg_error">La categoría ya se encuentra registrada.</p>';
        } else {
            // Se usa la variable correcta $nombre_categoria
            $query_insert = mysqli_query($conexion, "INSERT INTO categorias(nombre_categoria) VALUES('$nombre_categoria')");
            
            if ($query_insert) {
                // REDIRECCIÓN AUTOMÁTICA: Viaja directo a la lista enviando el aviso por la URL
                header("Location: lista_categoria.php?msg=register_success");
                exit();
            } else {
                $alert = '<p class="msg_error">Error de MySQL: No se pudo guardar la categoría.</p>';
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
    <title>Registro de Categoría</title>
</head>
<body>
    <?php include "include/header.php"; ?>
    
    <section id="container">
        <div class="form_register">
            <h1>Registro de Categoría</h1>
            <hr>
            
            <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">
                <label for="nombre_categoria">Nombre de la Categoría</label>
                <input type="text" name="nombre_categoria" id="nombre_categoria" placeholder="Ej. Charcutería" autocomplete="off">

                <input type="submit" value="Guardar Categoría" class="btn_save">
            </form>
        </div>
    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>