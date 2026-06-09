<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion, "utf8"); 

if (!$conexion) {
    die("Error crítico de conexión.");
}

// 2. PROCESAR LA ELIMINACIÓN
if (!empty($_GET['id'])) {
    $id_categoria = intval($_GET['id']);

    try {
        // Intentar ejecutar el borrado de la categoría elegida
        $query_delete = mysqli_query($conexion, "DELETE FROM categorias WHERE id_categoria = $id_categoria");

        // AQUÍ VA TU NUEVO CÓDIGO DE ÉXITO:
        if ($query_delete) {
            // Redirecciona directamente a la lista enviando el estado 'deleted'
            header("location: lista_categoria.php?status=deleted");
            exit();
        } else {
            echo "Error al eliminar la categoría";
        }

    } catch (mysqli_sql_exception $e) {
        // MEJORADO: Si tiene productos asignados, redirige enviando un estado de error 'error_has_products'
        header("location: lista_categoria.php?status=error_has_products");
        exit();
    }
} else {
    // Si entran al archivo sin un ID válido, se les expulsa a la lista de forma limpia
    header('Location: lista_categoria.php');
    exit();
}
?>