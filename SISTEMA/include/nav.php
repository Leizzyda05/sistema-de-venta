<?php
// 1. Aseguramos que la sesión esté activa y nos conectamos a la BD para obtener la tasa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion_nav = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion_nav, "utf8");

$tasa_bcv = "0.00";

if ($conexion_nav) {
    $query_nav = mysqli_query($conexion_nav, "SELECT valor FROM configuracion WHERE id = 1");
    if ($query_nav && mysqli_num_rows($query_nav) > 0) {
        $data_nav = mysqli_fetch_assoc($query_nav);
        $tasa_bcv = $data_nav['valor'];
    }
    mysqli_close($conexion_nav); // Cerramos esta conexión rápida
}
?>

<nav>
    <ul>
        <li class="principal">
            <a href="index.php" class="link_inicio_top"><i class="fas fa-home"></i> Inicio</a>
        </li>

        <li class="principal">
            <a href="#">Categorías</a>
            <ul>
                <li><a href="registro_categoria.php"><i class="fas fa-plus"></i>Nueva Categoría</a></li>
                <li><a href="lista_categoria.php"><i class="fas fa-plus"></i>Lista de Categorías</a></li>
            </ul>
        </li>

        <li class="principal">
            <a href="#"><i class="fas fa-cubes"></i>Productos</a>
            <ul>
                <li><a href="registro_dproducto.php"><i class="fas fa-plus"></i>Nuevo Producto</a></li>
                <li><a href="lista_dproducto.php"><i class="fas fa-cubes"></i>Lista de Productos</a></li>
            </ul>
        </li>

        <li style="margin-left: 1000px;; padding: 29px 20px; font-size: 14px; font-weight: bold; color: #ffffff; font-family: 'Arial', sans-serif; list-style: none;">
            <i class="fas fa-dollar-sign" style="color: #58d68d;"></i> Tasa USD: 
            <span style="background: rgba(255, 255, 255, 0.15); padding: 5px 15px; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.3); color: #ffffff; margin-left: 5px;">
                <?php echo htmlspecialchars($tasa_bcv); ?> Bs.
            </span>
        </li>
    </ul>
</nav>