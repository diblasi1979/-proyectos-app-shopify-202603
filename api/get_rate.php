<?php
error_reporting(E_ALL & ~E_WARNING);
//use Dotenv\Dotenv;

//$dotenv = Dotenv::createImmutable(__DIR__);
//$dotenv->load();

require '../func/funtions.php';
require '../db/cnx.php';

$fichero = '..\logs\CTZ';
$fechalog = gmDate("Y-m-d\TH:i:s");
//$LOG_TRACE = $_ENV['LOG_TRACE'] ;
$LOG_TRACE = "1";


// Logica que recibe la peticion de SF
//var_dump("Llego !! "); exit;

if($_SERVER['request_method'] == 'POST' or true )
  {
	
    $input = json_decode(file_get_contents("php://input"), true);
    
    //creo un log de recepcion de pedidos de cotizacion
    
    $infoin = $input;

    //var_dump($infoin); exit;
    // De ser necesario puedo habilitar el log
    
    
    $company = $infoin["rate"]["origin"]["company_name"];

    if ($LOG_TRACE = "1"){
    file_put_contents($fichero ."-" .$company .".log", json_encode($fechalog."-" .json_encode($infoin, true), true) ."\n",  FILE_APPEND | LOCK_EX);}

    $valor ="'%$company%'";
    // Busco en base de datos el usuario y clave de API con el nombre de la compania
    $consulta = $pdo->prepare("SELECT `usr-wms`, `psw-wms` FROM `sf_stores` WHERE `usr-store` like $valor ");
    $consulta->execute();
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    $UsuarioAPI = $resultados[0]["usr-wms"] ;
    $ClaveAPI = $resultados[0]["psw-wms"];

    //Con los datos de API creo el token 
    $token = tokenapi($UsuarioAPI,$ClaveAPI);

    //var_dump($token);

    //ARMO EL ARRAY PARA ENCIAR A COTIZAR
    $gross_price= $infoin["rate"]["items"]["price"];
    $real_weight= $infoin["rate"]["items"]["grams"];
    $real_weight = $infoin["rate"]["items"]["grams"];
    $maxwidth= 1;

    $cotizar["packages"][0]["width"]= $maxwidth;
    $cotizar["packages"][0]["height"]=$real_weight;
    $cotizar["packages"][0]["length"]=1;
    $cotizar["packages"][0]["real_weight"]=$real_weight;
    $cotizar["packages"][0]["gross_price"]=$gross_price;
    $cotizar["zip_code"]=$infoin["rate"]["destination"]["postal_code"];
    $cotizar["province"]=$infoin["rate"]["destination"]["province"];
    $cotizar["delivery_mode"]= 1;


    $COTjson = json_encode($cotizar);

    // ENVIO LOS DATOS A COTIZAR 

    $cotizado = rateapi($token,$COTjson);

   
    // Con los datos recibidos de API armo el array de respuesta a SF

    $RateS["rates"][0]["service_name"] = "IFLOW S.A.";
    $RateS["rates"][0]["service_code"] = "ON";
    $RateS["rates"][0]["total_price"] = round($cotizado["results"]["final_value"],2)*100;
    $RateS["rates"][0]["description"] = "Entrega a Domicilio por IFLOW S.A."; 
    $RateS["rates"][0]["currency"] = "ARS"; 
    $RateS["rates"][0]["min_delivery_date"] = $cotizado["results"]["min_delivery_date"];
    $RateS["rates"][0]["max_delivery_date"] = $cotizado["results"]["max_delivery_date"];

    $Resp = json_encode($RateS);

    header('Content-Type: application/json');
    http_response_code(200);
    echo($Resp);

 
}


die();
