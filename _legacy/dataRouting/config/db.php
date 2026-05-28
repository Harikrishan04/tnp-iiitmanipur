<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// $logFile = __DIR__ . '/../../logs/db_handler_debug.log';
// if (!is_dir(dirname($logFile))) {
//     mkdir(dirname($logFile), 0755, true);
// }
// function authDebugLog($msg) {
//     global $logFile;
//     $time = date('Y-m-d H:i:s');
//     if (is_array($msg) || is_object($msg)) {
//         $msg = print_r($msg, true);
//     }
//     file_put_contents($logFile, "[{$time}] {$msg}" . PHP_EOL, FILE_APPEND | LOCK_EX);
// }

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$port = $_ENV['DB_PORT'] ?? 3306;

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    //authDebugLog("helloooo");
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);
    return $pdo;

} catch (PDOException $e) {
    // Instead of echoing and exiting, throw an exception or return false
    error_log("DB connection failed: " . $e->getMessage());
    return false;  // Make sure your calling code handles this
}