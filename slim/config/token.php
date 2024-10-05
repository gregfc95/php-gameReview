<?php
function generarToken($userId) {
    // Crea un token único basado en el ID del usuario y la hora actual
    $token = hash('sha256', $userId . time());
    return $token;
}