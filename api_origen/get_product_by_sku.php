<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");

function products($sku){
    global $dbh;
    $sql = "SELECT p.ARTICULO, 
                   p.DESCRIP AS DESCRIP,
                   p.claveprodserv AS clave_sat, 
                   p.COSTO AS COSTO, 
                   p.COSTO_U AS costo_ultimo, 
                   p.impuesto AS impuesto,
                   p.unidad AS unidad, 
                   p.claveunidad AS claveunidad, 
                   p.barcode1 AS barcode1, 
                   p.barcode2 AS barcode2,  
                   m.Descrip AS marca,
                   pr.Proveedor AS proveedor,
                   l.Descrip AS linea, 
                   PRECIO1 AS PRECIO1, 
                   PRECIO2 AS PRECIO2, 
                   C2 AS C2, 
                   PRECIO3 AS PRECIO3, 
                   C3 AS C3, 
                   PRECIO4 AS PRECIO4, 
                   C4 AS C4, 
                   PRECIO5 AS PRECIO5, 
                   C5 AS C5, 
                   PRECIO6 AS PRECIO6, 
                   p.C6 AS C6, 
                   p.URL AS url, 
                   c.Clave AS bar_code, 
                   p.Bloqueado AS Bloqueado, 
                   p.BloqueadoWooCommerce AS BloqueadoWooCommerce,
                (SELECT existencia FROM existenciaalmacen WHERE articulo = p.ARTICULO AND almacen = 1) AS corona,
                (SELECT existencia FROM acrexistenciaremota WHERE Sucursal = 'S001Joinet' AND Articulo = p.ARTICULO) AS colon,
                (SELECT existencia FROM acrexistenciaremota WHERE Sucursal = 'S002LopezC' AND Articulo = p.ARTICULO) AS cotilla,
                p.ACROferta AS in_offer,
                p.ACRPrecioOfertaWeb AS price_offer
                FROM prods AS p
                LEFT JOIN marcas AS m ON m.Marca = p.MARCA
                LEFT JOIN lineas AS l ON l.Linea = p.LINEA
                LEFT JOIN provprod AS pr ON pr.Articulo = p.ARTICULO
                LEFT JOIN clavesadd AS c ON c.Articulo = p.ARTICULO
                WHERE p.ARTICULO = '$sku'";
    $result = $dbh->query($sql);
    $all_products = array();
    while($row = $result->fetch()) {
        $sku =  trim($row['ARTICULO']);
        $in_offer = $row['in_offer'];
        $regular_price = $row['price_offer'];
        $price1 = round($row['PRECIO1'] * 1.16 , 0);
        $price2 = round($row['PRECIO2'] * 1.16 , 0);
        $price3 = round($row['PRECIO3'] * 1.16 , 0);
        $price4 = round($row['PRECIO4'] * 1.16 , 0);
        $price5 = round($row['PRECIO5'] * 1.16 , 0);
        $price6 = round($row['PRECIO6'] * 1.16 , 0);
        $costo = round($row['COSTO'] * 1.16 , 0);
        $costo_ultimo = round($row['costo_ultimo'] * 1.16 , 0);
        $c2 =  round($row['C2'], 0);
        $c3 =  round($row['C3'], 0);
        $c4 =  round($row['C4'], 0);
        $c5 =  round($row['C5'], 0);
        $c6 =  round($row['C6'], 0);
        $marca = trim($row['marca']);
        $categoria = trim($row['linea']);
        $cat_slug = createLink($categoria);
        $qty = round($row['colon'], 0);
        $bloqueado = trim($row['Bloqueado']);
        $url = $row['url'];
        $marca = $row['marca'];
        $all_products = array(
            'sku' => $sku,
            'qty' => $qty,
            'name' => $row["DESCRIP"],
            'marca' => $marca,
            'categoria' => $categoria,
            'slug' => $cat_slug,
            'in_offer' => $in_offer,
            'regular_price' => $regular_price,
            'price' => $price1,
            'price2' => $price2,
            'price3' => $price3,
            'price4' => $price4,
            'price5' => $price5,
            'price6' => $price6,
            'c2' => $c2,
            'c3' => $c3,
            'c4' => $c4,
            'c5' => $c5,
            'c6' => $c6,
            'bloqueado' => $bloqueado,
            'no_show_wc' => $row['BloqueadoWooCommerce'],
            'MBPOS_CATIDAD_CAJA' => $c5,
            'MBPOS_CLAVE_SAT' => $row['clave_sat'],
            'MBPOS_CODIGO_BARRAS' => $row['bar_code'],
            'MBPOS_COSTO' => $costo,
            'MBPOS_COSTO_ULTIMO' => $costo_ultimo,
            'MBPOS_IMPUESTO' => $row['impuesto'],
            'MBPOS_NOMBRE_PROVEEDOR' => $row['proveedor'],
            'MBPOS_UNIDAD' => $row['unidad'],
            'MBPOS_UNIDAD_SAT' => $row['claveunidad'],
            'MBPOS_STOCK_DOS' => 0,
            'MBPOS_CODIGO_UNO' => $row['barcode1'],
            'MBPOS_CODIGO_DOS' => $row['barcode2'],
            'MBPOS_STOCK_CORONA' => round($row['corona'],0),
            'MBPOS_STOCK_COLON' => round($row['corona'],0),
            'MBPOS_STOCK_COTILLA' => round($row['cotilla'],0),
            'url' => $url,
        );
    }
	return $all_products;
}

if(isset( $_GET['sku'] )){
    $sku	   = $_GET['sku'];
	$response  = products($sku);
}else{
	header('HTTP/1.1 405 Method Not Allowed');
	exit;
}
echo json_encode($response);