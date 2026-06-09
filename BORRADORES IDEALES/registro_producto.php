<?php
    session_start();

    // Control de acceso para los roles permitidos
    if($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2)
    {
        header("location: ./");
        exit(); // Añadido por seguridad para detener la ejecución
    }

    include "conexion.php";

    if(!empty($_POST))
    {
        $alert='';
        
        // Se validan los campos obligatorios del formulario de productos
        if(empty($_POST['nombre_producto']) || empty($_POST['precio_venta']) || empty($_POST['existencia']) || empty($_POST['id_categoria']))
        {
            $alert='<p class="msg_error">Todos los campos son obligatorios.</p>';
        }else{

            // Se capturan los datos enviados por el formulario post
            $nombre_producto = $_POST['nombre_producto'];
            $precio_venta    = $_POST['precio_venta'];
            $existencia      = $_POST['existencia'];
            $id_categoria    = $_POST['id_categoria'];
            $idusuario      = $_POST['idUser']; // ID del usuario activo en la sesión

            // Consulta relacionada directamente con la tabla productos de tu sistema
            $query_insert = mysqli_query($conection, "INSERT INTO productos(nombre_producto, precio_venta, existencia, id_categoria, idusuario)
                                                     VALUES('$nombre_producto', '$precio_venta', '$existencia', '$id_categoria', '$idusuario')");

            if($query_insert){
                $alert='<p class="msg_save">Producto guardado correctamente.</p>';
            }else{
                $alert='<p class="msg_error">Error al guardar el Producto.</p>';
            }
        }
    }
    mysqli_close($conection);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Registro Producto</title>
</head>
<body>
    <?php include "include/header.php"; ?>
    <section id="container">
        
        <div class="form_register">
            <h1><i class="far fa-cubes"></i> Registro Producto</h1>
            <hr>
            <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>
            
            <form action="" method="post" enctype="multipart/form-data">

                <!--<label for="categoria">Categoria</label>
                <input type="text" name="direccion" id="direccion" placeholder="Dirección completa">-->



                <label for="producto">Producto</label>
                <input type="text" name="producto" id="producto" placeholder="Nombre del producto">
                <label for="precio">Precio</label>
                <input type="number" name="precio" id="precio" placeholder="Precio del producto">
                <label for="cantidad">Cantidad</label>
                <input type="number" name="cantidad" id="cantidad" placeholder="Cnatidad del producto">

                <!--<label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" placeholder="Dirección completa">-->

                <button type="submit" class="btn_save"><i class="far fa-save fa-lg"></i>Guardar Producto</button>
            </form>
        </div>

    </section>
             <?php include "include/footer.php"; ?>
</body>
</html>
