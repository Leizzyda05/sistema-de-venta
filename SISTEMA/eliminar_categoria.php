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
    $id_categoria = intval($_GET['id']);

    try {
        // Intentar ejecutar el borrado de la categoría elegida
        $query_delete = mysqli_query($conexion, "DELETE FROM categorias WHERE id_categoria = $id_categoria");

        // Si se ejecuta sin lanzar excepciones, significa que no tenía productos asociados
        echo "<script>
                alert('¡Categoría eliminada correctamente!');
                window.location.href = 'lista_categoria.php';
              </script>";
        exit();

    } catch (mysqli_sql_exception $e) {
        // Si MySQL frena el borrado por la clave foránea, cae directamente aquí
        echo "<script>
                alert('No se puede eliminar esta categoría porque contiene productos asignados.');
                window.location.href = 'lista_categoria.php';
              </script>";
        exit();
    }
} else {
    // Si entran al archivo sin un ID válido, se les expulsa a la lista
    header('Location: lista_categoria.php');
    exit();
}
?>