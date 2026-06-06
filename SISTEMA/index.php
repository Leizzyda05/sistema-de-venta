<?php
// Variables con valores iniciales por si falla la conexión
$precio_dolar = "No disponible";

// 1. Iniciamos cURL para descargar la web de forma segura
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://www.bcv.org.ve/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Engañamos al servidor del BCV simulando ser un navegador Chrome real
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
// OBLIGATORIO PARA XAMPP: Ignorar errores de certificados SSL locales
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// Tiempo máximo de espera para que no se quede colgada tu página (5 segundos)
curl_setopt($ch, CURLOPT_TIMEOUT, 5); 

$html = curl_exec($ch);
curl_close($ch);

// 2. Si logramos descargar el HTML, extraemos los valores con XPath
if ($html !== false && !empty($html)) {
    $dom = new DOMDocument();
    
    // Desactivamos errores visuales por culpa del HTML mal cerrado del BCV
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);

    // Buscamos la etiqueta <strong> exacta que está dentro del bloque de Dólar
    $query_usd = $xpath->query('//div[@id="dolar"]//div[contains(@class, "centrado")]/strong');
    if ($query_usd->length > 0) {
        $precio_dolar = trim($query_usd->item(0)->nodeValue) . " Bs.";
    }

}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "include/scripts.php"; ?>
    <title>Sistema Venta</title>  
    <style>
    /* Contenedor principal blanco (Todo se encierra aquí) */
    .contenedor_tasas {
        max-width: 600px;
        background: #fff;
        margin: 40px auto;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1); 
        text-align: center;
        font-family: 'Arial', sans-serif; /* O la fuente que use tu sistema */
    }

    /* Título de bienvenida */
    .contenedor_tasas h2 {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 5px;
        font-weight: bold;
    }

    /* Subtítulo: Tasa Oficial (BCV) */
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

    /* Contenedor Flex para las cajas de las monedas */
    .flex_divisas {
        display: flex;
        justify-content: space-around;
        gap: 20px;
        margin-top: 20px;
    }

    /* Estilo base para cada tarjeta de moneda */
    .caja_moneda {
        flex: 1;
        padding: 15px;
        border-radius: 6px;
        background: #fdfdfd;
        box-shadow: inset 0px 0px 5px rgba(0,0,0,0.05);
    }

    /* Bordes de color inferiores distintivos */
    .caja_moneda.usd {
        border-bottom: 4px solid #2ecc71; /* Verde BCV / Éxito */
    }

    .caja_moneda.eur {
        border-bottom: 4px solid #3498db; /* Azul */
    }

    /* Texto pequeño de arriba (DÓLAR USD) */
    .titulo_moneda {
        font-size: 13px;
        color: #7f8c8d;
        text-transform: uppercase;
        font-weight: bold;
        margin: 0 0 8px 0;
        letter-spacing: 0.5px;
    }

    /* El valor numérico de la tasa */
    .valor_moneda {
        font-size: 18px;
        color: #333;
        font-weight: bold;
        margin: 0;
    }
</style>
</head>
<body>
    <?php include "include/header.php"; ?>
    
    <section id="container">
        <div class="contenedor_tasas">
    <h2>Bienvenido al Sistema</h2>
    <h3>Tasa Oficial (BCV)</h3>
    <hr>

    <div class="flex_divisas">
        
        <div class="caja_moneda usd">
            <p class="titulo_moneda">Dólar USD</p>
            <p class="valor_moneda">567,68280000 Bs.</p>
        </div>

    </div>
</div>
    </section>

    <?php include "include/footer.php"; ?>
</body>
</html>