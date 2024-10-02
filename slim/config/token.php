<?php
function generarToken($userId) {
    $fecha = date('Y-m-d H:i:s'); // Fecha actual
    $key = $_ENV['JWT_SECRET'];  // Llave secreta

    // Crear un string que combine el userId, la fecha y la clave secreta
    $data = $userId . '|' . $fecha;

    // Generar un string aleatorio usando random_bytes para mayor seguridad
    $randomData = random_bytes(64); // 64 bytes * 2 (hexadecimal) = 128 caracteres
    $token = bin2hex($randomData);

    // Devolver el token limitado a 128 caracteres
    return $token;
}


// Función para decodificar el token
function decodeToken($token) {
    // Obtener la clave secreta desde el archivo .env
    $key = $_ENV['JWT_SECRET'];

    // Decodificar el token para extraer los datos
    $data = explode('|', base64_decode($token));
    if (count($data) !== 2) {
        return null; // Formato de token incorrecto
    }

    $userId = $data[0];    // ID del usuario
    $fechaToken = $data[1]; // Fecha del token

    // Verificar que el token no haya expirado
    $expTime = strtotime($fechaToken) + 3600; // Token expira en 1 hora
    if (time() > $expTime) {
        return null; // Token expirado
    }

    // Verificar que el token es válido usando la misma clave secreta
    $validToken = hash_hmac('sha256', $userId . '|' . $fechaToken, $key);

    if ($token === $validToken) {
        return $userId; // El token es válido, devolver el userId
    }

    return null; // Token inválido
}