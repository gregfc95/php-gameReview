<?php

// Middleware para verificar si es administrador
$adminMiddleware = function ($request, $handler) use ($pdo) {
    $userId = $request->getAttribute('userId');

    $stmt = $pdo->prepare("SELECT es_admin FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user && $user['es_admin'] === 1 ) {
        return $handler->handle($request);
    }

    $response = new \Slim\Psr7\Response();
    return $response->withStatus(403)->withHeader('Content-Type', 'application/json')
                    ->getBody()->write(json_encode(['error' => 'Acceso denegado']));
};
