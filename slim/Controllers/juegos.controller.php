<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//refactorizar a container
global $pdo;

require_once __DIR__ . '/../helpers/pdo.helper.php';
require_once __DIR__ . '/../config/token.php';

//Obtener juego especifico y lista de calif get 
// GET /juegos?pagina={pagina}&clasificacion={clasificacion}&texto={texto}&plataforma={plataforma}
/*     $app->get('/juegos', function (Request $request, Response $response) use ($pdo) {
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
        // Left join para que no de error si no hay calificaciones (recuerda Diagrama de Venn)
        //Where 1=1 Como no sabemos si vamos a usar todas las condiciones o solo una
        //se arma un query generico y se concatena las demas condiciones, para eso el where = true o 1=1
        // Array para los parámetros
        $params = [];

        // Condiciones para el texto de búsqueda
        if ($texto) {
            $sql .= " AND j.nombre LIKE :texto";
            $params[':texto'] = '%' . $texto . '%'; // For LIKE search
        }

        // Condiciones para la clasificación con Correccion
        if ($clasificacion) {
            if ($clasificacion == '+18') {
                // No hay filtro para "+18", return todo
            } elseif ($clasificacion == '+13') {
                // Return ambos "ATP" and "+13"
                $sql .= " AND (j.clasificacion_edad = 'ATP' OR j.clasificacion_edad = '+13')";
            } elseif ($clasificacion == 'ATP') {
                // Return solo "ATP"
                $sql .= " AND j.clasificacion_edad = 'ATP'";
            } else {
                // Caso por defecto, return exactamente el valor de clasificación
                $sql .= " AND j.clasificacion_edad = :clasificacion";
                $params[':clasificacion'] = $clasificacion;
            }
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
 */
    $app->get('/juegos', function (Request $request, Response $response) use ($pdo) {
        $pagina = $request->getQueryParams()['pagina'];
/*         $clasificacion = $request->getQueryParams()['clasificacion'];
        $texto = $request->getQueryParams()['texto'];
        $plataforma = $request->getQueryParams()['plataforma']; */

        $limit = 5; // Número de juegos por página
        $offset = ($pagina - 1) * $limit; //calculado de la pagina actual para omitir el número de registros.   

        $sql = "SELECT 
                    *
                FROM juego j
                limit :offset, :limit";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'No se encontraron juegos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

    });

