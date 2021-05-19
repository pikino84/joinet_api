<?php
$serverName = '26.220.158.225\MYBUSINESSPOS,53100';
$database = 'MyBusiness20';
$uid = 'sa';
$pwd = 'Computacionjoinet2020';
$opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
try {
    //Data Source Name
    $dsn = "sqlsrv:server=$serverName;Database=$database";
    //Database Handle
    $dbh = new PDO($dsn, $uid, $pwd, $opciones );
}
catch(PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}