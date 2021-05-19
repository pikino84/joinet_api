<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");

function products(){
    global $dbh;
    $sql = "SELECT  ARTICULO FROM dbo.prods";
    $result = $dbh->query($sql);
    $skus = array();
    while($row = $result->fetch()) {
        $sku = trim($row["ARTICULO"]);
        array_push($skus, $sku);
    }
	return $skus;
}
$response = products();
echo json_encode($response);
?>
