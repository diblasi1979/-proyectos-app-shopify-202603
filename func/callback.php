<?php


declare(strict_types=1);

error_reporting(E_ALL);

require '../vendor/autoload.php';
require '../db/cnx.php';
require 'funtions.php';


use Dotenv\Dotenv;

/* ================================
   CARGA DE VARIABLES DE ENTORNO
================================ */
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey    = $_ENV['SHOPIFY_API_KEY'];
$apiSecret = $_ENV['SHOPIFY_API_SECRET'];

/* ================================
   VALIDACIÓN BÁSICA DE PARÁMETROS
================================ */
$params = $_GET;

if (!isset($params['shop'], $params['code'], $params['hmac'], $params['state'])) {
    http_response_code(400);
    exit('Missing required parameters.');
}

$shop  = $params['shop'];
$code  = $params['code'];
$hmac  = $params['hmac'];
$state = $params['state'];

/* ================================
   VALIDAR STATE (ANTI-CSRF)
================================ */
//session_start();

if ($_ENV['APP_ENV'] !== 'local') {
    if (!isset($_SESSION['shopify_state']) || $state !== $_SESSION['shopify_state']) {
        http_response_code(403);
        exit('Invalido state.');
    }
}


/* ================================
   VALIDAR HMAC (SEGURIDAD CRÍTICA)
================================ */
unset($params['hmac']);

ksort($params);

$queryString = http_build_query($params);

$calculatedHmac = hash_hmac('sha256', $queryString, $apiSecret);

if (!hash_equals($hmac, $calculatedHmac)) {
    http_response_code(403);
    exit('Invalid HMAC.');
}

/* ================================
   INTERCAMBIAR CODE POR TOKEN
================================ */
$tokenUrl = "https://{$shop}/admin/oauth/access_token";

$ch = curl_init($tokenUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'client_id'     => $apiKey,
        'client_secret' => $apiSecret,
        'code'          => $code
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false || $httpCode !== 200) {
    http_response_code(500);
    exit('Failed to retrieve access token.');
}

curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['access_token'])) {
    http_response_code(500);
    exit('Access token not found.');
}

$accessToken = $data['access_token'];

/* ================================
   GUARDAR TOKEN EN BASE DE DATOS
================================ */
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        UPDATE sf_dash.sf_stores
        SET `token-store` = :token,
            `updated` = NOW()
        WHERE `url-store` = :shop
    ");

    $stmt->execute([
        ':token' => $accessToken,
        ':shop'  => $shop
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error.');
}

/* ================================
   REGISTRAR WEBHOOK app/uninstalled
================================ */
$webhookData = [
    "webhook" => [
        "topic"   => "app/uninstalled",
        "address" => $_ENV['SHOPIFY_UNINSTALL_WEBHOOK'],
        "format"  => "json"
    ]
];

$ch1 = curl_init("https://{$shop}/admin/api/2026-01/webhooks.json");

curl_setopt_array($ch1, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($webhookData),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        
        "X-Shopify-Access-Token: {$accessToken}"
    ],
]);

//curl_exec($ch1);
$response1 = curl_exec($ch1);
curl_close($ch1);



try {
    $insertedId = insertlogs($pdo, 'sf_logs', json_decode($response1) );
    
}
catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
     echo "\n-------------------------\n";
}



/* ================================
   CREAR EL SERVICIO DE CARRIER
================================ */
$webhookData = [
    "carrier_service" => [
        // "id"                    => 1036894980,
        "name"                  => "iFLOW eCOMMERCE S.A.",
        "callback_url"          =>  $_ENV['SHOPIFY_RATE_URL'],
        "carrier_service_type"  => "api",
        //"admin_graphql_api_id"  => "gid://shopify/DeliveryCarrierService/1036894980",
        "service_discovery"     => true,
        "format"                => "json" ]
		
];

$ch2 = curl_init("https://{$shop}/admin/api/2026-01/carrier_services.json");

curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($webhookData),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: {$accessToken}"
    ],
]);

//curl_exec($ch2);
 $response2 = curl_exec($ch2);
curl_close($ch2);


try {
    $insertedId = insertlogs($pdo, 'sf_logs', json_decode($response2) );
}
catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    //echo "\n-------------------------\n";
}


/* ================================
   WEBHOOK DE CREACION DE ORDEN
================================ */
$webhookData = [
        "webhook" => [
            "topics" => "orders/updated",  // la debe enviar cuando la orden este paga
            "address" => $_ENV['SHOPIFY_HOOK_URL'],
            "format" => "json" ]
		
];

$ch3 = curl_init("https://$shop/admin/api/2026-01/webhooks.json");

curl_setopt_array($ch3, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($webhookData),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: {$access_token}"],
        
     ]);

//curl_exec($ch2);
 $response3 = curl_exec($ch3);
curl_close($ch3);


try {
    $insertedId = insertlogs($pdo, 'sf_logs', json_decode($response3) );
}
catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    //echo "\n-------------------------\n";
}


/* ================================
   LIMPIAR SESSION STATE
================================ */
unset($_SESSION['shopify_state']);

/* ================================
   REDIRIGIR A DASHBOARD
================================ */
header("Location: ../dashboard.php?shop={$shop}");

exit;