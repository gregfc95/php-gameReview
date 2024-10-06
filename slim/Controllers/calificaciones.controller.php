<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

//refactorizar a container
global $pdo;

require_once __DIR__ . '/../helpers/pdo.helper.php';
require_once __DIR__ . '/../config/token.php';


//Solo usuario logeado
// POST /calificacion: Crear una nueva calificación
$app->post('/calificacion', function (Request $request, Response $response) use ($pdo) {
    // Obtener el user del token desde el middleware
    $user = $request->getAttribute('user');
    $userId = $user['id']; // Extraer el userId

    try {
        $data = $request->getParsedBody();
        $juegoId = $data['juego_id'] ?? null;
        $estrellas = $data['estrellas'] ?? null;

        // Verificar si ya existe una calificación para el mismo juego por parte del mismo usuario
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM calificacion WHERE usuario_id = ? AND juego_id = ?");
        $stmt->execute([$userId, $juegoId]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $response->getBody()->write(json_encode(['error' => 'Ya has calificado este juego']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Insertar la nueva calificación en la base de datos
        $stmt = $pdo->prepare("INSERT INTO calificacion (usuario_id, juego_id, estrellas) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $juegoId, $estrellas]);


        $response->getBody()->write(json_encode(['status' => 'Calificacion creada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Manejo de errores de la base de datos
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Manejo de errores generales
        $response->getBody()->write(json_encode(['error' => 'Ocurrio un error inesperado: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
})->add($tokenValidationMiddleware)->add('validateEstrella'); //Last in, first OUT!




// PUT /calificacion/{id}: Editar una calificación existente
$app->put('/calificacion/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    // Obtener el user del token desde el middleware
    $user = $request->getAttribute('user');
    $userId = $user['id']; // Extraer el userId
    $calificacionId = $args['id'];

    try {
        // Obtener la calificación actual
        $stmt = $pdo->prepare("SELECT usuario_id FROM calificacion WHERE id = ?");
        $stmt->execute([$calificacionId]);
        $calificacion = $stmt->fetch();

        // Verificar si la calificación existe
        if (!$calificacion) {
            return createErrorResponse(404, 'Calificacion no encontrada');
        }

        // Verificar si la calificación pertenece al usuario autenticado
        if ($calificacion['usuario_id'] !== $userId) {
            return createErrorResponse(401, 'No autorizado');
        }

        // Obtener los datos de la solicitud
        $data = $request->getParsedBody();
        $estrellas = $data['estrellas'];

        // Actualizar la calificación
        $stmt = $pdo->prepare("UPDATE calificacion SET estrellas = ? WHERE id = ?");
        $stmt->execute([$estrellas, $calificacionId]);
        $response->getBody()->write(json_encode(['status' => 'Calificacion actualizada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        return createErrorResponse(500, 'Error en la base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        return createErrorResponse(500, 'Ocurrió un error inesperado: ' . $e->getMessage());
    }

})->add($tokenValidationMiddleware)->add('validateEstrella'); //Last in, first OUT!


//Borrar calificacion Delete
// DELETE /calificacion/{id}: Eliminar una calificación existente
$app->delete('/calificacion/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    // Obtener el user del token desde el middleware
    $user = $request->getAttribute('user');
    $userId = $user['id']; // Extraer el userId
    $calificacionId = $args['id'];

    try {
        // Obtener la calificación actual
        $stmt = $pdo->prepare("SELECT usuario_id FROM calificacion WHERE id = ?");
        $stmt->execute([$calificacionId]);
        $calificacion = $stmt->fetch();

        // Verificar si la calificación existe
        if (!$calificacion) {
            $response->getBody()->write(json_encode(['error' => 'Calificacion no encontrada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Verificar si la calificación pertenece al usuario autenticado
        if ($calificacion['usuario_id'] !== $userId) {
            $response->getBody()->write(json_encode(['error' => 'No autorizado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Eliminar la calificación
        $stmt = $pdo->prepare("DELETE FROM calificacion WHERE id = ?");
        $stmt->execute([$calificacionId]);

        $response->getBody()->write(json_encode(['status' => 'Calificacion eliminada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        
    } catch (PDOException $e) {
        // Manejo de errores de la base de datos
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Manejo de errores generales
        $response->getBody()->write(json_encode(['error' => 'Ocurrio un error inesperado: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

})->add($tokenValidationMiddleware); //Last in, first OUT!
