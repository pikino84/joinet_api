<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS,PUT');//accede desde diferentes verbos https
include("_functions/core.php");

function images(){
    global $dbh;
    //https://joinet.com/wp-content/uploads/2020/09/732-1.jpg

    //https://joinet.com/wp-content/uploads/2021/01/732-1.jpg

    

    
	return $response;
}
$response = images();
echo json_encode($response);
