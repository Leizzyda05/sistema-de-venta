<?php
// 1. Conexión directa a la base de datos para evitar el error del include
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);

if (!$conexion) {
    die("Error de conexión con la base de datos: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");


// 2. PROCESAMIENTO DEL FORMULARIO ENVIADO POR POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['precio_dolar'])) {
    
    // Limpiamos el valor usando la conexión ya establecida arriba
    $nuevo_precio = mysqli_real_escape_string($conexion, $_POST['precio_dolar']);
    
    // Actualizamos el valor apuntando directamente al id = 1
    $query_update = mysqli_query($conexion, "UPDATE configuracion SET valor = '$nuevo_precio' WHERE id = 1");
    
    if ($query_update) {
        // Redirecciona de vuelta al index mostrando el Toast de éxito
        header("Location: index.php?msg=rate_success");
        exit();
    } else {
        echo "Error al actualizar la tasa: " . mysqli_error($conexion);
    }
} else {
    // Si intentan entrar al archivo directamente desde la URL, los regresa al index
    header("Location: index.php");
    exit();
}
?>