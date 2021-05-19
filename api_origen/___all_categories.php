<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");

function products(){
    global $dbh;
    /*$sql = "SELECT p.ARTICULO, l.Descrip 
            FROM dbo.lineas AS l
            LEFT JOIN prods AS p ON p.LINEA = l.Linea";*/
    $sql = "SELECT Descrip FROM dbo.lineas";
    $result = $dbh->query($sql);
    $categorias = array();
    while($row = $result->fetch()) {
        $categorias[] = array(
            'categoria' => $row['Descrip'],
        );
    }
	return $categorias;
}
$response = products();
echo json_encode($response);
?>
