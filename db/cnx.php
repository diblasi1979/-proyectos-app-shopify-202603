<?php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$username = $_ENV['USER_DB'] ?? '';
$password = $_ENV['PSW_DB'] ?? '';
$host =     $_ENV['HOST_DB'] ?? '';
$port =     $_ENV['PORT_DB'] ?? ''; 
$dbname=    $_ENV['DBNAME'] ?? ''; 

$dsn = 'mysql:host='.$host .';port=' .$port .';dbname=' .$dbname;
$options = [];
$pdo = new PDO($dsn, $username, $password, $options);

?>

