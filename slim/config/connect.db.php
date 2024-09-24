<?php
require 'vendor/autoload.php'; // Make sure to include this to load Composer dependencies
//fetch .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));//load dotenv
$dotenv->load();
// pdo instance and attributes
try {
    $pdo = new PDO(
        "mysql:host=host.docker.internal;dbname=" . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'Connection succesfully';
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

?>