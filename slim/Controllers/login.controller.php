<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//route login 
$app->post('/login',function(Request $request, Response $response) use($pdo){
    $data = $request->getParsedBody();

   // Validación básica: Verificar que los campos estén presentes
   if (!isset($data['nombre_usuario']) || !isset($data['clave'])) {
    $response->getBody()->write(json_encode(['error' => 'Faltan datos']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
}

    $username = $data['nombre_usuario'];
    $password = $data['clave'];

    try {
        // Buscar el usuario en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Si el usuario no existe o la contraseña no es correcta, devolver 401 Unauthorized
        if (!$user || !password_verify($password, $user['clave'])) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales inválidas']));
        
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Llamar a la función que ya tienes para generar el token JWT
        $token = generarToken($user['id']); 

        // Actualizar la base de datos con el token y su fecha de vencimiento
        $stmt = $pdo->prepare("UPDATE usuario SET token = ?, vencimiento_token = ? WHERE id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', time() + 3600), $user['id']]);

        // Devolver el token en la respuesta
        $response->getBody()->write(json_encode(['token' => $token]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        // Si hay un error con la consulta o la base de datos, devolver estado 500 (Internal Server Error)
        $response->getBody()->write(json_encode(['error' => 'Error de base de datos', 'details' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



//Registro
$app->post('/register',function(Request $request, Response $response) use($pdo){

    $data = $request->getParsedBody();

   // Validación básica: Verificar que los campos estén presentes
   if (!isset($data['nombre_usuario']) || !isset($data['clave'])) {
    $response->getBody()->write(json_encode(['error' => 'Faltan datos']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
}

    $username = $data['nombre_usuario'];
    $password = password_hash($data['clave'], PASSWORD_BCRYPT);

    try {
        // Insertar el usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre_usuario, clave) VALUES (?, ?)");
        
        if ($stmt->execute([$username,$password])){
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

$app->run();