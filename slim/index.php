<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Slim\Factory\AppFactory;
// Traer PDO
$pdo = require_once __DIR__ . '/config/connect.db.php';
require_once __DIR__ . '/helpers/pdo.helper.php';
require __DIR__ . '/config/token.php';

//Load Composer
require __DIR__ . '/vendor/autoload.php';

//Middleware
require __DIR__ . '/middleware/token.middleware.php';  // Checkeo de vencimiento
require __DIR__ . '/middleware/validation.middleware.php';  // validaciones basicas
require __DIR__ . '/middleware/juego.validation.middleware.php';  // validaciones de juego
require __DIR__ . '/middleware/admin.middleware.php';  // Middleware para admin

// Create App
$app = AppFactory::create();
$app->setBasePath('/php-gameReview/slim');


$app->addBodyParsingMiddleware();   // Middleware for JSON body parsing
$app->addRoutingMiddleware();   // Routing Middleware

$app->addErrorMiddleware(true, true, true); // Error Middleware 

// CORS Middleware
    /* $app->add(function (Request $request, $handler) {
        $response = $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
            ->withHeader('Content-Type', 'application/json');
    }); */

//Endpoints (Controllers)
//require __DIR__ . '/controllers/calificaciones.controller.php';
require __DIR__ . '/controllers/juegos.controller.php';
require __DIR__ . '/controllers/login.controller.php';
require __DIR__ . '/controllers/usuarios.controller.php';

//Tests
require __DIR__ . '/tests/controller.test.php';

//Correr App
$app->run();