<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");

function brands(){
    global $dbh;
    $sql = "SELECT * FROM marcas WHERE Marca != 'SYS'";
    $result = $dbh->query($sql);
    $brands = array();
    while($row = $result->fetch()) {
        $short_name = trim($row["Marca"]);
        $long_name  = trim($row["Descrip"]);
        $brands[] = array('short_name' => $short_name, 'long_name' => $long_name);
    }
	return $brands;
}
$response = brands();
echo json_encode($response);
?>
