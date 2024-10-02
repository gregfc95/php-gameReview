<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

//Conexion DB
require __DIR__ . '/config/connect.db.php';
//Endpoints (Controllers)
require __DIR__ . '/controllers/calificaciones.controller.php';
require __DIR__ . '/controllers/juegos.controller.php';
require __DIR__ . '/controllers/login.controller.php';
require __DIR__ . '/controllers/usuarios.controller.php';


// Create App
$app = AppFactory::create();
$app->addBodyParsingMiddleware(); // Middleware for JSON body parsing
$app->addRoutingMiddleware();     // Routing Middleware



// CORS Middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json');
});

// Error Middleware 
$app->addErrorMiddleware(true, true, true);


//Correr App
$app->run();