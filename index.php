<?php

$alert = '';
session_start();    
if (!empty($_SESSION['active'])) 
{
    header('location: sistema/');
}else{
            
    if (!empty($_POST))
    {
        //if (isset($_POST['ingresar'])) {//
        if (empty($_POST['usuario']) || empty($_POST['clave']))
            {
                $alert = 'Ingrese su usuario y su clave';
            }else{

                require_once "conexion.php";

                //Aquí guardas lo que escribió el usuario
                $user = mysqli_real_escape_string($conection,$_POST['usuario']); 
                $pass = md5(mysqli_real_escape_string($conection,$_POST['clave']));

                $query = mysqli_query($conection, "SELECT * FROM usuarios WHERE usuario = '$user' AND clave = '$pass'");
                $result = mysqli_num_rows($query);

                if($result > 0)
                {
                    $data = mysqli_fetch_array($query);
                    $_SESSION['active'] = true;
                    $_SESSION['idUser'] = $data['idusuario'];
                    $_SESSION['nombre'] = $data['nombre'];
                    $_SESSION['email']  = $data['email'];
                    $_SESSION['user']   = $data['usuario'];
                    $_SESSION['rol']    = $data['rol'];

                header('location: sistema/');
                
            }else{
                $alert = 'El usuario o la clave son incorrectos';
                session_destroy();
                
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Sistema de Venta</title>
    <link rel="stylesheet" type="text/css" href="CSS/style.css">
</head>
<body>
    <section id="container">

        <form action="" method="post">
        <!--<form action="tu_archivo_login.php" method="POST">  -->

            <h3>Iniciar Sesión</h3>
            <img src="img/iniciar-sesion.png" alt="Login">

            <input type="text" name="usuario" placeholder="Usuario">
            <input type="password" name="clave" placeholder="Contraseña">
            <div class="alert"><?php echo isset($alert) ? $alert:'' ?></div> 
            <!-- Mensaje (dentro de <p></p>) -->

            <!--<button type="submit" name="ingresar">INGRESAR</button>-->
            <!--<input type="submit" value="INGRESAR">-->
            <input type="submit" name="ingresar" value="INGRESAR">
            

        </form>

    </section>
        <?php include "sistema/include/footer.php"; ?>

</body>
</html>