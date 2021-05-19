<?php
/********** AL MACENA LAS  IMAGENES DE MANERA LOCAL Y LA RUTA EN LA BASE DE DATOS*************** */
$start_time = microtime(true);
require __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';
require_once '_functions/con_sql_corona.php';
echo "\n\n/****************************************/";
echo "\n➜ Comenzando la actualizacion de imagenes \n";
// Se verifica si no esxiste la carpeta contenedora de imagenes
// ================================
if (!file_exists("C:\MyBusinessPOS20\images\Nuevos\Articulos")) {
    mkdir("C:\MyBusinessPOS20\images\Nuevos\Articulos", 0777, true);
}
// Conexión WooCommerce API destino
// ================================
$woocommerce = products_woocommerce();
// ================================
echo "➜ Conexcion a  Woocommerce exitosa\n";
// Conexión API origen
// ===================
$items_origin = productos_sin_imagen('get_products_no_img');
echo "➜ Conexcion a API de origen exitosa\n";
// Obtenemos datos de la API de origen
$items_origin = json_decode($items_origin, true);
$total_items_origen = count($items_origin);
echo "➜ El total de items del API de origen sin fotografia son: $total_items_origen \n";
// Obtener el total de lotes del api de origen
$lotes = ceil( $total_items_origen / $items_by_lote );
echo "➜ Total de lotes del API de origen sin imagen son: $lotes  \n\n\n";
$indice = 0;
if($lotes >= 2){
    //Se obtienen los ID's da cada producto en woocommerce que coincida con los SKU del API de origen y el API WC
    for($cuenta_lotes = 1; $cuenta_lotes <= $lotes; $cuenta_lotes++ ){
        echo "➜ Obteniendo IDs de los productos del lote numero: $cuenta_lotes de $lotes  \n";
        $param_sku = '';
        foreach( array_slice($items_origin, $indice) as $item ){
            if($indice % $items_by_lote == 0 && $indice != 0  && $indice != 1){
                $indice++;
                break;    
            }
            $indice++;
            $param_sku .= $item['sku'] . ',';
        }
        echo "Cadena de items: \n $param_sku  \n";
        // Obtenemos todos los productos de la lista de SKUs que coincidan
        $products = $woocommerce->get('products/?per_page=100&sku='. $param_sku);
        //Obtenermos el  total de items que coincidieron
        $total_items_origen = count($products);
        echo "➜ $total_items_origen items encontrados en woocommerce del lote  $cuenta_lotes  \n";
        // Construimos la data con base a los productos recuperados
        foreach($products as $product){
            // Filtramos el array de origen por sku
            $sku = trim($product->sku);
            $search_item = array_filter( $items_origin, function($item) use($sku) {
                return $item['sku'] == $sku;
            });
            //No muestra productos bloqueados
            if(count($product->images) >= 1){
                $img = (array) $product->images[0];
                $image = $img['src'];
                $path_img = pathinfo($image, PATHINFO_DIRNAME );
                $path_img = pathinfo($image, PATHINFO_DIRNAME );
                $name_img = pathinfo($image, PATHINFO_FILENAME );
                $exte_img = pathinfo($image, PATHINFO_EXTENSION);
                //$thumb = $path_img."/".$name_img."-150x150.".$exte_img;
                $thumb = $path_img."/".$name_img.".".$exte_img;
                $local = download_imagen($thumb);
                echo $query = "UPDATE prods  SET url = '$local', ACREXPORTADO = 0, acrURLWeb = '$thumb'  WHERE ARTICULO = '$sku' ";
                echo "\n";
                $result = $dbh->query($query);
            }else{
                /*$thumb = 'https://joinet.com/wp-content/uploads/woocommerce-placeholder-85x85.png';
                $local = download_imagen($thumb);
                echo $query = "UPDATE prods  SET url = '$local', ACREXPORTADO = 0, acrURLWeb = '$thumb' WHERE ARTICULO = '$sku' ";
                echo "\n";
                $result = $dbh->query($query);*/
            }
        }
        $end_time = microtime(true);
        $duration = $end_time - $start_time;
        $hours = (int)($duration/60/60);
        $minutes = (int)($duration/60)-$hours*60;
        $seconds = (int)$duration-$hours*60*60-$minutes*60; 
        echo "\nTiempo transcurrido  $hours  horas,  $minutes  minutos y $seconds  segundos\n\n";
    }
}
echo "➜ !Proceso finalizado exitosamente! \n";