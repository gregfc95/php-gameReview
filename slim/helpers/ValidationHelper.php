<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;

class ValidationHelper {
    public static function validateCredentials($data, ResponseInterface $response) {
        if (!isset($data['nombre_usuario']) || !isset($data['clave'])) {
            $response->getBody()->write(json_encode(['error' => 'Faltan datos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        return null; //Si no hay error, retornar null
    }
}
