<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/********** AL MACENA LOS LINKS DE LAS IMAGENES EN LA BASE DE DATOS*************** */
require __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';
require_once '_functions/con_sql_corona.php';
echo "\n\n/****************************************/";
echo "\n➜ Comenzando la actualizacion de imagenes \n";
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
        echo "➜ Obteniendo IDs de los productos del lote numero: $cuenta_lotes  \n";
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
                $img = $img['src'];
                $query = "UPDATE prods  SET url = '$img', ACREXPORTADO = 0 WHERE ARTICULO = '$sku'";
                $result = $dbh->query($query);
            }else{
                $img = '';
                $query = "UPDATE prods  SET url = '$img', ACREXPORTADO = 0 WHERE ARTICULO = '$sku'";
                $result = $dbh->query($query);
            }

        }
    }
}else{

}
echo "➜ !Proceso finalizado exitosamente! \n";