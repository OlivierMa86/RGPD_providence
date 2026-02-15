<?php
session_start();

// --- Chargement du fichier .env ---
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die("ERREUR CRITIQUE : Le fichier .env est introuvable. Créez-le à la racine du projet avec les variables de configuration.");
}
$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($envLines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#')
        continue;
    if (strpos($line, '=') === false)
        continue;
    list($key, $value) = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($value);
}

// Configuration MySQL (depuis .env)
define('DB_HOST', $_ENV['DB_HOST'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// API Keys (depuis .env)
$geminiKeys = [];
for ($i = 1; $i <= 10; $i++) {
    if (!empty($_ENV["GEMINI_KEY_$i"])) {
        $geminiKeys[] = $_ENV["GEMINI_KEY_$i"];
    }
}
define('SECRET_GEMINI_KEYS', $geminiKeys);
define('SECRET_MISTRAL_KEY', $_ENV['MISTRAL_KEY'] ?? '');
define('SECRET_GROQ_KEY', $_ENV['GROQ_KEY'] ?? '');
define('SECRET_OPENROUTER_KEY', $_ENV['OPENROUTER_KEY'] ?? '');

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

// Sécurité : Masquer les erreurs en production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

include_once(__DIR__ . "/functions.php");
?>