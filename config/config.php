<?php
session_start();

require_once(__DIR__ . '/../functions/activity.php');
require_once(__DIR__ . '/../functions/auth.php');
require_once(__DIR__ . '/../functions/redirect.php');
require_once(__DIR__ . '/../functions/session.php');
require_once(__DIR__ . '/../functions/csrf.php');
require_once(__DIR__ . '/../functions/csv.php');

// 1. Require Composer Autoload
require_once(__DIR__ . '/../vendor/autoload.php');

// 2. Load .env from the root directory (one level up from config/)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

define('BASE_URL','http://localhost/it34-boilerplate');

// 3. Define constants using the .env variables, with fallbacks just in case
define('DB_HOST', $_ENV['DB_HOST'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ??'');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? ''); // Note: DB_PASS constant, but DB_PASSWORD in .env

try{
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}catch(PDOException $e){
    die("Connection failed: " . $e->getMessage());
}
?>