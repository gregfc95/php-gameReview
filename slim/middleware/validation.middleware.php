<?php

// validation.middleware.php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

function validateUserInput(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    // Obtener los datos enviados en el cuerpo de la solicitud
    $data = $request->getParsedBody();

    // Validación básica: Verificar que los campos estén presentes
    if (!isset($data['nombre_usuario']) || !isset($data['clave'])) {
        return createErrorResponse(400, 'Faltan datos');
    }

    $username = $data['nombre_usuario'];
    $password = $data['clave'];

    // Validar nombre de usuario: alfanumérico, entre 6 y 20 caracteres
    if (!preg_match('/^[a-zA-Z0-9]{6,20}$/', $username)) {
        return createErrorResponse(400, 'El nombre de usuario debe tener entre 6 y 20 caracteres y solo puede contener caracteres alfanumericos.');
    }

    // Validar la clave: mínimo 8 caracteres, mayúsculas, minúsculas, números y caracteres especiales
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        return createErrorResponse(400, 'La password debe tener al menos 8 caracteres, con mayusculas, minusculas, numeros y caracteres especiales.');
    }

    // Si las validaciones pasan, continua con el manejo de la solicitud
    return $handler->handle($request);
}

// Helper para crear una respuesta de error
function createErrorResponse(int $status, string $message): ResponseInterface {
    $response = new Response();
    $response->getBody()->write(json_encode(['error' => $message]));
    
    return $response->withStatus($status)
                    ->withHeader('Content-Type', 'application/json');
}
