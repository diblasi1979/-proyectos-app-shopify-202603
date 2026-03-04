<?php
$shop = 'zeusintegra.myshopify.com';
$access_token = file_get_contents("tokens/{$shop}.txt");


use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$SHOPIFY_RATE_URL = $_ENV['SHOPIFY_RATE_URL'];


/********** ARMO Y ENVIO LA CREACION DEL SERVICIO DE CARRIER   *******************/
$url = "https://{$shop}/admin/api/2025-01/carrier_services.json";

$data = [
    "carrier_service" => [
        "name"                  => "IFLOW S.A.",
        "callback_url"          => $SHOPIFY_RATE_URL,
        "service_discovery"     => true,
        "format"                => "json"
    ]
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => [
            "Content-Type: application/json",
            "X-Shopify-Access-Token: $access_token"
        ],
        'content' => json_encode($data)
    ]
];


//$response = file_get_contents($url, false, stream_context_create($options));
// esta era una alternativa al Curl

/******************************************************** */
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Shopify-Access-Token: $access_token"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

//echo "HTTP Code: $http_code\n";
//echo "Response: $response\n";


/******************************************************** */
echo "Carrier Service creado: $response\n";
echo "Token usado: " . $access_token;

/*********************************************************** */

// Configura la URL de la API de Shopify
$api_url = "https://{$shop}/admin/api/2025-01/shipping_methods.json";

$data2 = [
    'shipping_zone_id' => '1', // Reemplaza con la ID de la zona de envío
    'name' => 'Iflow_Starndar',
    'price' => '10.00', // Reemplaza con el precio del medio de envío
    'tax_code' => 'standard', // Puedes ajustar esto según tus necesidades
];

// Configura las opciones de la solicitud
$options = [
    'http' => [
        'header' => [
            "Content-type: application/json",
            "X-Shopify-Access-Token: $access_token",
        ],
        'method' => 'POST',
        'content' => json_encode(['shipping_method' => $shipping_method_data]),
    ],
];

// Realiza la solicitud para crear el medio de envío


$ch1 = curl_init();

curl_setopt($ch1, CURLOPT_URL, $api_url);

curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Shopify-Access-Token: $access_token"
]);
curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode($data2));

$response = curl_exec($ch1);
$http_code = curl_getinfo($ch1, CURLINFO_HTTP_CODE);

curl_close($ch1);


echo "Response: $response\n";












$context = stream_context_create($options);
$result = file_get_contents("$api_url", false, $context);



?>
