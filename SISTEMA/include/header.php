<?php 

    session_start();    
    if (empty($_SESSION['active'])){ //si no existe la variable sesion {
        header('location: ../');
        exit(); //hace esto
    }
?>

<header>
        <div class="header">
            
            <h1>Sistema de Venta</h1>
            <div class="optionsBar">
                <p>Coro, <?php echo fechaC(); ?></p>
                <span>|</span>
                <span class="user"> <?php echo $_SESSION['user']; ?></span>
                <img class="photouser" src="img/cuenta.png" alt="Usuario" title="Usuario">
                <a href="salir.php"><img class="close" src="img/fuerza (1).png" alt="Salir del sistema" title="Salir"></a>
            </div>
        </div>
        <?php include "nav.php"; ?>
    
</header>