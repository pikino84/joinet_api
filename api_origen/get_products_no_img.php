<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");

function products(){
    global $dbh;
    //$sql = "SELECT ARTICULO FROM prods WHERE URL = 'https://joinet.com/wp-content/uploads/woocommerce-placeholder.png'";
    $sql = "SELECT ARTICULO FROM prods";
    $result = $dbh->query($sql);
    $all_products = array();
    while($row = $result->fetch()) {
        $sku =  trim($row['ARTICULO']);
        $all_products[] = array(
            'sku' => $sku,
        );
    }
	return $all_products;
}
$response = products();
echo json_encode($response);
?>
