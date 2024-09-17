<?php
/* use Firebase\JWT\JWT;
use Firebase\JWT\Key;
 */
//funcion que genera el token para el parametro userID
/* function generateToken($userId) {
    $key = $_ENV['JWT_SECRET']; //env variable
    $payload = [
        'iat' => time(), 
        'exp' => time() + 3600, // Token exp 1h
        'sub' => $userId
    ];
    return JWT::encode($payload, $key,'HS256');
} */

function generarToken($userId) {
    $fecha = date('Y-m-d H:i:s'); // Fecha actual
    $key = $_ENV['JWT_SECRET'];  // Llave secreta

    // Crear un string que combine el userId, la fecha y la clave secreta
    $data = $userId . '|' . $fecha;
    
    // Generar un hash HMAC con SHA-256 usando la clave secreta
    $token = hash_hmac('sha256', $data, $key);

    // Devolver el token generado
    return $token;
}

//No esta bien decodificar, hay que comparar llaves solamente
/* function decodeToken($token) {
    $key = $_ENV['JWT_SECRET']; // Ensure JWT_SECRET is defined in your .env
    try {
        return JWT::decode($token, new Key($key, 'HS256')); 
    } catch (Exception $e) {
        return null;
    }
} */