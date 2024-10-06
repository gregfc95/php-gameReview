<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//refactorizar a container
global $pdo;

require_once __DIR__ . '/../helpers/pdo.helper.php';
require_once __DIR__ . '/../config/token.php';


/*
//Obtener juego especifico y lista de calif get 
// GET /juegos?pagina={pagina}&clasificacion={clasificacion}&texto={texto}&plataforma={plataforma}
$app->get('/juegos', function (Request $request, Response $response) use ($pdo) {

    // Obtener los parámetros de consulta (query params)
    $params = $request->getQueryParams();
    $pagina = isset($params['pagina']) ? (int)$params['pagina'] : 1;
    $clasificacion = isset($params['clasificacion']) ? $params['clasificacion'] : null;
    $texto = isset($params['texto']) ? $params['texto'] : null;
    $plataforma = isset($params['plataforma']) ? $params['plataforma'] : null;

    // Establecer tamaño de página y calcular el offset para la paginación
    $pageSize = 10; // Puedes ajustar el tamaño de la página según sea necesario
    $offset = ($pagina - 1) * $pageSize;

    // Construir la consulta básica con filtros
    $sql = "SELECT j.*, 
                   IFNULL(AVG(c.puntuacion), 0) AS puntuacion_promedio 
            FROM juegos j 
            LEFT JOIN calificaciones c ON j.id = c.juego_id 
            WHERE 1=1";

    // Añadir los filtros según los parámetros pasados
    if ($clasificacion) {
        $sql .= " AND j.clasificacion = :clasificacion";
    }
    if ($texto) {
        $sql .= " AND j.nombre LIKE :texto";
    }
    if ($plataforma) {
        $sql .= " AND j.plataforma = :plataforma";
    }

    // Añadir la agrupación por juego y la limitación para la paginación
    $sql .= " GROUP BY j.id LIMIT :limit OFFSET :offset";

    // Preparar la consulta
    $stmt = $pdo->prepare($sql);

    // Vincular los parámetros
    if ($clasificacion) {
        $stmt->bindParam(':clasificacion', $clasificacion);
    }
    if ($texto) {
        $searchText = '%' . $texto . '%';
        $stmt->bindParam(':texto', $searchText);
    }
    if ($plataforma) {
        $stmt->bindParam(':plataforma', $plataforma);
    }
    // Parámetros de paginación
    $stmt->bindParam(':limit', $pageSize, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $juegos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar la respuesta en formato JSON
    $response->getBody()->write(json_encode([
        'pagina' => $pagina,
        'resultados' => $juegos
    ]));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

}); */


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
        // Verificar si el juego tiene calificaciones
        $stmt = $pdo->prepare("SELECT * FROM calificaciones WHERE juego_id = ?");
        $stmt->execute([$juegoId]);
        $calificaciones = $stmt->fetchAll();

        if (!empty($calificaciones)) {
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'No se puede eliminar un juego con calificaciones']));
        }

        // Eliminar el juego
        $stmtDelete = $pdo->prepare("DELETE FROM juegos WHERE id = ?");
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