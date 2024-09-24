<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
require_once __DIR__ . '/../Middleware/auth.middleware.php';

$app = AppFactory::create();

//Crear Usuario POST
$app->post('/usuario',function(Request $request, Response $response) use($pdo){

    $data = $request->getParsedBody();

   $username = $data['nombre_usuario'];

    try {
        // Insertar el usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre_usuario, clave) VALUES (?, ?)");
        
        if ($stmt->execute([$username])){
            // Si todo sale bien, devolver estado 201 (Created)
            $response->getBody()->write(json_encode(['status' => 'Usuario creado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        }

    } catch (PDOException $e) {
       // Si hay un error con la consulta, devolver estado 500 (Internal Server Error)
       $response->getBody()->write(json_encode(['error' => 'Error al crear el usuario', 'details' => $e->getMessage()]));
       return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
   }
});


//Editar Usuario Put
$app->put('/usuario/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    // Obtener el id del usuario desde los parámetros de la ruta
    $id = $args['id'];
    
    // Obtener el usuario autenticado desde el middleware
    $authenticatedUser = $request->getAttribute('user');
      // Verificar si el usuario autenticado tiene permiso para editar
      if ($authenticatedUser['id'] != $id) {
        $response->getBody()->write(json_encode(['error' => 'No autorizado para modificar este usuario']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    // Obtener los datos enviados en el cuerpo de la solicitud
    $data = $request->getParsedBody();
    $username = $data['nombre_usuario'] ?? null;
    $password = isset($data['clave']) ? password_hash($data['clave'], PASSWORD_BCRYPT) : null;

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
})->add($authMiddleware); // Añadir el middleware de autenticación



//Borrar Usuario Delete
$app->delete('/usuario/{id}',function(Request $request, Response $response, array $args) use($pdo){
    // Obtener el id del usuario desde los parámetros de la ruta
    $id = $args['id'];
    
    // Obtener el usuario autenticado desde el middleware
    $authenticatedUser = $request->getAttribute('user');

    // Verificar si el usuario autenticado tiene permiso para eliminar
    if ($authenticatedUser['id'] != $id) {
        $response->getBody()->write(json_encode(['error' => 'No autorizado para eliminar este usuario']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
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
})->add($authMiddleware); // Añadir el middleware de autenticación






    //Obtener Usuario get
$app->get('/usuario/{id}',function(Request $request, Response $response, array $args) use($pdo){
    $id = $args['id'];
      // Obtener el usuario autenticado desde el middleware
    $user = $request->getAttribute('user');

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
})->add($authMiddleware);