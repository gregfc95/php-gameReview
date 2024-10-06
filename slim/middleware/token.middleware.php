<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

// Middleware to validate token and set user in request attribute
$tokenValidationMiddleware = function (Request $request, RequestHandler $handler) use ($pdo): Response {
    $authHeader = $request->getHeader('Authorization');
    $token = str_replace('Bearer ', '', $authHeader[0] ?? '');

    if (!$token) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Verify the token in the database
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE token = ? AND vencimiento_token > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Token inválido o caducado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Attach the user data to the request for use in other routes
    $request = $request->withAttribute('user', $user);
    
    // Call the next middleware or route handler
    return $handler->handle($request);
};
