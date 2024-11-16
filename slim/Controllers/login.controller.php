<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//Traer PDO
//$pdo = require_once __DIR__ . '/../config/connect.db.php';
require_once __DIR__ . '/../helpers/pdo.helper.php';
require_once __DIR__ . '/../config/token.php';

//Refactorizar a container
global $pdo;

//route validation-token
$app->get('/validar-token', function(Request $request, Response $response) use ($pdo) {

    $user = $request->getAttribute('user');

    // Chqueamos si el user esta presente
    if ($user) {
        // Si existe, el token es valido
        $response->getBody()->write(json_encode(['message' => 'Token valido', 'nombre_usuario' => $user['nombre_usuario'], 'es_admin' => $user['es_admin'], 'vencimiento_token' => $user['vencimiento_token'] ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        // Token es invalido
        $response->getBody()->write(json_encode(['error' => 'Token no valido']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

})->add($tokenValidationMiddleware);


//route login 
$app->post('/login', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();

    //Comprobar PDO
    $responseCheck = checkDatabaseConnection($pdo, $response);
    if ($responseCheck) {
        return $responseCheck;
    }

    //Refactoring in middleware or Helper    
    // Validación básica: Verificar que los campos estén presentes
    if (!isset($data['nombre_usuario']) || !isset($data['clave'])) {
        $response->getBody()->write(json_encode(['error' => 'Faltan datos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    } 
 
    $username = $data['nombre_usuario'] ?? '';
    $password = $data['clave'] ?? '';


    try {
        // Buscar el usuario en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario no existe o la contraseña no es correcta, devolver 401 Unauthorized
        if (!$user || substr(md5($password), 0, 16) !== $user['clave']) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales invalidas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        // Llamar a la función que ya tienes para generar el token
        $token = generarToken($user['id']);

        // Actualizar la base de datos con el token y su fecha de vencimiento
        $stmt = $pdo->prepare("UPDATE usuario SET token = ?, vencimiento_token = ? WHERE id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', time() + 3600), $user['id']]);
        
        //Checkear timezone
        //echo date_default_timezone_get();

        // Devolver el token en la respuesta
        $response->getBody()->write(json_encode(['token' => $token, 'vencimiento_token' => $user['vencimiento_token'], 'es_admin' => $user['es_admin'], 'nombre_usuario' => $user['nombre_usuario']]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Si hay un error con la consulta o la base de datos, devolver estado 500 (Internal Server Error)
        $response->getBody()->write(json_encode(['error' => 'Error de base de datos', 'details' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


//Registro
$app->post('/register', function (Request $request, Response $response) use ($pdo) {

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

        // Insertar el nuevo usuario
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
