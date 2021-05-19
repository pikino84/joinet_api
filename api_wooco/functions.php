<?php
use Automattic\WooCommerce\Client;
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//Nuymero de items por lote
$items_by_lote = 100;
function get_permalink($array){
    require __DIR__ . '/_functions/con_sql_corona.php';
    if(count($array) > 0 ){
        foreach($array as $sku => $url){
            $sql = "UPDATE prods SET imagen = '$url' WHERE ARTICULO = '$sku'";
            $dbh->query($sql);
        }
    }
}
function products_woocommerce(){
    require __DIR__ . '/vendor/autoload.php';
    // Conexión WooCommerce API destino
    // ================================
    $url_API_woo = 'https://joinet.com/';
    $ck_API_woo = 'ck_1b97c8e55de58296d792f150cbeb987f0097fa34';
    $cs_API_woo = 'cs_159bb5346697bd2acce4641021b86f78eace4455';
    $woocommerce = new Client(
        $url_API_woo,
        $ck_API_woo,
        $cs_API_woo,
        ['version' => 'wc/v3', 'verify_ssl' => false, 'timeout' => 400]
    );
    return $woocommerce;
}

function products_origen($endpoint = '', $params = ''){
    // Conexión API origen
    if($endpoint != ""){
        $endpoint = $endpoint.".php".$params;
    }
    $url_API="localhost/con_wc_mbpos/api_origen/".$endpoint; //LOCALHOST
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT,0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }
    return $items_origin;
}

function get_sku_MBPOS(){
    $url_API="http://localhost/products_json/"; //LOCALHOST
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }
    $items_origin = json_decode($items_origin, true);
    return $items_by_lote;
}

function productos_sin_imagen($endpoint = '' , $params = ''){
    // Conexión API origen
    if($endpoint != ""){
        $endpoint = $endpoint.".php".$params;
    }
    $url_API="localhost/con_wc_mbpos/api_origen/".$endpoint; //LOCALHOST
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT,150000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }
    return $items_origin;
}

function one_product_origen($endpoint = '', $params = ''){
    // Conexión API origen
    if($endpoint != ""){
        $endpoint = $endpoint.".php".$params;
    }
    $url_API="localhost/con_wc_mbpos/api_origen/".$endpoint; //LOCALHOST
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT,150000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }
    return $items_origin;
}

function get_categories_MBPOS(){
    $url_API="localhost/con_wc_mbpos/api_origen/categories.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT,150000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }
    return $items_origin;
}
function get_categories_woocommerce($conexion_woocommerce){
    $categories = $conexion_woocommerce->get('products/categories/?per_page=100');
    return $categories;
}

function get_brands_woocommerce($conexion_woocommerce){
    $brands = $conexion_woocommerce->get('brands');
    return $brands;
}
function get_brands_MBPOS(){
    $url_API="localhost/con_wc_mbpos/api_origen/brands.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT,150000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }
    return $items_origin;
}
function sendEmail($mensaje){
    //Load Composer's autoloader
    require 'vendor/autoload.php';

    //Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtpout.secureserver.net';             //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'web_developer@joinet.com';             //SMTP username
        $mail->Password   = 'Learsi01@@@@@';                        //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('web_developer@joinet.com');
        $mail->addAddress('jfcruz@outlook.com');     //Add a recipient
        $mail->addAddress('roxana.torres1220@gmail.com');    //Name is optional

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = "Joinet sistems validar sku's";
        $mail->Body    = 'Existe discrepancia en los siguientes codigos: <b>'.$mensaje.'</b>';
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $response =  'Message has been sent';
    } catch (Exception $e) {
        $response =  "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    return $response;
}
function download_imagen($link){
    //se obtiene el nombre de la imagen
    $img_name = basename($link);
    //se obtiene la imagende la web
    $imagen = file_get_contents($link);
    //Segenera la ruta temporal
    $path = "temp_img/$img_name";
    //Se pone la imagen en una carpeta temporal
    file_put_contents($path, $imagen);
    //Se obtiene el peso de la imagen EN Bytes
    $size = filesize($path);
    //Se convierten en Kilo Bytes
    $sizeKB = $size / 1024;
    if($sizeKB > 0){
        $destino = "C:\MyBusinessPOS20\images\Nuevos\Articulos\\$img_name";
        copy($path,$destino);
        unlink($path);
    }else{
        $destino = "C:\MyBusinessPOS20\images\Nuevos\Articulos\woocommerce-placeholder-85x85.png";
    }
    return $destino;
}
function start_time(){
    $time = microtime(true);
    return $time;
}
function end_time($start_time){
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    $hours = (int)($duration/60/60);
    $minutes = (int)($duration/60)-$hours*60;
    $seconds = (int)$duration-$hours*60*60-$minutes*60; 
    $response = "Tiempo transcurrido  $hours  horas,  $minutes  minutos y $seconds  segundos\n\n";
    return $response;
}
