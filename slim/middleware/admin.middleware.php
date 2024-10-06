<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
// pdo global refactorizar a container
global $pdo;

$checkAdmin = function (Request $request, RequestHandler $handler) use($pdo): Response {
    // Obtener el userId del middleware token
    $user = $request->getAttribute('user');

    if (!$user || !isset($user['id'])) {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => 'Acceso denegado: usuario no autenticado.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Extraer el userId
    $userId = $user['id'];

    try {
        // Query a la db, check si el user es admin
        $stmt = $pdo->prepare("SELECT es_admin FROM usuario WHERE id = ?");
        $stmt->execute([$userId]);
        $userRecord = $stmt->fetch();

        // Check si el user es admin (es_admin = 1)
        if ($userRecord && $userRecord['es_admin'] === 1) {
            return $handler->handle($request);
        }
    } catch (PDOException $e) {
        // Handle any db error
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // Si no es admin, return a 401 
    $response = new SlimResponse();
    $response->getBody()->write(json_encode(['error' => 'Acceso denegado: se requiere rol de administrador.']));
    return $response
        ->withStatus(401) 
        ->withHeader('Content-Type', 'application/json');
};