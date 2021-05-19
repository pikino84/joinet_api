<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
require_once 'functions.php';
$start_time = start_time();
echo "\n\n/**************** insert_products.php ************************/";
echo "\n\n➜ Cominza la actualizacion de productos \n";
$items_origin = products_origen('all_skus_origen');
// Obtenemos datos de la API de origen
$items_origin = json_decode($items_origin, true);
$total_items_origen = count($items_origin);
echo "➜El total de items de origen son:  $total_items_origen \n";
// Obtener el total de lotes del API de origen
$lotes = ceil( count( $items_origin ) / $items_by_lote );
echo "➜El total de lotes de origen son:  $lotes \n";
// formamos el parámetro de lista de SKUs a actualizar
$indice = 0;
$all_skus_origen = array();
$all_skus_woocom = array();
for($cuenta_lotes = 1; $cuenta_lotes <= $lotes; $cuenta_lotes++ ){
    echo "➜ Creando lote numero: $cuenta_lotes de $lotes para ser consultado\n";
    $param_sku = '';
    foreach( array_slice($items_origin, $indice) as $key => $item ){
        if($indice % $items_by_lote == 0 && $indice != 0  && $indice != 1){
            $indice++;
            break;    
        }
        $indice++;
        $item = trim($item);
        $param_sku .= $item . ',';
        array_push($all_skus_origen, $item);
    }    
    // Obtenemos todos los productos de la lista de SKUs
    $woocommerce = products_woocommerce();
    echo "Cadena de items: \n $param_sku  \n";
    $products = $woocommerce->get('products/?per_page=100&sku='. $param_sku);
    
    // Generando los SKU no encontrados en woocommerce
    foreach($products as $key => $val){
        foreach($val as $k => $v){
            if($k == 'sku'){
                $v = trim($v);
                array_push($all_skus_woocom, $v);
            }
        }
    }
}
$new_skus = array_diff( $all_skus_origen, $all_skus_woocom);
$nuevos_items = count($new_skus);
echo "Total de nuevos items: $nuevos_items \nLista de nuevos items: \n";
print_r($new_skus);
//$items_by_lote son 100 el maximo que permite woocomerce
//En esta condicion es verdadera solo si 100 articulos o menos de lo contrario se parte en lotes de 100
if(count($new_skus) <= $items_by_lote && count($new_skus) > 0){
    $skus = "'" . implode( "','", $new_skus )."'";
    $endpoint = 'get_products_by_sku';
    $params = '?skus='.$skus;
    $new_items = json_decode( products_origen($endpoint, $params), true);
    foreach($new_items['inventory'] as $key ){
        //No muestra productos bloqueados 
        if( $key['bloqueado'] == 1 ||  $key['no_show_wc'] == 1){
            $visibility = "hidden";
        }else{
            $visibility = "visible";
        }

        if( $key['in_offer'] && $key['regular_price'] > $key['price'] ){
            $price = $key['price'];
            $regular_price = $key['regular_price'];
            $sale_price = $key['price'];
        }else{
            $price = $key['price'];
            $regular_price = $key['price'];
            $sale_price = $key['price'];
        }
        $stock_corona = 0;
        $stock_colon = 0;
        $stock_cotilla = 0;
        $total_stock = 0;
        $total_stock = 0;
        $stock_corona = $key['MBPOS_STOCK_CORONA'];
        $stock_colon = $key['MBPOS_STOCK_COLON'];
        $stock_cotilla = $key['MBPOS_STOCK_COTILLA'];
        $total_stock = $stock_corona + $stock_colon + $stock_cotilla;
        $data['create'][] = array(
            'sku' => $key['sku'],
            "price" => $price,
            'regular_price' => $key['price'],
            'sale_price' => $sale_price,
            'stock_quantity' => $total_stock,
            'name' => $key['name'],
            'catalog_visibility' => $visibility,
            'status' => 'publish',
            'manage_stock' => true,
            'meta_data' => array([
                'key' => "_fixed_price_rules",
                'value' => array(
                    $key['c2'] => $key['price2'],
                    $key['c3'] => $key['price3'],
                    $key['c4'] => $key['price4'],
                    $key['c5'] => $key['price5'],
                    $key['c6'] => $key['price6'],
                ),
            ],
            [
                'key' => "MBPOS_CATIDAD_CAJA",
                'value' => $key['MBPOS_CATIDAD_CAJA']
            ],
            [
                'key' => "MBPOS_CLAVE_SAT",
                'value' => $key['MBPOS_CLAVE_SAT']
            ],
            [
                'key' => "MBPOS_CODIGO_BARRAS",
                'value' => $key['MBPOS_CODIGO_BARRAS']
            ],
            [
                'key' => "MBPOS_COSTO",
                'value' => $key['MBPOS_COSTO']
            ],
            [
                'key' => "MBPOS_COSTO_ULTIMO",
                'value' => $search_item['MBPOS_COSTO_ULTIMO']
            ],
            [
                'key' => "MBPOS_NOMBRE_PROVEEDOR",
                'value' => $key['MBPOS_NOMBRE_PROVEEDOR']
            ],
            [  
                'key' => "MBPOS_UNIDAD",
                'value' => $key['MBPOS_UNIDAD']

            ],
            [
                'key' => "MBPOS_CODIGO_UNO",
                'value' => $key['MBPOS_CODIGO_UNO']
            ],
            [
                'key' => "MBPOS_CODIGO_DOS",
                'value' => $key['MBPOS_CODIGO_DOS']
            ],
            [
                'key' => "MBPOS_STOCK_CORONA",
                'value' => $corona
            ],
            [
                'key' => "MBPOS_STOCK_COLON",
                'value' => $colon
            ],
            [
                'key' => "MBPOS_STOCK_COTILLA",
                'value' => $cotilla
            ])
        );
    }
    echo "➜ Data generada para el lote  \n";
    
    // Actualización en lotes
    $result = $woocommerce->post('products/batch', $data);
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    if (! $result) {
        echo("❗Error al actualizar productos <br> \n");
    } else {
        print("✔ Productos actualizados correctamente <br>  \n");
    };
}else{
    //armando lotes de 100
    $lotes = ceil( count( $new_skus ) / $items_by_lote );
    echo "➜ Total de lotes a enviar $lotes <br> \n";
    $indice = 0;
    for($i = 1; $i <= $lotes; $i++){
        $lote_skus = array();
        foreach( array_slice($new_skus, $indice) as $key => $new_sku ){
            if($indice % $items_by_lote == 0 && $indice != 0  && $indice != 1){
                $indice++;
                break;    
            }
            $indice++;
            array_push($lote_skus, $new_sku);
        }        
        $skus = "'" . implode( "','", $lote_skus )."'";
        $endpoint = 'get_products_by_sku';
        $params = '?skus='.$skus;
        $new_items = json_decode( products_origen($endpoint, $params), true);
        $items_eviados = count($new_items);
        echo "➜ items enviados $items_eviados en el lote $i <br> \n";
        $data = [];
        //ESTA PARTE ESTA PENDIENTE DE COMPLETAR QUE SE ENCUENTRAN EN LA PARTE DE ARRIBA LE FALTAN TODOS LOS CAMPOS PERSONALIZADOS Y UN PAR DE COSAS MAS
        foreach($new_items as $key ){
            $data['create'][] = [
                'sku' => $key['sku'],
                'name' => $key['name'],
                'type' => 'simple',
                'regular_price' => $key['price'],
                'stock_quantity' => $key['qty'],
                'manage_stock' => true,
                'meta_data' => [
                    'key' => '_fixed_price_rules',
                    'value' => [
                        $key['c2'] => $key['price2'],
                        $key['c3'] => $key['price3'],
                        $key['c4'] => $key['price4'],
                        $key['c5'] => $key['price5'],
                        $key['c6'] => $key['price6'],
                    ],
                ]
            ];
        }
        $result = $woocommerce->post('products/batch', $data);

        if (! $result) {
            echo("❗Error al actualizar productos <br>  \n");
        } else {
            print("✔ Productos actualizados correctamente <br> \n");
            echo end_time($start_time);
        };
    }
}