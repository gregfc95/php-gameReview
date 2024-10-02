<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../middleware/auth.middleware.php';
require_once __DIR__ . '/../middleware/admin.middleware.php';
//Traer PDO
$pdo = require_once __DIR__ . '/../config/connect.db.php';

$app = AppFactory::create();

// POST /calificacion: Crear una nueva calificación
$app->post('/calificacion', function (Request $request, Response $response) use ($pdo) {

    // Obtener el userId del token desde el middleware
    $userId = $request->getAttribute('userId');

    $data = $request->getParsedBody();
    $juegoId = $data['juego_id'];
    $puntuacion = $data['puntuacion'];

    // Insertar la nueva calificación en la base de datos
    $stmt = $pdo->prepare("INSERT INTO calificaciones (usuario_id, juego_id, puntuacion) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $juegoId, $puntuacion]);

    $response->getBody()->write(json_encode(['status' => 'Calificación creada']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

})->add($authMiddleware);  // Añadir el middleware de autenticación

// PUT /calificacion/{id}: Editar una calificación existente
$app->put('/calificacion/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    // Obtener el userId del token desde el middleware
    $userId = $request->getAttribute('userId');
    $calificacionId = $args['id'];

    // Obtener la calificación actual
    $stmt = $pdo->prepare("SELECT usuario_id FROM calificaciones WHERE id = ?");
    $stmt->execute([$calificacionId]);
    $calificacion = $stmt->fetch();

    // Verificar si la calificación pertenece al usuario autenticado
    if ($calificacion['usuario_id'] !== $userId) {
        $response->getBody()->write(json_encode(['error' => 'No autorizado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Actualizar la calificación
    $data = $request->getParsedBody();
    $puntuacion = $data['puntuacion'];

    $stmt = $pdo->prepare("UPDATE calificaciones SET puntuacion = ? WHERE id = ?");
    $stmt->execute([$puntuacion, $calificacionId]);

    $response->getBody()->write(json_encode(['status' => 'Calificación actualizada']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

})->add($authMiddleware);  // Añadir el middleware de autenticación


//Borrar calificacion Delete
// DELETE /calificacion/{id}: Eliminar una calificación existente
$app->delete('/calificacion/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    // Obtener el userId del token desde el middleware
    $userId = $request->getAttribute('userId');
    $calificacionId = $args['id'];

    // Obtener la calificación actual
    $stmt = $pdo->prepare("SELECT usuario_id FROM calificaciones WHERE id = ?");
    $stmt->execute([$calificacionId]);
    $calificacion = $stmt->fetch();

    // Verificar si la calificación pertenece al usuario autenticado
    if ($calificacion['usuario_id'] !== $userId) {
        $response->getBody()->write(json_encode(['error' => 'No autorizado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Eliminar la calificación
    $stmt = $pdo->prepare("DELETE FROM calificaciones WHERE id = ?");
    $stmt->execute([$calificacionId]);

    $response->getBody()->write(json_encode(['status' => 'Calificación eliminada']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

})->add($authMiddleware);  // Añadir el middleware de autenticación
