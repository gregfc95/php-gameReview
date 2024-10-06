<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
// Middleware to validate juego input
function validateJuego(Request $request, RequestHandler $handler): Response {
    // Obtener los datos enviados en el cuerpo de la solicitud
    $data = $request->getParsedBody();

    // Validación básica: Verificar que los campos estén presentes
    if (!isset($data['nombre']) || !isset($data['clasificacion_edad'])) {
        return createErrorResponse(400, 'Faltan datos');
    }

    $nombre = $data['nombre'];
    $clasificacion_edad = $data['clasificacion_edad'];

    // Validar longitud del nombre: máximo 45 caracteres
    if (strlen($nombre) > 45) {
        return createErrorResponse(400, 'El nombre del juego no puede tener mas de 45 caracteres.');
    }

    // Validar clasificación de edad
    $validAgeRatings = ['ATP', '+13', '+18'];
    if (!in_array($clasificacion_edad, $validAgeRatings)) {
        return createErrorResponse(400, 'La clasificacion de edad debe ser ATP, +13, o +18.');
    }

    // Si las validaciones pasan, continúa con el manejo de la solicitud
    return $handler->handle($request);
}