<?php
use Psr\Http\Message\ResponseInterface as Response;

function checkDatabaseConnection($pdo, Response $response) {
    if (!$pdo instanceof PDO) {
        $response->getBody()->write(json_encode(['error' => 'Error de conexion a la base de datos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
    return null; // Return null si conexion es correcta
}