<?php
session_start();

// Configuration IONOS MySQL
define('DB_HOST', 'db5019663454.hosting-data.io');
define('DB_NAME', 'dbs15302347');
define('DB_USER', 'dbu3869441');
define('DB_PASS', 'Oliv2001!@');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

include_once(__DIR__ . "/functions.php");
?>