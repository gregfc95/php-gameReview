<?php
require_once 'vendor/autoload.php'; // Make sure to include this to load Composer dependencies
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


// pdo instance and attributes
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=" . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

?>