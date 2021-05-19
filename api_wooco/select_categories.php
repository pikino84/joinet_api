<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';
echo "\n\n/****************************************/";
echo "\n➜ Comenzando la actualizacion de productos \n";
// Conexión WooCommerce API destino
// ================================
$woocommerce = products_woocommerce();
// ================================
echo "➜ Conexcion a  Woocommerce exitosa\n";
// Conexión API origen
// ===================
$items_origin = products_origen('all_categories');
echo "➜ Conexcion a API de origen exitosa\n";
// Obtenemos datos de la API de origen
$items_origin = json_decode($items_origin, true);
$total_items_origen = count($items_origin);
echo "➜ El total de categorias en el API de origen son: $total_items_origen \n";
// Obtener el total de lotes del api de origen
$lotes = ceil( $total_items_origen / $items_by_lote );
echo "➜ Total de lotes del API de origen son: $lotes  \n\n\n";
if($lotes <= 1){
    echo "➜ Obteniendo los IDs de las categorias  \n";
    $categorias = '';
    foreach( array_slice($items_origin, $indice) as $item ){
        if($indice % $items_by_lote == 0 && $indice != 0  && $indice != 1){
            $indice++;
            break;    
        }
        $indice++;
        $categorias .= $item['categoria'] . ',';
    }
}else{
    echo "➜ Obteniendo los IDs de las productos del lote numero: $cuenta_lotes  \n";
}