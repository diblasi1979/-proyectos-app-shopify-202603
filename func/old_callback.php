<?php
error_reporting(E_ALL & ~E_WARNING);

require '../vendor/autoload.php';
require '../db/cnx.php';
require 'funtions.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$shop = $_GET['shop'] ?? '';
$code = $_GET['code'] ?? '';
$dotenv->load();

$url_store = $shop;
if (!$shop || !$code) {
    die("Parámetros inválidos.");
} 




$access_token_url = "https://{$url_store}/admin/oauth/access_token";

$response = file_get_contents($access_token_url, false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json",
        'content' => json_encode([
            'client_id'     => $_ENV['SHOPIFY_API_KEY'],
            'client_secret' => $_ENV['SHOPIFY_API_SECRET'],
            'code'          => $code
        ])
    ]
]));

$data = json_decode($response, true);


try {
    $insertedId = insertlogs($pdo, 'sf_logs', $data);
}
catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    //echo "\n-------------------------\n";
}

$access_token = $data['access_token'] ?? '';


if (!$access_token) {
    die("No se pudo obtener el token de acceso.");
    //echo "\n-------------------------\n";
}

            else{


            // Configurar el modo de error de PDO para excepciones
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $fechaHoraActual = date('Y-m-d H:i:s');

            $query = "UPDATE sf_dash.sf_stores 
                    SET `token-store` = :token_store, 
                        `updated` = :updated 
                    WHERE `url-store` = :url_store";

            $stmt = $pdo->prepare($query);

            // Enlazar parámetros correctamente
            $stmt->bindParam(':token_store', $access_token, PDO::PARAM_STR);
            $stmt->bindParam(':updated', $fechaHoraActual, PDO::PARAM_STR);
            $stmt->bindParam(':url_store', $url_store, PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt->execute();
           
            //file_put_contents("../tokens/{$shop}.txt", $access_token);


/************************INICIO LA CREACION DEL SERVICIO DE CARRIER ***************************** */

$url = "https://{$shop}/admin/api/2024-07/carrier_services.json";
$psdurl = $_ENV['SHOPIFY_RATE_URL'];
$psdurl = "https://rate.requestcatcher.com/";  //uRL donde va a ir a buscar cotizaciones posteriormente

//var_dump($psdurl);

$data = [
    "carrier_service" => [
        //"id"                    => 1036894980,
        "name"                  => "IFLOW S.A.",
        "callback_url"          => $psdurl,
        "carrier_service_type"  => "api",
        //"admin_graphql_api_id"  => "gid://shopify/DeliveryCarrierService/1036894980",
        "service_discovery"     => true,
        "format"                => "json"
    ]
];

//var_dump($data); exit;
/****************************************** */
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-Shopify-Shop-Domain: $shop",
    "Content-Type: application/json",
    "X-Shopify-Access-Token: $access_token",
    "X-Shopify-API-Version: 2024-07",
]);

$response = curl_exec($ch);

curl_close($ch);


/******************************************** */


$data_sc = json_decode($response, true);


var_dump($data_sc); // veo la respuesta de la ejecucion del post 


try {
    $insertedId = insertlogs($pdo,'sf_logs', $data_sc);
}
catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
   
}


$carrier_id = $data_sc["carrier_service"]["id"];

$webhookUrl = $_ENV['SHOPIFY_HOOK_URL']; // url de la creacion de la orden 

$data = [
    "webhook" => [
        "topic" => "orders/paid",  // la debe enviar cuando la orden este paga
        "address" => $webhookUrl,
        "format" => "json"
    ]
];

$ch = curl_init("https://$shop/admin/api/2024-07/webhooks.json");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Shopify-Access-Token: $access_token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Deberia capturar el codigo de respuesta del post 
                                                    // se podria cambiar por guardar la respuesta completa en el log
$insertedId = insertlogs($pdo,'sf_logs', json_encode($response, true));


}

curl_close($ch);
    



?>