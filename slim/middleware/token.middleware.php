<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

// Middleware para verificar el token
$tokenValidationMiddleware = function (Request $request, RequestHandler $handler) use ($pdo): Response {
    $authHeader = $request->getHeader('Authorization');
    $token = str_replace('Bearer ', '', $authHeader[0] ?? '');

    if (!$token) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Verificar el token
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE token = ? AND vencimiento_token > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Token invalido o caducado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Adjuntar user data 
    $request = $request->withAttribute('user', $user);
    
    // Call next middleware
    return $handler->handle($request);
};
