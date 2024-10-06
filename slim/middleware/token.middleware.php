<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
//Refactorizar a container
global $pdo;

// Middleware para verificar el token
$tokenValidationMiddleware = function (Request $request, RequestHandler $handler) use ($pdo): Response {
    $authHeader = $request->getHeader('Authorization');
    
    $token = str_replace('Bearer ', '', $authHeader[0] ?? '');

    if (!$token) {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
    try {
        // Verificar el token
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE token = ? AND vencimiento_token > NOW()");
        $stmt->execute([$token]);
        
        // Fetch user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(['error' => 'Token invalido o caducado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
     //   var_dump($user); // Outputs the user data
        // Adjuntar user data 
        $request = $request->withAttribute('user', $user);
     //   var_dump($request->getAttribute('user')); // Should output the user data


    } catch (PDOException $e) {
        // Log the error message and return a 500 response
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // Call next middleware
    return $handler->handle($request);
};
