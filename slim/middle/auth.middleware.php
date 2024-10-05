<?php
$pdo = require_once __DIR__ . '/../config/connect.db.php';

$authMiddleware = function ($request, $handler) use ($pdo) {
    $token = $request->getHeaderLine('Authorization'); // Obtener el token del encabezado Authorization

    if (!$token) {
        // Si no se envió un token
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Verificar el token en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user && strtotime($user['vencimiento_token']) > time()) {
        // Si el token es válido y no ha expirado, proceder con la petición
        $request = $request->withAttribute('user', $user); // Pasar el usuario al siguiente middleware o controlador
        return $handler->handle($request);
    } else {
        // Token inválido o expirado
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Token inválido o expirado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
};
