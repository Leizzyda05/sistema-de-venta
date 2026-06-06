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

// -----------------------------------------------------------------
// NUEVO: CAPTURAR EL TÉRMINO DE BÚSQUEDA
// -----------------------------------------------------------------
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
    <?php include "include/scripts.php"; ?>
    <title>Lista de Productos</title>
    
    <style>
        /* 1. CONTENEDOR EXTERNO: Mueve el buscador arriba de la tabla y lo alinea a la derecha */
        .contenedor_buscador_superior {
            display: flex;
            justify-content: flex-end; /* Lo desplaza hacia el extremo derecho */
            margin-bottom: 15px;       /* Crea un espacio de separación vertical con la tabla */
            padding: 0 10px;           /* Evita que pegue al borde de la pantalla */
        }

        /* 2. TU ESTILO BASE: Mantiene tu diseño original para la casilla */
        .form_search {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Opcional: Sutil sombra para que combine con el entorno */
        }
        .form_search input[type="text"] {
            border: none;
            padding: 5px 10px;
            outline: none;
            font-size: 14px;
            width: 200px; /* Ancho controlado para que no se expanda de forma errática */
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
        .custom_alert_floating {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2ecc71; /* Verde éxito */
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
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'register_success'): ?>
    <div id="alerta-flotante" class="custom_alert_floating">
        <i class="fas fa-check-circle"></i> ¡Producto guardado exitosamente!
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alerta = document.getElementById('alerta-flotante');
            if (alerta) {
                // Se mantiene visible 3 segundos
                setTimeout(() => {
                    alerta.style.opacity = '0';
                    alerta.style.transform = 'translateY(-20px)'; // Efecto visual de subida
                    
                    // Se elimina del DOM una vez termine la transición CSS
                    setTimeout(() => {
                        alerta.remove();
                    }, 500); 
                }, 3000);
            }
        });
    </script>
<?php endif; ?>
    <?php include "include/header.php"; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    <div id="alerta-flotante" class="custom_alert_floating">
        <i class="fas fa-check-circle"></i> ¡Producto actualizado exitosamente!
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alerta = document.getElementById('alerta-flotante');
            if (alerta) {
                // Espera 3 segundos (3000ms) y luego cambia la opacidad para el efecto de desvanecido
                setTimeout(() => {
                    alerta.style.opacity = '0';
                    alerta.style.transform = 'translateY(-20px)'; // Pequeño efecto de subida al irse
                    
                    // Elimina por completo el elemento del HTML después de terminar la transición de CSS
                    setTimeout(() => {
                        alerta.remove();
                    }, 500); 
                }, 3000);
            }
        });
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
                        <th>Precio (Bs.)</th>
                        <th>Precio (USD)</th>
                        <th>Categoría</th>
                        <th>Fecha de Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // -------------------------------------------------------------
                    // MODIFICADO: CONSULTA ADAPTATIVA CON FILTRO LIKE
                    // -------------------------------------------------------------
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
                                $precio_bs = (float)$data['precio_venta'];
                                $precio_usd = ($tasa_dolar_num > 0) ? ($precio_bs / $tasa_dolar_num) : 0;
                                $fecha = date('d-m-Y g:i a', strtotime($data['date_add']));
                    ?>
                                <tr>
                                    <td><?php echo $contador; ?></td>
                                    <td><?php echo $data['nombre_producto']; ?></td>
                                    <td><b><?php echo number_format($precio_bs, 2, ',', '.'); ?> Bs.</b></td>
                                    <td style="color: #0515f8; font-weight: bold;">$ <?php echo number_format($precio_usd, 2, '.', ','); ?></td>
                                    <td><?php echo $data['nombre_categoria']; ?></td>
                                    <td><?php echo $fecha; ?></td>
                                    <td class="text-center">
                                        <a class="link_edit" href="editar_producto.php?id=<?php echo $data['id_producto']; ?>"><i class="fas fa-edit"></i> Editar</a>
                                        <a class="link_delete" href="eliminar_producto.php?id=<?php echo $data['id_producto']; ?>"><i class="fas fa-trash-alt"></i> Eliminar</a>
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

    <?php include "include/footer.php"; ?>
</body>
</html>