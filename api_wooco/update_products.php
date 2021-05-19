<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
require __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';
$start_time = start_time();
echo "\n\n/**************** update_products.php ************************/";
echo "\n➜ Comenzando la actualizacion de productos \n";
// Conexión WooCommerce API destino
// ================================
$woocommerce = products_woocommerce();
// ================================
echo "➜ Conexcion a  Woocommerce exitosa\n";
// Conexión API origen
// ===================
$items_origin = products_origen();
echo "➜ Conexcion a API de origen exitosa\n";
// Obtenemos datos de la API de origen
$items_origin = json_decode($items_origin, true);

$total_items_origen = count($items_origin['inventory']);
echo "➜ El total de items del API de origen son: $total_items_origen \n";
// Obtener el total de lotes del api de origen
$lotes = ceil( $total_items_origen / $items_by_lote );
echo "➜ Total de lotes del API de origen son: $lotes  \n\n\n";
//SE OBTIENEN LAS CATEGORIAS DE WOOCOMMERCE (nombre, id, etc)
$categories_woocommerce = get_categories_woocommerce($woocommerce);
//SE OBTIENEN LAS CATEGORIAS DE MBPOS 
$get_categories_MBPOS = json_decode(get_categories_MBPOS(), true);
//SE VERIFICA SI HAY NUEVAS CATEGORIAS PARA INSERTAR
$array_cat_ori = array();
foreach($get_categories_MBPOS as $cat_ori_name){
    $array_cat_ori[trim($cat_ori_name['long_name'])] = trim($cat_ori_name['long_name']);
}
//SE ELIMINA DEL ARREGLO LAS CATEGORIAS REPETIDAS, PARA SOLO DEJAR LANUEVAS
foreach($categories_woocommerce as $cat_woo ){
    $cat_woo_name = trim($cat_woo->name);
    if( array_key_exists($cat_woo_name, $array_cat_ori) ){
        unset($array_cat_ori[$cat_woo_name]);
    }
}

