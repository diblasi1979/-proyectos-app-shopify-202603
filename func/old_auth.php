<?php
require '../vendor/autoload.php';
require '../db/cnx.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();




if ($_SERVER["REQUEST_METHOD"] == "POST") {

$nonce = bin2hex(random_bytes(16));

$usr_wms = $_POST['usr_wms'];
$psw_wms = $_POST['psw_wms'];
$url_store =$_POST['url_store'];
$cuit_store = $_POST['cuit_store'];

// Configurar el modo de error de PDO para excepciones
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "INSERT INTO sf_stores (
                `usr-store`, `url-store`, `usr-wms`, `psw-wms`, `cuit-store`
            ) VALUES (
                :url_store, :url_store, :usr_wms, :psw_wms, :cuit_store
            )";

$stmt = $pdo->prepare($query);

// Corregir los nombres de parámetros en bindParam()
$stmt->bindParam(':usr_store', $usr_store, PDO::PARAM_STR);
$stmt->bindParam(':url_store', $url_store, PDO::PARAM_STR);
$stmt->bindParam(':usr_wms', $usr_wms, PDO::PARAM_STR);
$stmt->bindParam(':psw_wms', $psw_wms, PDO::PARAM_STR);
$stmt->bindParam(':cuit_store', $cuit_store, PDO::PARAM_STR);

// Ejecutar la consulta
$stmt->execute();
}

$params = $_GET;

if (!$url_store) {
    die("Falta el nombre de la tienda.");
} else {

/**************************************** */


$install_url = "https://{$url_store}/admin/oauth/authorize?" . http_build_query([
        'client_id'    => $_ENV['SHOPIFY_API_KEY'],
        'scope'        => $_ENV['SHOPIFY_SCOPES'],
        'redirect_uri' => $_ENV['SHOPIFY_REDIRECT_URI'],
        'state' => $nonce 
    ]);
    
    header("Location: $install_url");
    exit;
}


?>
