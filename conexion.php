<?php
$servername = "localhost"; // Dirección del servidor MySQL
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña de la base de datos
$database = "sistema_de_venta"; // Nombre de la base de datos

mysqli_report(MYSQLI_REPORT_OFF);

// Crear conexión
$conection = @mysqli_connect($servername,$username,$password,$database);

// Verificar conexión
    if (!$conection) {
       echo "Error en la conexión";
    }

?>