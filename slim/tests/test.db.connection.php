<?php
require_once __DIR__ . '/../config/connect.db.php';

if ($pdo instanceof PDO) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}