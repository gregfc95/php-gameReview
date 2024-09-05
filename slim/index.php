<?php 
use \Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require 'pdo.php';
require 'jwt.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//Middleware para CORS y JSON
$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json');
});

// ACÁ VAN LOS ENDPOINTS
//Login
$app->post('/login', function(Request $request, Response $response) use($pdo){
    $data = $request->getParsedBody();
    $username = $data['nombre_usuario'];
    $password = $data['clave'];

    $stmt = $pdo->prepare('SELECT * FROM usuario WHERE nombre_usuario = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['clave'])) {
        $token = generateToken($user['id']);

        // Actualiza el token en la base de datos
        $stmt = $pdo->prepare("INSERT INTO tokens (id, token, expiration) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', time() + 3600)]);


        // Create response with JSON data
        $response->getBody()->write(json_encode(['token' => $token]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
      
});

//Register
$app->post('/register', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $username = $data['nombre_usuario'];
    $password = password_hash($data['clave'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO usuario (nombre_usuario, clave) VALUES (?, ?)");
    $stmt->execute([$username, $password]);

    $response->getBody()->write(json_encode(['status' => 'User creado']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// Middleware chequea la autorizacion
$checkAuth = function (Request $request, RequestHandler $handler): Response {
    $response = new \Slim\Psr7\Response(); // Crear un nuevo Response para uso en el middleware

    $token = $request->getHeaderLine('Authorization');
    if (!$token) {
        $response->getBody()->write(json_encode(['error' => 'No token provided']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $decoded = decodeToken($token);
    if (!$decoded) {
        $response->getBody()->write(json_encode(['error' => 'Invalid token']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $request = $request->withAttribute('user_id', $decoded->sub);
    return $handler->handle($request);
};




$app->run();
?>