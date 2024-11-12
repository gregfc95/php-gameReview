<?php
require __DIR__ . '/../vendor/autoload.php'; // Make sure to include this to load Composer dependencies
//fetch .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2)); 
$dotenv->load();
// pdo instance and attributes
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=" . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    //Setear UTC timezone para la db sesion esto no
    //$pdo->exec("SET time_zone = '+00:00'");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage());
    die('Connection failed: ' . $e->getMessage());
}
return $pdo;
