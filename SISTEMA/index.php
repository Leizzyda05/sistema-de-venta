<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Conexión directa a la base de datos para evitar el error del include
$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; // Asegúrate de que este sea el nombre exacto de tu BD

$conexion = @mysqli_connect($servername, $username, $password, $database);

if (!$conexion) {
    die("Error de conexión con la base de datos: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

// Variable con valor inicial por defecto
$precio_dolar = "0.00"; 

// 2. Buscamos el dato en la columna 'valor'
$query_tasa = mysqli_query($conexion, "SELECT valor FROM configuracion WHERE id = 1");

if ($query_tasa && mysqli_num_rows($query_tasa) > 0) {
    $data_tasa = mysqli_fetch_assoc($query_tasa);
    $precio_dolar = $data_tasa['valor']; 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Sistema de Venta</title>  
    <style>
    /* ==========================================================================
       ESTILOS DEL CONTENEDOR DE TASA (FORMULARIO MANUAL)
       ========================================================================== */
    .contenedor_tasas {
        max-width: 600px;
        background: #fff;
        margin: 40px auto;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1); 
        text-align: center;
        font-family: 'Arial', sans-serif;
    }

    .contenedor_tasas h2 {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .contenedor_tasas h3 {
        font-size: 20px;
        color: #34495e;
        margin-top: 0;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .contenedor_tasas hr {
        border: 0;
        background: #CCC;
        height: 1px;
        margin-bottom: 25px;
    }

    .flex_divisas {
        display: flex;
        justify-content: space-around;
        gap: 20px;
        margin-top: 20px;
    }

    .caja_moneda {
        flex: 1;
        padding: 20px;
        border-radius: 6px;
        background: #fdfdfd;
        box-shadow: inset 0px 0px 5px rgba(0,0,0,0.05);
    }

    .caja_moneda.usd {
        border-bottom: 4px solid #2ecc71; /* Verde */
    }

    .titulo_moneda {
        font-size: 13px;
        color: #7f8c8d;
        text-transform: uppercase;
        font-weight: bold;
        margin: 0 0 12px 0;
        letter-spacing: 0.5px;
    }

    .input_valor {
        width: 100%;
        max-width: 200px;
        padding: 8px 10px;
        font-size: 18px;
        text-align: center;
        font-weight: bold;
        color: #333;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        outline: none;
        transition: border-color 0.3s;
    }

    .input_valor:focus {
        border-color: #2ecc71;
    }

    .contenedor_boton {
        margin-top: 25px;
    }

    .btn_guardar {
        background: #2ecc71;
        color: #fff;
        border: none;
        padding: 10px 30px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s, transform 0.1s;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    .btn_guardar:hover {
        background: #27ae60;
    }

    .btn_guardar:active {
        transform: scale(0.98);
    }

    /* ==========================================================================
       ESTILOS NOTIFICACIONES TOAST (TU DISEÑO ORIGINAL)
       ========================================================================== */
    .custom-toast-delete {
        position: fixed;
        bottom: 20px;       
        right: -400px;      
        background-color: #2ecc71; 
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
    /* ==========================================================================
       CONTROLADOR DE LA NOTIFICACIÓN "TASA GUARDADA"
       ========================================================================== */
    $mensaje_alerta = "";
    $bg_color = "#2ecc71"; 
    $icono = "fas fa-check-circle";

    if (isset($_GET['msg']) && $_GET['msg'] == 'rate_success') { 
        $mensaje_alerta = "¡Tasa guardada exitosamente!";
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
        
        <div class="contenedor_tasas">
            <h2>Bienvenido al Sistema</h2>
            <h3>Actualizar Tasa Oficial (BCV)</h3>
            <hr>

            <form action="procesar_tasa.php" method="POST">
                <div class="flex_divisas">
                    
                    <div class="caja_moneda usd">
                        <p class="titulo_moneda">Dólar USD</p>
                        <input type="number" 
                               step="0.01" 
                               min="0"
                               name="precio_dolar" 
                               placeholder="Ej: 45.50"
                               class="input_valor" required>
                    </div>

                </div>

                <div class="contenedor_boton">
                    <button type="submit" class="btn_guardar">Guardar Tasa</button>
                </div>
            </form>

        </div>

    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>