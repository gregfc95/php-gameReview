<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
// Middleware to validate juego input
function validateJuego(Request $request, RequestHandler $handler): Response
{
    // Obtener los datos enviados en el cuerpo de la solicitud
    $data = $request->getParsedBody();

    // Validación básica: Verificar que los campos estén presentes
    //Correcion descrición no puede estar vacia
    if (!isset($data['nombre']) || !isset($data['clasificacion_edad']) || !isset($data['descripcion']) || !isset($data['plataforma'])) {
        return createErrorResponse(400, 'Faltan datos');
    }

    $nombre = $data['nombre'];
    $clasificacion_edad = $data['clasificacion_edad'];
    $descripcion = $data['descripcion']; //Correcion descricion no puede estar vacia
    $plataformas = $data['plataforma']; //Correcion plataformas no puede estar vacia
    // Validar longitud del nombre: máximo 45 caracteres
    if (strlen($nombre) > 45) {
        return createErrorResponse(400, 'El nombre del juego no puede tener mas de 45 caracteres.');
    }

    // Validar clasificación de edad
    $validAgeRatings = ['ATP', '+13', '+18'];
    if (!in_array($clasificacion_edad, $validAgeRatings)) {
        return createErrorResponse(400, 'La clasificacion de edad debe ser ATP, +13, o +18.');
    }
    // Correcion, validar que la descripción no esté vacía
    if (empty(trim($descripcion))) {
        return createErrorResponse(400, 'La descripción no puede estar vacía.');
    }

    // Validar plataformas: debe ser un array con valores permitidos
    $validPlatforms = ['PS', 'XBOX', 'PC', 'Android', 'Otro'];
    if (!is_array($plataformas)) {
        return createErrorResponse(400, 'El campo plataforma debe ser un arreglo de plataformas.');
    }

    // Validar que todas las plataformas sean válidas
    foreach ($plataformas as $plataforma) {
        if (!in_array($plataforma, $validPlatforms)) {
            return createErrorResponse(400, 'Una o más plataformas no son válidas. Las plataformas válidas son: PS, XBOX, PC, Android, Otro.');
        }
    }
    // Si las validaciones pasan, continúa con el manejo de la solicitud
    return $handler->handle($request);
}
