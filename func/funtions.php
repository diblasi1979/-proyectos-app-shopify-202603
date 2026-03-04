<?php
require '../vendor/autoload.php';
require '../db/cnx.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function insertlogs($pdo, $table, $data) {
    try {
        // Construir la consulta SQL dinámicamente
        $columns = json_encode($data);

        $sql = "INSERT INTO $table (`msg_log`) VALUES ('$columns') " ;

        //var_dump($sql); 
        //exit;

        // Preparar la consulta
        $stmt = $pdo->prepare($sql);
        
        // Ejecutar la consulta con los valores proporcionados
        //$stmt->execute(array_values($data));
        $stmt->execute();
        return $pdo->lastInsertId(); // Retorna el ID del último registro insertado
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

function tokenapi ($usrwms, $pswwms){
                    
                    
                    $url = $_ENV['WMS_URL_BASE'];
                    $url = $url .'/api/login';
                    $usrapi =$usrwms;
                    $clvapi = $pswwms;
                    
                    //var_dump ($url);
                    //var_dump ($usrapi);  
                    //var_dump ($clvapi); 
                    

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => "$url",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS =>"{\"_username\":\"$usrapi\",\"_password\":\"$clvapi\"}",
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "Cookie: SERVERID=api_iflow21"
                    ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $arraypsd = json_decode($response,true);
                     
                    //var_dump ($arraypsd);

                    $tokeapi= $arraypsd['token'];
                     
                    return $tokeapi;


}

function rateapi ($token, $jsondata) {

    $tokeapi =$token;
    $rate = $jsondata;

    $url = $_ENV['WMS_URL_BASE'];
    $url = $url .'/api/rate';
    
    $curl = curl_init();

                  curl_setopt_array($curl, array(
                    CURLOPT_URL => "$url",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS =>"$rate",
                    CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer $tokeapi",
                    "Cookie: SERVERID=api_iflow21"
                  ),
                    ));

                  $response = curl_exec($curl);

                  


                  $rateapi = json_decode($response, true);
                  curl_close($curl);
                  return $rateapi;

}


function OrderCreateapi ($token, $ORDjson) { 

    $url = $_ENV['WMS_URL_BASE'];
    $url = $url .'/api/order/create';
  
    $tokeapi = $token;
    $ORDjson = $ORDjson;
  
    $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "$url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>"$ORDjson",
        CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json",
        "Authorization: Bearer $tokeapi",
        "Cookie: SERVERID=api_iflow21"
      ),
        ));
  
      $response = curl_exec($curl);
  
      curl_close($curl);
  
        return $response;






}

