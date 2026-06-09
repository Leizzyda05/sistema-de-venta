<?php
// 1. CONEXIÓN Y LOGUEO (Mantén lo que ya tengas aquí arriba)
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 
$conexion = @mysqli_connect($servername, $username, $password, $database);

// 2. PROCESAR LA ELIMINACIÓN
if (!empty($_GET['id'])) {
    $id_producto = intval($_GET['id']);

    // Tu consulta para borrar el producto
    $query_delete = mysqli_query($conexion, "DELETE FROM productos WHERE id_producto = $id_producto");

    if ($query_delete) {
        // REEMPLAZA CUALQUIER 'echo alert(...)' POR ESTO:
        // Redirige de golpe enviando el estado "deleted" por la URL
        header("location: lista_dproducto.php?status=deleted");
        exit;
    } else {
        // Si hay un error, regresa con estado "error"
        header("location: lista_dproducto.php?status=error");
        exit;
    }
} else {
    header("location: lista_dproducto.php");
    exit;
}
?>