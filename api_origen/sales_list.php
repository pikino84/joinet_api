<?php
header('Content-Type: application/json');//Habilita el formato JSON
header('Access-Control-Allow-Origin: *');//Habilita los permisoso para que ususarios de otros dominos puedan entrar
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');//accede desde diferentes verbos https
include("_functions/core.php");


function sales_list(){
    global $dbh;
    $sqlMar = "SELECT Marca, Descrip FROM marcas";
    $resMar = $dbh->query($sqlMar);
    $marcas = array();
    while($rowMar = $resMar->fetch()) {
        $id_marca = $rowMar['Marca'];
        $marca = $rowMar['Descrip'];
        $marcas[$id_marca] = $marca;
    }

    $sql = "SELECT 
                year(f.f_emision) AS ano,
                month(f.f_emision) AS mes ,
                P.Articulo,
                p.descrip,
                p.COSTO_U AS costo_ultimo,
                p.PRECIO1,
                p.U1,
                p.U2,
                p.U3,
                p.U4,
                p.U5,
                p.U6,
                p.PRECIO2,
                p.PRECIO3,
                p.PRECIO4,
                p.PRECIO5,
                p.PRECIO6,
                p.MARCA,
            isnull((SELECT SUM(cantidad) FROM partvta WHERE  isnull(tipo_doc,'REM') IN ('REM','DEV') AND year(usufecha)=year(f.f_emision) AND month(usufecha)= month(f.f_emision) AND articulo = P.Articulo),0) AS 'cantidad_vendida',
            (SELECT existencia FROM acrexistenciaremota WHERE Sucursal = 'S003Matriz' AND Articulo = p.Articulo) AS corona,
            p.EXISTENCIA AS colon,
            (SELECT existencia FROM acrexistenciaremota WHERE Sucursal = 'S002LopezC' AND Articulo = p.Articulo) AS cotilla,
            p.URL
            FROM prods P, ventas f 
            WHERE  1=1
            GROUP BY year(f.f_emision),month(f.f_emision),p.articulo,p.descrip, p.URL, p.COSTO_U, p.PRECIO1, p.U1, p.PRECIO2, p.U2, p.PRECIO3, p.U3, p.PRECIO4, p.U4, p.PRECIO5, p.U5, p.PRECIO6, p.U6, p.EXISTENCIA, p.MARCA
            ORDER BY year(f.f_emision),month(f.f_emision),p.articulo";
    $result = $dbh->query($sql);
    $periods = array();
    $sales = array();
    $sale =array();
    $group_sales = array();
    $meses = array("", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic" );
    while($row = $result->fetch()) {
        $year = $row['ano'];
        $month = $row['mes'];
        $sku = $row['Articulo'];
        $vendidas = round($row['cantidad_vendida'], 0);
        $descrip = $row['descrip'];
        $costo_ultimo = round($row['costo_ultimo'] * 1.16 , 0);
        $precio1 = round($row['PRECIO1'] * 1.16 , 0);
        $utilidad1 = round($row['U1'],2);
        $precio2 = round($row['PRECIO2'] * 1.16 , 0);
        $utilidad2 = round($row['U2'],2);
        $precio3 = round($row['PRECIO3'] * 1.16 , 0);
        $utilidad3 = round($row['U3'],2);
        $precio4 = round($row['PRECIO4'] * 1.16 , 0);
        $utilidad4 = round($row['U4'],2);
        $precio5 = round($row['PRECIO5'] * 1.16 , 0);
        $utilidad5 = round($row['U5'],2);
        $precio6 = round($row['PRECIO6'] * 1.16 , 0);
        $utilidad6 = round($row['U6'],2);
        $marca_key = $row['MARCA'];
        $stock_corona = ($row['corona'] != NULL )? round($row['corona'],0):0;
        $stock_colon = ($row['colon'] != NULL )? round($row['colon'],0):0;
        $stock_cotilla = ($row['cotilla'] != NULL )? round($row['cotilla'],0):0;
        $url = $row['URL'];

        $year = substr($year, -1);
        $period = $meses[$month].$year;
        if(!in_array( $period, $periods )){
            array_push($periods,$period);
        }
        if(array_key_exists($marca_key, $marcas)){
            $marca = $marcas[$marca_key];
        }

        
        $sales[] = array(
            'sku' => $sku,
            'vendidas' => $vendidas,
            'priodo_sale' => $period,
            'descrip' => $descrip,
            'costo_ultimo' => $costo_ultimo,
            'precio1' => $precio1,
            'utilidad1' => $utilidad1,
            'precio2' => $precio2,
            'utilidad2' => $utilidad2,
            'precio3' => $precio3,
            'utilidad3' => $utilidad3,
            'precio4' => $precio4,
            'utilidad4' => $utilidad4,
            'precio5' => $precio5,
            'utilidad5' => $utilidad5,
            'precio6' => $precio6,
            'utilidad6' => $utilidad6,
            'stock_corona' => $stock_corona,
            'stock_colon' => $stock_colon,
            'stock_cotilla' => $stock_cotilla,
            'marca' => $marca,
            'url' => $url,
        );
    }
    $cont_periodo = 0;
    foreach($periods as $period){
        foreach($sales as $sale){
            if($period == $sale['priodo_sale'] ){
                $group_sales[ $cont_periodo][] = $sale;
            }
        }
        $cont_periodo++;
    }
    
    $report['period_sale'] = $periods;
    $report['group_sales'] = $group_sales;
    $report = array("sales" => $report);
    return $report;
}
$response  = sales_list();

echo json_encode($response);