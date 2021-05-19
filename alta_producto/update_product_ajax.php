<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$heror = '';
$message = '';
$link = '';
$link_msj =  '';
if(isset( $_POST['sku']) && $_POST['sku'] != "" && $_POST['sku'] != '0'){
    $sku = htmlspecialchars (trim($_POST['sku']));
    require '../api_wooco/vendor/autoload.php';
    require '../api_wooco/functions.php';
    // Obteniendo la informacion del prodeucto desde la API de origen
    $end_point = "get_product_by_sku";
    $params = "?sku=".$sku;
    $product_origen = one_product_origen($end_point, $params);
    $product_info = json_decode($product_origen, true);
    //SE VEFIFICA EL PRODUCTO EXISTE EN MBPOS
    if( !empty($product_info)){
        // Conexión WooCommerce API WC
        $woocommerce = products_woocommerce();
        // Comprobando si existe el producto en WC
        $product = $woocommerce->get('products/?sku='. $sku);
        $product = json_encode($product);
        $product = json_decode($product, true);
        //SE OBTIENEN LAS CATEGORIAS DE WOOCOMMERCE (nombre y id)
        $categories_woocommerce = get_categories_woocommerce($woocommerce);
        $array_categories = array();
        foreach($categories_woocommerce as $category){
            $array_categories[$category->name] = $category->id;
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
            $brand_woo_name = trim($brand_woo->name);
            $array_brands[$brand_woo_name] = $brand_woo->term_id;
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
        $marca_id = $array_brands[trim($product_info['marca'])];

        //SE OBTEIEN EL ID DE LA CATEGORIA PARA EL PORODUCTO A AGREGAR O ACTUALIZAR
        $categoria_id = $array_categories[$product_info['categoria']];
        //OCULTA PRODUCTOS BLOQUEADOS O QUE NO SE QUIEREN MOSTRAR EN EL SITIO WEB
        if( $product_info['bloqueado'] == 1 ||  $product_info['no_show_wc'] == 1){
            $visibility = "hidden";
        }else{
            $visibility = "visible";
        }
        //APLICA PARA LOS ARTICULOS EN OFERTA
        if( $product_info['in_offer'] && $product_info['regular_price'] > $product_info['price'] ){
            $price = $product_info['price'];
            $regular_price = $product_info['regular_price'];
            $sale_price = $product_info['price'];
        }else{
            $price = $product_info['price'];
            $regular_price = $product_info['price'];
            $sale_price = $product_info['price'];
        }
        $stock_corona  = 0;
        $stock_colon   = 0;
        $stock_cotilla = 0;
        $total_stock   = 0;
        $total_stock   = 0;
        $stock_corona  = $product_info['MBPOS_STOCK_CORONA'];
        $stock_colon   = $product_info['MBPOS_STOCK_COLON'];
        $stock_cotilla = $product_info['MBPOS_STOCK_COTILLA'];
        $total_stock   = $stock_corona + $stock_colon + $stock_cotilla;
        $item_data     = array();
        //SE VERIFICA SI EL PRODUCTO ESXITE EN WOOCOMMERCE PARA ACTULIZAR LO DE LO CONTRARIO SE DA DE ALTA
        if(!empty($product)){
            $item_data[] = array(
                'id' => $product[0]['id'],
                "price" => $price,
                'regular_price' => $regular_price,
                'sale_price' => $sale_price,
                'stock_quantity' => $total_stock,
                'name' => $product_info['name'],
                'catalog_visibility' => $visibility,
                'status' => 'publish',
                'manage_stock' => true,
                'brands' => $marca_id,
                'meta_data' => array([
                    'key' => "_fixed_price_rules",
                    'value' => array(
                        $product_info['c2'] => $product_info['price2'],
                        $product_info['c3'] => $product_info['price3'],
                        $product_info['c4'] => $product_info['price4'],
                        $product_info['c5'] => $product_info['price5'],
                        $product_info['c6'] => $product_info['price6'],
                    ),
                ],
                [
                    'key' => "MBPOS_CATIDAD_CAJA",
                    'value' => $product_info['MBPOS_CATIDAD_CAJA']
                ],
                [
                    'key' => "MBPOS_CLAVE_SAT",
                    'value' => $product_info['MBPOS_CLAVE_SAT']
                ],
                [
                    'key' => "MBPOS_CODIGO_BARRAS",
                    'value' => $product_info['MBPOS_CODIGO_BARRAS']
                ],
                [
                    'key' => "MBPOS_COSTO_ULTIMO",
                    'value' => $product_info['MBPOS_COSTO_ULTIMO']
                ],
                [
                    'key' => "MBPOS_NOMBRE_PROVEEDOR",
                    'value' => $product_info['MBPOS_NOMBRE_PROVEEDOR']
                ],
                [  
                    'key' => "MBPOS_UNIDAD",
                    'value' => $product_info['MBPOS_UNIDAD']

                ],
                [
                    'key' => "MBPOS_CODIGO_UNO",
                    'value' => $product_info['MBPOS_CODIGO_UNO']
                ],
                [
                    'key' => "MBPOS_CODIGO_DOS",
                    'value' => $product_info['MBPOS_CODIGO_DOS']
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
            $data = [
                'update' => $item_data,
            ];
            // Actualización
            $result = $woocommerce->post('products/batch', $data);
            //obtenemos el link del producto
            $link = $result->update[0]->permalink;
            $link_msj = 'Aquí puedes revisar el árticulo con SKU '.$sku;
            if (! $result) {
                $message = "Error al actualizar";
            } else {
                $message = "Actualizado !Exitosamente!";
            }
        }else{
            $data['create'][] = array(
                'sku' => $product_info['sku'],
                'regular_price' => $product_info['price'],
                'stock_quantity' => $product_info['qty'],
                'name' => $product_info['name'],
                'catalog_visibility' => $visibility,
                'status' => 'publish',
                'manage_stock' => true,
                'brands' => $marca_id,
                'meta_data' => array([
                    'key' => "_fixed_price_rules",
                    'value' => array(
                        $product_info['c2'] => $product_info['price2'],
                        $product_info['c3'] => $product_info['price3'],
                        $product_info['c4'] => $product_info['price4'],
                        $product_info['c5'] => $product_info['price5'],
                        $product_info['c6'] => $product_info['price6'],
                    ),
                ],
                [
                    'key' => "MBPOS_CATIDAD_CAJA",
                    'value' => $product_info['MBPOS_CATIDAD_CAJA']
                ],
                [
                    'key' => "MBPOS_CLAVE_SAT",
                    'value' => $product_info['MBPOS_CLAVE_SAT']
                ],
                [
                    'key' => "MBPOS_CODIGO_BARRAS",
                    'value' => $product_info['MBPOS_CODIGO_BARRAS']
                ],
                [
                    'key' => "MBPOS_COSTO_ULTIMO",
                    'value' => $product_info['MBPOS_COSTO_ULTIMO']
                ],
                [
                    'key' => "MBPOS_NOMBRE_PROVEEDOR",
                    'value' => $product_info['MBPOS_NOMBRE_PROVEEDOR']
                ],
                [  
                    'key' => "MBPOS_UNIDAD",
                    'value' => $product_info['MBPOS_UNIDAD']
    
                ],
                [
                    'key' => "MBPOS_CODIGO_UNO",
                    'value' => $product_info['MBPOS_CODIGO_UNO']
                ],
                [
                    'key' => "MBPOS_CODIGO_DOS",
                    'value' => $product_info['MBPOS_CODIGO_DOS']
                ],
                [
                    'key' => "MBPOS_STOCK_CORONA",
                    'value' => $product_info['MBPOS_STOCK_CORONA']
                ],
                [
                    'key' => "MBPOS_STOCK_COTILLA",
                    'value' => $product_info['MBPOS_STOCK_COTILLA']
                ]),
                'categories' => array([
                    'id' => $categoria_id
                ])
            );
            $result = $woocommerce->post('products/batch', $data);
            /*echo "<pre>";
            print_r($result);
            echo "</pre>";*/
            //obtenemos el link del producto
            $link = $result->create[0]->permalink;
            $link_msj = 'Aquí puedes revisar el árticulo con SKU '.$sku;
            if (! $result) {
                $message = "Error al actualizar";
            } else {
                $message = "Agregado !Exitosamente!";
            }
        }//END !empty($product)
    }else{
        $heror = "SE REQUIERE PRIMERO DAR DE ALTA EN My Business POS";
    }//END !empty($product_info)
}else{
   $heror = "SKU no valido";
}
echo $response = json_encode( array('error' => $heror, 'message' => $message, 'link' => $link, 'link_msj' => $link_msj) );

