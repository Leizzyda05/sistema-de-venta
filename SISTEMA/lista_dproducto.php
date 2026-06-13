<?php
// 1. CONEXIÓN A LA BASE DE DATOS Y SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);

if (!$conexion) {
    die("Error de conexión con la base de datos: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8"); 

// 2. OBTENER LA TASA DESDE LA BASE DE DATOS (Sincronizado con index.php)
$tasa_dolar_num = 1.0; // Valor por defecto por seguridad matemática

$query_tasa = mysqli_query($conexion, "SELECT valor FROM configuracion WHERE id = 1");

if ($query_tasa && mysqli_num_rows($query_tasa) > 0) {
    $data_tasa = mysqli_fetch_assoc($query_tasa);
    // Convertimos a float para poder multiplicar matemáticamente sin errores
    $tasa_dolar_num = (float)$data_tasa['valor']; 
}

// CAPTURAR EL TÉRMINO DE BÚSQUEDA
$busqueda = "";
if (isset($_GET['busqueda'])) {
    $busqueda = mysqli_real_escape_with_like_support($conexion, trim($_GET['busqueda']));
}

// Función auxiliar para sanitizar la búsqueda protegiendo contra Inyección SQL
function mysqli_real_escape_with_like_support($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include "include/scripts.php"; ?>
    <title>Lista de Productos</title>
    
    <style>
        .contenedor_buscador_superior {
            display: flex;
            justify-content: flex-end; 
            margin-bottom: 15px;      
            padding: 0 10px;           
        }

        .form_search {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
        }
        .form_search input[type="text"] {
            border: none;
            padding: 5px 10px;
            outline: none;
            font-size: 14px;
            width: 200px; 
        }
        .form_search .btn_search {
            background: #2ecc71;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .form_search .btn_clear {
            background: #e74c3c;
            color: #fff;
            padding: 6px 12px;
            border-radius: 3px;
            text-decoration: none;
            margin-left: 5px;
            font-size: 14px;
        }

        /* NOTIFICACIONES TOAST */
        .custom-toast-delete {
            position: fixed;
            bottom: 20px;       
            right: -400px;      
            background-color: #046a5b;
            color: #ffffff;
            padding: 16px 25px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            transition: right 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .custom-toast-delete.show {
            right: 20px;       
        }

        .custom-toast-delete i {
            font-size: 20px;
        }

        .custom-toast-delete .toast-close-delete {
            margin-left: 15px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .custom-toast-delete .toast-close-delete:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    
    <?php include "include/header.php"; ?>

    <?php 
    $mensaje_alerta = "";
    $bg_color = "#046a5b"; 
    $icono = "fas fa-check-circle";

    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'deleted') {
            $mensaje_alerta = "¡Producto eliminado correctamente!";
        } elseif ($_GET['status'] == 'error') {
            $mensaje_alerta = "No se pudo eliminar el producto del inventario.";
            $bg_color = "#e74c3c"; 
            $icono = "fas fa-exclamation-triangle";
        }
    } elseif (isset($_GET['msg'])) {
        if ($_GET['msg'] == 'register_success') {
            $mensaje_alerta = "¡Producto guardado exitosamente!";
        } elseif ($_GET['msg'] == 'success') {
            $mensaje_alerta = "¡Producto actualizado exitosamente!";
        }
    }
    ?>

    <?php if (!empty($mensaje_alerta)): ?>
        <div id="toastDeleteMessage" class="custom-toast-delete" style="background-color: <?php echo $bg_color; ?>;">
            <i class="<?php echo $icono; ?>"></i>
            <span><?php echo $mensaje_alerta; ?></span>
            <i class="fas fa-times toast-close-delete" onclick="closeToastDelete()"></i>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var toastDelete = document.getElementById("toastDeleteMessage");
                setTimeout(function() {
                    if(toastDelete) toastDelete.classList.add("show");
                }, 200);

                setTimeout(function() {
                    closeToastDelete();
                }, 4200);
            });

            function closeToastDelete() {
                var toastDelete = document.getElementById("toastDeleteMessage");
                if(toastDelete) {
                    toastDelete.classList.remove("show"); 
                    setTimeout(function() {
                        toastDelete.remove(); 
                    }, 500);
                }
            }
            window.history.replaceState({}, document.title, window.location.pathname);
        </script>
    <?php endif; ?>

    <section id="container">
        <div class="data_table"> 
            
            <div class="header_table">
                <h1><i class="fas fa-cube"></i> Lista de Productos</h1>
                
                <form action="" method="get" class="form_search">
                    <input type="text" name="busqueda" id="busqueda" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn_search"><i class="fas fa-search"></i> Buscar</button>
                    <?php if (!empty($busqueda)): ?>
                        <a href="lista_dproducto.php" class="btn_clear"><i class="fas fa-times"></i> Limpiar</a>
                    <?php endif; ?>
                </form>

                <a href="registro_dproducto.php" class="btn_new">Crear Producto</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Precio (USD)</th>
                        <th>Precio (Bs.)</th>
                        <th>Categoría</th>
                        <th>Fecha de Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where = "";
                    if (!empty($busqueda)) {
                        $where = "WHERE p.nombre_producto LIKE '%$busqueda%'";
                    }

                    $query = mysqli_query($conexion, "SELECT p.id_producto, p.nombre_producto, p.precio_venta, c.nombre_categoria, p.date_add 
                                                      FROM productos p 
                                                      INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
                                                      $where
                                                      ORDER BY p.nombre_producto ASC");
                    
                    if ($query) {
                        $result = mysqli_num_rows($query);
                        if ($result > 0) {
                            
                            $contador = 1; 

                            while ($data = mysqli_fetch_array($query)) {
                                $precio_usd = (float)$data['precio_venta'];
                                // Multiplicación por la tasa guardada en tu Base de Datos
                                $precio_bs = $precio_usd * $tasa_dolar_num;
                                $fecha = date('d-m-Y g:i a', strtotime($data['date_add']));
                    ?>
                                <tr>
                                    <td><?php echo $contador; ?></td>
                                    <td><?php echo $data['nombre_producto']; ?></td>
                                    
                                    <td style="color: #0515f8; font-weight: bold;">$ <?php echo number_format($precio_usd, 2, '.', ','); ?></td>
                                    
                                    <td><b><?php echo number_format($precio_bs, 2, ',', '.'); ?> Bs.</b></td>
                                    
                                    <td><?php echo $data['nombre_categoria']; ?></td>
                                    <td><?php echo $fecha; ?></td>
                                    <td class="text-center">
                                        <a class="link_edit" href="editar_producto.php?id=<?php echo $data['id_producto']; ?>"><i class="fas fa-edit"></i> Editar</a>
                                        <button type="button" class="link_edit btn_delete" style="border: none; cursor: pointer;" onclick="confirmarEliminacion(<?php echo $data['id_producto']; ?>)">Eliminar</button>
                                    </td>
                                </tr>
                    <?php
                                $contador++; 
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No se encontraron productos coincidentes.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center' style='color:red;'>Error: " . mysqli_error($conexion) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro de eliminar este producto?',
                text: "¡Esta acción no se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00a65a', 
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                position: 'center'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'eliminar_producto.php?id=' + id;
                }
            });
        }
    </script>

    <?php include "include/footer.php"; ?>
</body>
</html>