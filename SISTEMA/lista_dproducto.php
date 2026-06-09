<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion, "utf8"); 

if (!$conexion) {
    die("Error de conexión con la base de datos.");
}

// 2. OBTENER LA TASA DEL DÓLAR DESDE EL BCV
$tasa_dolar_num = 1.0; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.bcv.org.ve/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
$html = curl_exec($ch);
curl_close($ch);

if ($html !== false && !empty($html)) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $query_usd = $xpath->query('//div[@id="dolar"]//div[contains(@class, "centrado")]/strong');
    
    if ($query_usd->length > 0) {
        $raw_usd = trim($query_usd->item(0)->nodeValue);
        $tasa_clean = str_replace(',', '.', str_replace('.', '', $raw_usd));
        $tasa_dolar_num = (float)$tasa_clean;
    }
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

        /* =========================================================================
           ESTILOS PARA LAS NOTIFICACIONES FLOTANTES TIPO TOAST (INFERIOR DERECHA)
           ========================================================================= */
        .custom-toast-delete {
            position: fixed;
            bottom: 20px;       /* Posicionado abajo */
            right: -400px;      /* Inicia oculto a la derecha fuera de la pantalla */
            background-color: #2ecc71; /* Verde éxito por defecto */
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

        /* Clase activa para activar el deslizamiento */
        .custom-toast-delete.show {
            right: 20px;       /* Se desliza a su posición visible abajo a la derecha */
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
    $bg_color = "#2ecc71"; // Verde por defecto
    $icono = "fas fa-check-circle";

    // Evaluar parámetros de redirección URL
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'deleted') {
            $mensaje_alerta = "¡Producto eliminado correctamente!";
        } elseif ($_GET['status'] == 'error') {
            $mensaje_alerta = "No se pudo eliminar el producto del inventario.";
            $bg_color = "#e74c3c"; // Rojo para fallos
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
                
                // Hace aparecer el toast deslizándose suavemente desde la derecha a los 200ms
                setTimeout(function() {
                    if(toastDelete) toastDelete.classList.add("show");
                }, 200);

                // Desaparece automáticamente después de 4 segundos (4000ms)
                setTimeout(function() {
                    closeToastDelete();
                }, 4200);
            });

            function closeToastDelete() {
                var toastDelete = document.getElementById("toastDeleteMessage");
                if(toastDelete) {
                    toastDelete.classList.remove("show"); // Inicia animación de salida
                    setTimeout(function() {
                        toastDelete.remove(); // Remueve el elemento del HTML por completo
                    }, 500);
                }
            }
            
            // Limpia los parámetros de la barra de direcciones para evitar duplicados al recargar
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
                                                      ORDER BY p.id_producto ASC");
                    
                    if ($query) {
                        $result = mysqli_num_rows($query);
                        if ($result > 0) {
                            
                            $contador = 1; 

                            while ($data = mysqli_fetch_array($query)) {
                                $precio_usd = (float)$data['precio_venta'];
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
                                        <button type="button" class="btn_delete" onclick="confirmarEliminacion(<?php echo $data['id_producto']; ?>)">Eliminar</button>
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