if( count($array_cat_ori) >  0){
    $cat_data = array();
    foreach($array_cat_ori as $new_cat ){
        $cat_data[] = array('name' => $new_cat);
    }
    $c_data = [
        'create' => $cat_data
    ];
    $result = $woocommerce->post('products/categories/batch', $c_data);
    if (! $result) {
        echo("❗Error al actualizar las categorias \n\n\n");
    } else {
        print("✔ Categorias actualizadas !Exitosamente!  \n");
    }
}
//SE OBTIENEN LAS MARCAS DE WOOCOMMERCE (term_id, names, slug, etc.)
$brands_woocommerce = get_brands_woocommerce($woocommerce);
//SE OBTIENEN LAS MARCAS DE MBPOS (names, slug, etc.)
$brands_origen = json_decode(get_brands_MBPOS(), true);
//SE VERIFICA SI HAY NUEVAS MARCAS PARA INSERTAR
$array_brand_ori = array();
foreach($brands_origen as $brand_ori_name){
    $array_brand_ori[trim($brand_ori_name['long_name'])] = trim($brand_ori_name['long_name']);
}
$array_brands = array();
foreach($brands_woocommerce as $brand_woo ){
    //SE ALMACENAN LA MARCAS PARA DESPUES ASIGNAR SE LAS A SU PRODUCTO CORRECPONDIENTE
    $array_brands[$brand_woo->name] = $brand_woo->term_id;
    $brand_woo_name = trim($brand_woo->name);
    if( array_key_exists($brand_woo_name, $array_brand_ori) ){
        unset($array_brand_ori[$brand_woo_name]);
    }
}
//SI HAY NUEVAS MARCAS AQUI SE INSERTAN
if( count($array_brand_ori) > 0 ){   
    foreach($array_brand_ori as $brand ){
        $slug_brand = str_replace('_', '', $brand);
        $new_brand = array( 'name' => $brand, 'slug' => $slug_brand );

        $result = $woocommerce->post('brands', $new_brand);
        if (! $result) {
            echo("❗Error al actualizar las marcas \n\n\n");
        } else {
            print("✔ Marcas actualizadas !Exitosamente!  \n");
        }
    }
}
//CATEGORIAS
$array_categories = array();
foreach($categories_woocommerce as $category){
    $array_categories[$category->name] = $category->id;
}
$indice = 0;
$brands_mbpos = json_decode( get_brands_MBPOS(), true);
$no_encontrado = array();
//Se obtienen los ID's da cada producto en woocommerce que coincida con los SKU del API de origen y el API WC
for($cuenta_lotes = 1; $cuenta_lotes <= $lotes; $cuenta_lotes++ ){

    /*if($cuenta_lotes == 2){
        break;
    }*/
    echo "➜ Obteniendo IDs de los productos del lote numero: $cuenta_lotes  \n";
    $param_sku = '';
    $sku_origen =  array();
    foreach( array_slice($items_origin['inventory'], $indice) as $item ){
        if($indice % $items_by_lote == 0 && $indice != 0  && $indice != 1){
            $indice++;
            break;    
        }
        $indice++;
        $param_sku .= trim($item['sku']) . ',';
        
        $sku_origen[(string) $item['sku']] = (string) $item['sku'];
    }
    
    // Obtenemos todos los productos de la lista de SKUs que coincidan
    $products = $woocommerce->get('products/?per_page=100&sku='.$param_sku);
    //Obtenermos el  total de items que coincidieron
    $total_items_origen = count($products);
    //Vamos a obtener los articulos que no se han encontrado
    $sku_woo = array();
    foreach($products as $product){
        $sku_woo[trim((string) $product->sku)] = trim((string) $product->sku);
        $page_links[trim((string) $product->sku)] = $product->permalink;
    }
    foreach($sku_woo as $valor){
        if( array_key_exists(trim($valor), $sku_origen) ){
            unset($sku_origen[$valor]);
        }
    }
    $no_encontrado[] = $sku_origen;
    
    echo "$total_items_origen items encontrados en woocommerce del lote  $cuenta_lotes  \n";

    echo "Cadena de items: \n $param_sku  \n";

    // Construimos la data con base a los productos recuperados
    $item_data = [];
    $contador = 0;
    
    foreach($products as $product){
        $contador ++;
        // Filtramos el array de origen por sku
        $sku = trim($product->sku); 
        $search_item = array_filter( $items_origin['inventory'], function($item) use($sku) {
            return $item['sku'] == $sku;
        });
        //Ponemos el puntero en la primer posicion del array
        $search_item = reset($search_item);
        //No muestra productos bloqueados 
        if( $search_item['bloqueado'] == 1 ||  $search_item['no_show_wc'] == 1){
            $visibility = "hidden";
        }else{
            $visibility = "visible";
        }
        //SE ASGINAN A CADA PRODCUTO SU RESPECTIVA CATEGORIA
        if(array_key_exists ($search_item['categoria'], $array_categories)){
            $categoria_id = $array_categories[$search_item['categoria']]."\n";
        }else{
            $array_errors[] = array( 'cat'=>$search_item['categoria'] );
            $categoria_id = '';
        }
        //SE ASIGNA A CADA PRODUCTO SU RESPECTIVA MARCA
        if(array_key_exists ($search_item['marca'], $array_brands)){
            $marca_id = $array_brands[$search_item['marca']]."\n";
        }else{
            $array_errors[] = array( 'brand'=>$search_item['marca'] );
            $marca_id = '';
        }
        
        if( $search_item['in_offer'] && $search_item['regular_price'] > $search_item['price'] ){
            $price = $search_item['price'];
            $regular_price = $search_item['regular_price'];
            $sale_price = $search_item['price'];
        }else{
            $price = $search_item['price'];
            $regular_price = $search_item['price'];
            $sale_price = $search_item['price'];
        }
        $stock_corona = 0;
        $stock_colon = 0;
        $stock_cotilla = 0;
        $total_stock = 0;
        $stock_corona = $search_item['MBPOS_STOCK_CORONA'];
        $stock_colon = $search_item['MBPOS_STOCK_COLON'];
        $stock_cotilla = $search_item['MBPOS_STOCK_COTILLA'];
        $total_stock = $stock_corona + $stock_colon + $stock_cotilla;
        $item_data[] = array(
            'id' => $product->id,
            "price" => $price,
            'regular_price' => $regular_price,
            'sale_price' => $sale_price,
            'stock_quantity' => $total_stock,
            'name' => $search_item['name'],
            'catalog_visibility' => $visibility,
            'status' => 'publish',
            'manage_stock' => true,
            'brands' => $marca_id,
            'meta_data' => array([
                'key' => "_fixed_price_rules",
                'value' => array(
                    $search_item['c2'] => $search_item['price2'],
                    $search_item['c3'] => $search_item['price3'],
                    $search_item['c4'] => $search_item['price4'],
                    $search_item['c5'] => $search_item['price5'],
                    $search_item['c6'] => $search_item['price6'],
                ),
            ],
            [
                'key' => "MBPOS_CATIDAD_CAJA",
                'value' => $search_item['MBPOS_CATIDAD_CAJA']
            ],
            [
                'key' => "MBPOS_CLAVE_SAT",
                'value' => $search_item['MBPOS_CLAVE_SAT']
            ],
            [
                'key' => "MBPOS_CODIGO_BARRAS",
                'value' => $search_item['MBPOS_CODIGO_BARRAS']
            ],
            [
                'key' => "MBPOS_COSTO",
                'value' => $search_item['MBPOS_COSTO']
            ],
            [
                'key' => "MBPOS_COSTO_ULTIMO",
                'value' => $search_item['MBPOS_COSTO_ULTIMO']
            ],
            [
                'key' => "MBPOS_NOMBRE_PROVEEDOR",
                'value' => $search_item['MBPOS_NOMBRE_PROVEEDOR']
            ],
            [  
                'key' => "MBPOS_UNIDAD",
                'value' => $search_item['MBPOS_UNIDAD']

            ],
            [
                'key' => "MBPOS_CODIGO_UNO",
                'value' => $search_item['MBPOS_CODIGO_UNO']
            ],
            [
                'key' => "MBPOS_CODIGO_DOS",
                'value' => $search_item['MBPOS_CODIGO_DOS']
            ],
            [
                'key' => "MBPOS_STOCK_CORONA",
                'value' => $stock_corona
            ],
            [
                'key' => "MBPOS_STOCK_COLON",
                'value' => $stock_colon
            ],
            [
                'key' => "MBPOS_STOCK_COTILLA",
                'value' => $stock_cotilla
            ]),
            'categories' => array([
                'id' => $categoria_id
            ])
        );
    }
    // Construimos información a actualizar en lotes
    $data = [
        'update' => $item_data,
    ];   
    // Actualización en lotes
    $result = $woocommerce->post('products/batch', $data);
    if (! $result) {
        echo("❗Error al actualizar el lote numero $cuenta_lotes   \n\n\n");
    } else {
        print("✔ Lote numero $cuenta_lotes de $lotes actualizado !Exitosamente!  \n");
        echo end_time($start_time);
    }
}
get_permalink($page_links);
//Alerta de posibles errores en la actualiacion deproductos
if( count($no_encontrado) > 0 ){
    $cadena = '';
    foreach($no_encontrado as $array){
        foreach($array as $valor){
            $cadena .= $valor.', ';
        }
    }
    echo sendEmail($cadena);
}
echo "➜ !Proceso finalizado exitosamente! \n";