$app->get('/juegos/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $juegoId = $args['id'];

    try {
        // Obtener información del juego
        $stmt = $pdo->prepare("SELECT * FROM juego WHERE id = ?");
        $stmt->execute([$juegoId]);
        $juego = $stmt->fetch(PDO::FETCH_ASSOC); //Correcion, se agrego PDO::FETCH_ASSOC para que devuelva un arreglo asociativa

        if (!$juego) {
            // Return 404 si no se encuentra el juego
            $response->getBody()->write(json_encode(['error' => 'Juego no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
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
    $plataformas = $data['plataforma'] ?? []; // Expecting an array of plataforma names

    try {
        // Iniciar una transacción
        $pdo->beginTransaction();
        //Image to Base64
        // Inicializar $imageData
        $imageData = null;

        //  Check si la imagen existe, convertir a base64
        if (file_exists($imagen)) {
            $type = pathinfo($imagen, PATHINFO_EXTENSION);
            $data = file_get_contents($imagen);
            $imageData = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }  
        //Si ya esta en base64 format, guardar
         elseif (preg_match('/^data:image\/(\w+);base64,/', $imagen)) {
            $imageData = $imagen;
        } else {
            // Rollback si es invalido
            $pdo->rollBack();
            $response->getBody()->write(json_encode(['error' => 'Imagen no valida o no encontrada.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Insertar el juego en la db
        $stmt = $pdo->prepare("INSERT INTO juego (nombre, descripcion, imagen, clasificacion_edad) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $imageData, $clasificacion_edad]);

        $juego_id = $pdo->lastInsertId();

        foreach ($plataformas as $plataforma_nombre) {
            // Hace Fecht de la plataforma ID de la plataforma tabla
            $stmt = $pdo->prepare("SELECT id FROM plataforma WHERE nombre = ?");
            $stmt->execute([$plataforma_nombre]);
            $plataforma_id = $stmt->fetchColumn();
            if (!$plataforma_id) {
                $pdo->rollBack();
                $response->getBody()->write(json_encode(['error' => 'Plataforma no encontrada: ' . $plataforma_nombre]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Inserta en soporte
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM soporte WHERE juego_id = ? AND plataforma_id = ?");
            $stmt->execute([$juego_id, $plataforma_id]);
            $exists = $stmt->fetchColumn();

            if (!$exists) {
                // Si no hay duplicados, inserta
                $stmt = $pdo->prepare("INSERT INTO soporte (juego_id, plataforma_id) VALUES (?, ?)");
                $stmt->execute([$juego_id, $plataforma_id]);
            }
        }
        //Hago el commit de la transacción
        $pdo->commit();

        $response->getBody()->write(json_encode(['status' => 'Juego creado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (PDOException $e) {
        // Handle db exceptions
        //Rollback si fallo
        $pdo->rollBack();
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Handle unexpected exceptions
        //Rollback si fallo
        $pdo->rollBack();
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
    $plataformas = $data['plataforma_nombre'] ?? [];

    try {
        //Transaccion para hacer rollback en caso de fallar, util para multiples peticiones SQL
        $pdo->beginTransaction();
        // Verificar si el juego existe
        $stmt = $pdo->prepare("SELECT * FROM juego WHERE id = ?");
        $stmt->execute([$juegoId]);
        $juego = $stmt->fetch(pdo::FETCH_ASSOC); //correccion PDO::FETCH_ASSOC para que devuelva un arreglo asociativa

        if (!$juego) {
            $response->getBody()->write(json_encode(['error' => 'Juego no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404); //Correccion 401 a 404
        }

        foreach ($plataformas as $plataforma_nombre) {
            // Check si la plataforma existe
            $stmtPlatform = $pdo->prepare("SELECT id FROM plataforma WHERE nombre = ?");
            $stmtPlatform->execute([$plataforma_nombre]);
            $platform = $stmtPlatform->fetch(PDO::FETCH_ASSOC);

            if ($platform) {
                $plataforma_id = $platform['id'];

                // Check duplicados en soporte
                $stmtSoporteCheck = $pdo->prepare("SELECT * FROM soporte WHERE juego_id = ? AND plataforma_id = ?");
                $stmtSoporteCheck->execute([$juegoId, $plataforma_id]);
                $soporte = $stmtSoporteCheck->fetch(PDO::FETCH_ASSOC);

                if (!$soporte) {
                    // Inserta en soporte si no existe
                    $stmtInsertSoporte = $pdo->prepare("INSERT INTO soporte (juego_id, plataforma_id) VALUES (?, ?)");
                    $stmtInsertSoporte->execute([$juegoId, $plataforma_id]);
                }
            }
        }

        // Actualizar juego
        $stmtUpdate = $pdo->prepare("UPDATE juego SET nombre = ?, descripcion = ?, clasificacion_edad = ? WHERE id = ?");
        $stmtUpdate->execute([$nombre, $descripcion, $clasificacion_edad, $juegoId]);

        //Hago el commit de la transacción
        $pdo->commit();

        $response->getBody()->write(json_encode(['status' => 'Juego actualizado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // Manejo de errores de la base de datos
        //Rollback si fallo
        $pdo->rollBack();
        $response->getBody()->write(json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        // Manejo de errores generales
        //Rollback si fallo
        $pdo->rollBack();
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
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404); //Correccion 401 a 404
        }

        // Verificar si el juego tiene calificaciones
        $stmt = $pdo->prepare("SELECT * FROM calificacion WHERE juego_id = ?");
        $stmt->execute([$juegoId]);
        $calificaciones = $stmt->fetchAll();
        if (!empty($calificaciones)) {
            $response->getBody()->write(json_encode(['error' => 'No se puede eliminar un juego con calificaciones']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        // Eliminar entradas en soporte relacionadas con el juego
        $stmtDeleteSoporte = $pdo->prepare("DELETE FROM soporte WHERE juego_id = ?");
        $stmtDeleteSoporte->execute([$juegoId]);

        // Eliminar el juego
        $stmtDelete = $pdo->prepare("DELETE FROM juego WHERE id = ?");
        $stmtDelete->execute([$juegoId]);

        $response->getBody()->write(json_encode(['status' => 'Juego y Soporte eliminados']));
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