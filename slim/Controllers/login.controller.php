<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ValidationHelper; 
//Traer PDO
$pdo = require_once __DIR__ . '/../config/connect.db.php';
require_once __DIR__ . '/../helpers/pdo.helper.php';


//route login 
$app->post('/login',function(Request $request, Response $response) use($pdo){
    $data = $request->getParsedBody();

    //Comprobar PDO
    $responseCheck = checkDatabaseConnection($pdo, $response);
    if ($responseCheck) {
        return $responseCheck;
    }
    //verificar que los campos estén presentes
    $validationResponse = ValidationHelper::validateCredentials($data, $response);
    if ($validationResponse) {
        return $validationResponse;
    }

    $username = $data['nombre_usuario'];
    $password = $data['clave'];

    try {
        // Buscar el usuario en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Si el usuario no existe o la contraseña no es correcta, devolver 401 Unauthorized
        if (!$user || !password_verify($password, $user['clave'])) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales invalidas']));
        
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Llamar a la función que ya tienes para generar el token
        $token = generarToken($user['id']); 

        // Actualizar la base de datos con el token y su fecha de vencimiento
        $stmt = $pdo->prepare("UPDATE usuario SET token = ?, vencimiento_token = ? WHERE id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', time() + 3600), $user['id']]);

        // Devolver el token en la respuesta
        $response->getBody()->write(json_encode(['token' => $token]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (PDOException $e) {
        // Si hay un error con la consulta o la base de datos, devolver estado 500 (Internal Server Error)
        $response->getBody()->write(json_encode(['error' => 'Error de base de datos', 'details' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


//Registro
$app->post('/register',function(Request $request, Response $response) use($pdo){

    $data = $request->getParsedBody();
    $username = $data['nombre_usuario'] ?? '';
    $password = $data['clave'] ?? '';
    
     
    //verificar que los campos estén presentes
    $validationResponse = ValidationHelper::validateCredentials($data, $response);
    if ($validationResponse) {
        return $validationResponse;
    }
    
    // 1. Validar nombre de usuario: alfanumérico, entre 6 y 20 caracteres
    if (!preg_match('/^[a-zA-Z0-9]{6,20}$/', $username)) {
        $response->getBody()->write(json_encode(['error' => 'El nombre de usuario debe tener entre 6 y 20 caracteres y solo puede contener caracteres alfanuméricos.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    // 3. Validar la clave: mínimo 8 caracteres, mayúsculas, minúsculas, números y caracteres especiales
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $response->getBody()->write(json_encode(['error' => 'La contraseña debe tener al menos 8 caracteres, con mayúsculas, minúsculas, números y caracteres especiales.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        // 2. Verificar si el nombre de usuario ya está en uso
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$username]);
        $usernameExists = $stmt->fetchColumn();
    
        if ($usernameExists) {
            $response->getBody()->write(json_encode(['error' => 'El nombre de usuario ya está en uso.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
         // Encriptar la clave
          $hashedPassword = password_hash($password, PASSWORD_BCRYPT);  

           // Insertar el nuevo usuario
           $stmt = $pdo->prepare("INSERT INTO usuario (nombre_usuario, clave) VALUES (?, ?)");
           $stmt->execute([$username, $hashedPassword]);
                    
           // Respuesta exitosa
          $response->getBody()->write(json_encode(['status' => 'Usuario creado con éxito']));
          return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        
    } catch (PDOException $e) {
        // Si hay un error con la consulta, devolver estado 500 (Internal Server Error)
       $response->getBody()->write(json_encode(['error' => 'Error al crear el usuario', 'details' => $e->getMessage()]));
       return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
   }
});
