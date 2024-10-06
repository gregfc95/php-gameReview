<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//refactorizar a container
global $pdo;

require_once __DIR__ . '/../helpers/pdo.helper.php';
require_once __DIR__ . '/../config/token.php';



//Obtener juego especifico y lista de calif get 
// GET /juegos?pagina={pagina}&clasificacion={clasificacion}&texto={texto}&plataforma={plataforma}
$app->get('/juegos', function (Request $request, Response $response) use ($pdo) {
    // Obtener los parámetros de consulta (query params)
    $pagina = (int) ($request->getQueryParams()['pagina'] ?? 1);
    $clasificacion = $request->getQueryParams()['clasificacion'] ?? null;
    $texto = $request->getQueryParams()['texto'] ?? null;
    $plataforma = $request->getQueryParams()['plataforma'] ?? null;

    // Definir límites para la paginación
    $limit = 5; // Número de juegos por página
    $offset = ($pagina - 1) * $limit; //calculado de la pagina actual para omitir el número de registros.

    // Preparar la consulta SQL
    $sql = "SELECT 
                j.id,
                j.nombre,
                j.descripcion,
                j.imagen,
                j.clasificacion_edad,
                AVG(c.estrellas) AS promedio_calificacion
            FROM 
                juego j
            LEFT JOIN 
                calificacion c ON j.id = c.juego_id
            JOIN 
                soporte s ON j.id = s.juego_id
            JOIN 
                plataforma p ON s.plataforma_id = p.id
            WHERE 1=1";
    // Array para los parámetros
    $params = [];

    // Condiciones para el texto de búsqueda
    if ($texto) {
        $sql .= " AND j.nombre LIKE :texto";
        $params[':texto'] = '%' . $texto . '%'; // For LIKE search
    }

    // Condiciones para la clasificación
    if ($clasificacion) {
        $sql .= " AND j.clasificacion_edad IN (:clasificacion)";
        $params[':clasificacion'] = $clasificacion;
    }

    // Condiciones para la plataforma
    if ($plataforma) {
        $sql .= " AND p.nombre = :plataforma";
        $params[':plataforma'] = $plataforma;
    }

    // Añadir paginación
    $sql .= " GROUP BY j.id
               ORDER BY j.nombre
               LIMIT :offset, :limit;";
    try {
        $stmt = $pdo->prepare($sql);

        // Bind parameters dinamico
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value);
        }

        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        //Ejecutar la consulta
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Handle db errors
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Handle general errors
        $response->getBody()->write(json_encode(['error' => 'Ocurrió un error inesperado: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


$app->get('/juegos/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $juegoId = $args['id'];

    try {
        // Obtener información del juego
        $stmt = $pdo->prepare("SELECT * FROM juego WHERE id = ?");
        $stmt->execute([$juegoId]);
        $juego = $stmt->fetch();

        if (!$juego) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'Juego no encontrado']));
        }

        // Obtener calificaciones del juego
        $stmtCalificaciones = $pdo->prepare("SELECT * FROM calificacion WHERE juego_id = ?");
        $stmtCalificaciones->execute([$juegoId]);
        $calificaciones = $stmtCalificaciones->fetchAll();

        $response->getBody()->write(json_encode(['juego' => $juego, 'calificaciones' => $calificaciones]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Log the error message and return a 500 response
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    } catch (Exception $e) {
        // Handle other types of exceptions
        $response->getBody()->write(json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});



//Solo usuario logeado y que sea administrador
//Crear juego post

$app->post('/juego', function (Request $request, Response $response) use ($pdo) {

    $data = $request->getParsedBody();

    $nombre = $data['nombre'] ?? '';
    $descripcion = $data['descripcion'] ?? '';
    $imagen = $data['imagen'] ?? '';
    $clasificacion_edad = $data['clasificacion_edad'] ?? '';

    try {
        //Image to Base64
        // Inicializar $imageData
        $imageData = null;

        //  Check si la imagen existe o es una cadena base64
        if (file_exists($imagen)) {
            // Convertir la imagen a Base64
            $imageData = base64_encode(file_get_contents($imagen));
        } else {
            // Si archivo no existe verificamos si es base64
            if (preg_match('/^data:image\/(\w+);base64,/', $imagen, $type)) {
                // Es una cadena base64; tomamos los datos base64 reales
                $data = substr($imagen, strpos($imagen, ',') + 1);
                $imageData = base64_decode($data);

                if ($imageData === false) {
                    $response->getBody()->write(json_encode(['error' => 'Datos base64 inválidos.']));
                    return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                }

                // También se puede guardar la cadena base64 sin procesar directamente
                $imageData = $imagen; // Descomente esta línea para almacenar la cadena base64 completa
            } else {
                // // Si no hay una ruta de archivo válida ni una cadena base64, devuelve un error
                $response->getBody()->write(json_encode(['error' => 'El archivo de imagen no se encuentra o es invalido.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        // Insertar el juego en la db
        $stmt = $pdo->prepare("INSERT INTO juego (nombre, descripcion, imagen, clasificacion_edad) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $imageData, $clasificacion_edad]);

        $response->getBody()->write(json_encode(['status' => 'Juego creado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {

        // Handle db exceptions
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Handle unexpected exceptions
        $response->getBody()->write(json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
})->add($checkAdmin)->add($tokenValidationMiddleware)->add('validateJuego'); //Last in, first OUT!

//Solo usuario logeado y que sea administrador
//Editar datos juego Put

$app->put('/juego/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $juegoId = $args['id'];
    $data = $request->getParsedBody();
    $nombre = $data['nombre'];
    $descripcion = $data['descripcion'];
    $clasificacion_edad = $data['clasificacion_edad'] ?? '';

    try {
        // Verificar si el juego existe
        $stmt = $pdo->prepare("SELECT * FROM juego WHERE id = ?");
        $stmt->execute([$juegoId]);
        $juego = $stmt->fetch();

        if (!$juego) {
            $response->getBody()->write(json_encode(['error' => 'Juego no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Actualizar juego
        $stmtUpdate = $pdo->prepare("UPDATE juego SET nombre = ?, descripcion = ?, clasificacion_edad = ? WHERE id = ?");
        $stmtUpdate->execute([$nombre, $descripcion, $clasificacion_edad, $juegoId]);

        $response->getBody()->write(json_encode(['status' => 'Juego actualizado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Manejo de errores de la base de datos
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Manejo de errores generales
        $response->getBody()->write(json_encode(['error' => 'Ocurrio un error inesperado: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
})->add($checkAdmin)->add($tokenValidationMiddleware)->add('validateJuego'); //Last in, first OUT!


//Borrar juego Delete
//Verificar User logeado y admin
//Solo borrar juego si no tiene calificaciones
$app->delete('/juego/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $juegoId = $args['id'];

    try {
        // Verificar si el juego existe
        $stmtCheck = $pdo->prepare("SELECT * FROM juego WHERE id = ?");
        $stmtCheck->execute([$juegoId]);
        $juego = $stmtCheck->fetch();

        if (!$juego) {
            $response->getBody()->write(json_encode(['error' => 'Juego no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Verificar si el juego tiene calificaciones
        $stmt = $pdo->prepare("SELECT * FROM calificacion WHERE juego_id = ?");
        $stmt->execute([$juegoId]);
        $calificaciones = $stmt->fetchAll();

        if (!empty($calificaciones)) {
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'No se puede eliminar un juego con calificaciones']));
        }

        // Eliminar el juego
        $stmtDelete = $pdo->prepare("DELETE FROM juego WHERE id = ?");
        $stmtDelete->execute([$juegoId]);

        $response->getBody()->write(json_encode(['status' => 'Juego eliminado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Manejo de errores de la base de datos
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Manejo de errores generales
        $response->getBody()->write(json_encode(['error' => 'Ocurrio un error inesperado: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
})->add($checkAdmin)->add($tokenValidationMiddleware); //Last in, first OUT!