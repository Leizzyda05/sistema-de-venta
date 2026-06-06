<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);

if (!$conexion) {
    die("Error de conexión con la base de datos.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Lista de Categorías</title>
    
    <style>
        /* Estilos para la ventana de alerta flotante */
        .custom_alert_floating {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #08a0f2; /* Verde éxito */
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            font-weight: bold;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .custom_alert_floating i {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <?php include "include/header.php"; ?>
    
    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $mensaje = "";
            if ($_GET['msg'] == 'success') {
                $mensaje = "¡Categoría actualizada exitosamente!";
            } elseif ($_GET['msg'] == 'register_success') {
                $mensaje = "¡Categoría guardada exitosamente!";
            }
        ?>
        
        <?php if (!empty($mensaje)): ?>
            <div id="alerta-flotante" class="custom_alert_floating">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const alerta = document.getElementById('alerta-flotante');
                    if (alerta) {
                        // Se mantiene visible por 3 segundos
                        setTimeout(() => {
                            alerta.style.opacity = '0';
                            alerta.style.transform = 'translateY(-20px)'; // Efecto visual de subida
                            
                            // Se elimina por completo del HTML tras la animación
                            setTimeout(() => {
                                alerta.remove();
                            }, 500); 
                        }, 3000);
                    }
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <section id="container">
        <div class="data_table"> 
            
            <div class="header_table">
                <h1><i class="fas fa-tags"></i> Lista de Categorías</h1>
                <a href="registro_categoria.php" class="btn_new">Crear Categoría</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Categoría</th>
                        <th>Fecha de Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // -------------------------------------------------------------
                    // CONSULTA PARA TRAER TODAS LAS CATEGORÍAS
                    // -------------------------------------------------------------
                    $query = mysqli_query($conexion, "SELECT id_categoria, nombre_categoria, date_add 
                                                      FROM categorias 
                                                      ORDER BY id_categoria ASC");
                    
                    if ($query) {
                        $result = mysqli_num_rows($query);
                        if ($result > 0) {
                            
                            $contador = 1; 

                            while ($data = mysqli_fetch_array($query)) {
                                $fecha = date('d-m-Y g:i a', strtotime($data['date_add']));
                    ?>
                                <tr>
                                    <td><?php echo $contador; ?></td>
                                    <td><?php echo $data['nombre_categoria']; ?></td>
                                    <td><?php echo $fecha; ?></td>
                                    <td class="text-center">
                                        <a class="link_edit" href="editar_categoria.php?id=<?php echo $data['id_categoria']; ?>"><i class="fas fa-edit"></i> Editar</a>
                                        <a class="link_delete" href="eliminar_categoria.php?id=<?php echo $data['id_categoria']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta categoría?');"><i class="fas fa-trash-alt"></i> Eliminar</a>
                                    </td>
                                </tr>
                    <?php
                                $contador++; 
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No se encontraron categorías registradas.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center' style='color:red;'>Error: " . mysqli_error($conexion) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>