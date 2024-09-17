<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add( function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json')
    ;
});

// ACÁ VAN LOS ENDPOINTS
/* Usuarios
● POST /usuario: Crear un nuevo usuario.
● PUT /usuario/{id}: Editar un usuario existente.
● DELETE /usuario/{id}: Eliminar un usuario.
● GET /usuario/{id}: Obtener información de un usuario específico.
○ En los últimos 3 casos (donde se recibe el id) se debe validar que el
usuario se haya logueado */

//Usuarios
$app->post('/usuario', function(Request $request, Response $response) use($pdo){
    $data = $request ->getParsedBody();
    $username = $data['nombre_usuario'];
    $password = password_hash($data['clave'],PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT * FROM usuario WHERE nombre_usuario = ?');
    $stmt-> execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);



})

$app->run();