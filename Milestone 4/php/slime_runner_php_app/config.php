<?php
// config.php - central DB connection and session start

$DB_HOST = 'localhost';
$DB_NAME = 'slime_runner_db';
$DB_USER = 'slime_app';
$DB_PASS = 'slime_app_password';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    exit("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


