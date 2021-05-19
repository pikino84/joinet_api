<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");

function products(){
    global $dbh;
    $sql = "SELECT Linea, Descrip FROM lineas;";
    $result = $dbh->query($sql);
    $categories = array();
    while($row = $result->fetch()) {
        $short_name = trim($row["Linea"]);
        $long_name  = trim($row["Descrip"]);
        $categories[] = array('short_name' => $short_name, 'long_name' => $long_name);
    }
	return $categories;
}
$response = products();
echo json_encode($response);
?>
