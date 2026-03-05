<?php
session_start();

require '../vendor/autoload.php';
require '../db/cnx.php';
require 'funtions.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();



/* ====================================
   SOLO PROCESAR POST
==================================== */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

/* ====================================
   RECIBIR Y VALIDAR DATOS
==================================== */
$url_store = trim($_POST['url_store'] ?? '');
$usr_wms   = trim($_POST['usr_wms'] ?? '');
$psw_wms   = trim($_POST['psw_wms'] ?? '');
$cuit_store = trim($_POST['cuit_store'] ?? '');

if (!$url_store) {
    exit("Falta el nombre de la tienda.");
}

/* Normalizar dominio */
$url_store = preg_replace('/https?:\/\//', '', $url_store);
$url_store = rtrim($url_store, '/');

if (!preg_match('/\.myshopify\.com$/', $url_store)) {
    exit("Dominio inválido.");
}

/* ====================================
   GUARDAR TIENDA EN DB
==================================== */
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO sf_stores (
            `usr-store`,
            `url-store`,
            `usr-wms`,
            `psw-wms`,
            `cuit-store`
        ) VALUES (
            :usr_store,
            :url_store,
            :usr_wms,
            :psw_wms,
            :cuit_store
        )
    ");

    $stmt->execute([
        ':usr_store'  => $url_store,
        ':url_store'  => $url_store,
        ':usr_wms'    => $usr_wms,
        ':psw_wms'    => $psw_wms,
        ':cuit_store' => $cuit_store
    ]);

} catch (PDOException $e) {
    exit("Error al guardar tienda.");
    $insertedId = insertlogs($pdo, 'sf_error', json_decode($e) );
}   



/* ====================================
   GENERAR STATE (ANTI-CSRF)
==================================== */
$state = bin2hex(random_bytes(16));
$_SESSION['shopify_state'] = $state;



/* ====================================
   REDIRECCIÓN A SHOPIFY
==================================== */
$install_url = "https://{$url_store}/admin/oauth/authorize?" . http_build_query([
    'client_id'    => $_ENV['SHOPIFY_API_KEY'],
    'scope'        => 'read_orders,write_orders,write_shipping,read_shipping',
    'redirect_uri' => $_ENV['SHOPIFY_REDIRECT_URI'],
    'state'        => $_SESSION['shopify_state'],
]);


    $insertedId = insertlogs($pdo, 'sf_error', $install_url );


header("Location: $install_url");
exit;