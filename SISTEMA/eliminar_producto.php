<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);

if (!$conexion) {
    die("Error crítico de conexión.");
}

// 2. PROCESAR LA ELIMINACIÓN
if (!empty($_GET['id'])) {
    $id_producto = intval($_GET['id']);

    // Ejecutar sentencia DELETE
    $query_delete = mysqli_query($conexion, "DELETE FROM productos WHERE id_producto = $id_producto");

    if ($query_delete) {
        echo "<script>
                alert('¡Producto eliminado correctamente del inventario!');
                window.location.href = 'lista_dproducto.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error de MySQL: No se pudo eliminar el producto en este momento.');
                window.location.href = 'lista_dproducto.php';
              </script>";
        exit();
    }
} else {
    // Protección por si entran al archivo directamente sin ID
    header('Location: lista_dproducto.php');
    exit();
}
?>