<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//Traer PDO esto no funciona, solo se puede del index
//$pdo = require_once __DIR__ . '/../config/connect.db.php';

//Global PDO, refactorizar a container luego
global $pdo;
require_once __DIR__ . '/../helpers/pdo.helper.php';


//Crear Usuario POST
$app->post('/usuario', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $username = $data['nombre_usuario'] ?? '';
    $password = $data['clave'] ?? '';

    try {
        // Verificar si el nombre de usuario ya está en uso
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$username]);
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists) {
            $response->getBody()->write(json_encode(['error' => 'El nombre de usuario ya esta en uso.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Encriptar la clave forzado a 16 caracteres
        $hashedPassword = substr(md5($password), 0, 16);

        // Insertar el usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre_usuario, clave) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);

        // Respuesta exitosa
        $response->getBody()->write(json_encode(['status' => 'Usuario creado con exito']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Si hay un error con la consulta, devolver estado 500 (Internal Server Error)
        $response->getBody()->write(json_encode(['error' => 'Error al crear el usuario', 'details' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
})->add('validateUserInput');


//Editar Usuario Put
$app->put('/usuario/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    // Obtener el id del usuario desde los parámetros de la ruta
    $id = $args['id'];

    // Obtener el usuario autenticado desde el middleware
    $authenticatedUser = $request->getAttribute('user');

    if ($authenticatedUser['id'] != $id) {
        $response->getBody()->write(json_encode(['error' => 'No autorizado para modificar este usuario']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Obtener los datos enviados en el cuerpo de la solicitud
    $data = $request->getParsedBody();
    $username = $data['nombre_usuario'] ?? null;
    $password = isset($data['clave']) ? substr(md5($data['clave']), 0, 16) : null;

    // Preparar la consulta de actualización
    $query = "UPDATE usuario SET nombre_usuario = ?, clave = ? WHERE id = ?";
    $stmt = $pdo->prepare($query);

    // Ejecutar la consulta con los nuevos valores
    try {
        $stmt->execute([$username, $password, $id]);
        $response->getBody()->write(json_encode(['status' => 'Usuario actualizado correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Error al actualizar el usuario']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
})->add($tokenValidationMiddleware);  // Añadir el middleware de autenticación



//Borrar Usuario Delete
$app->delete('/usuario/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    // Obtener el id del usuario desde los parámetros de la ruta
    $id = $args['id'];

    // Obtener el usuario autenticado desde el middleware
    $authenticatedUser = $request->getAttribute('user');

    // Verificar si el usuario autenticado tiene permiso para eliminar
    if ($authenticatedUser['id'] != $id) {
        $response->getBody()->write(json_encode(['error' => 'No autorizado para eliminar este usuario']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Preparar la consulta para eliminar el usuario
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");

    try {
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(['status' => 'Usuario eliminado correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Error al eliminar el usuario']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
})->add($tokenValidationMiddleware);  // Añadir el middleware de autenticación


// Obtener Usuario Get
$app->get('/usuario/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $id = $args['id'];

    // Obtener el usuario autenticado desde el middleware
    $user = $request->getAttribute('user');
    $userId = $user['id']; //extraer el userId

        // Verificar si el usuario autenticado es el mismo que el usuario
    if ($userId != $id) {
            return createErrorResponse(401, 'No autorizado');
    }

    // Obtener información del usuario por ID
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    $userInfo = $stmt->fetch();

    if ($userInfo) {
        $response->getBody()->write(json_encode($userInfo));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
})->add($tokenValidationMiddleware); // Añadir el middleware de autenticación