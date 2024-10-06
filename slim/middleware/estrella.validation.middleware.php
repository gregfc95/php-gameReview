<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
// Middleware to validate estrellas - calificacion input
function validateEstrella(Request $request, RequestHandler $handler): Response {
    // Obtener los datos enviados en el cuerpo de la solicitud
    $data = $request->getParsedBody();

    // Verificar que 'estrellas' esté presente
    if (!isset($data['estrellas'])) {
        return createErrorResponse(400, 'Faltan datos: el campo "estrellas" es requerido.');
    }

    $estrellas = $data['estrellas'];

    // Validar que 'estrellas' esté en el rango correcto (1-5)
    if ($estrellas < 1 || $estrellas > 5) {
        return createErrorResponse(400, 'Las estrellas deben estar entre 1 y 5.');
    }

    // Si las validaciones pasan, continúa con el manejo de la solicitud
    return $handler->handle($request);
}