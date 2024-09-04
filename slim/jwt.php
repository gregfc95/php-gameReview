<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
function generateToken($userId) {
    $key = $_ENV['JWT_SECRET']; //env variable
    $payload = [
        'iat' => time(),
        'exp' => time() + 3600, // Token exp 1h
        'sub' => $userId
    ];
    return JWT::encode($payload, $key,'HS256');
}

function decodeToken($token) {
    $key = $_ENV['JWT_SECRET']; // Ensure JWT_SECRET is defined in your .env
    try {
        return JWT::decode($token, new Key($key, 'HS256')); 
    } catch (Exception $e) {
        return null;
    }
}