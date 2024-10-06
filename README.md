## Tecnologías

- PHP 8.x
- Slim Framework 4.x
- MySQL for the database
- Composer para la gestión de dependencias
- "XAMPP" como gestor de MySQL y Apache server
- "vlucas/phpdotenv" para la gestión de variables de entorno

## Instalación

Para configurar este proyecto localmente, siga estos pasos:

1. Clone el repositorio:
```bash
   git clone https://github.com/gregfc95/php-gameReview.git
```


2. Vaya al directorio del proyecto:
```bash
    cd php-gameReview/slim
```
3. Instalar dependencias usando Composer:   
```bash
    composer install
```
   
4. Configure la conexión a la base de datos creando un archivo .env en el directorio raíz del proyecto. Puede utilizar el archivo .env.example como referencia:
```bash
    #nombre del proyecto docker-compose
    COMPOSE_PROJECT_NAME=seminariophp

    #nombre del volumen para la base de datos
    DB_VOLUME=seminariophp

    #nombre, usuario y contraseña de la base de datos
    DB_NAME=seminariophp
    DB_USER=root
    DB_PASS=

    #puerto de acceso a la aplicación
    SLIM_PORT=80

    #puerto de acceso a phpmyadmin
    DBADMIN_PORT=8080
    #Ejercicio no requiere JWT - Deprecated
    #JWT key
    #JWT_SECRET=
```
 

5. Inicie el servidor Apache usando XAMPP y navegue hasta el directorio del proyecto en su navegador:
   
```bash
http://localhost/php-gameReview/slim/
```

 
## Uso
Puedes probar la API con herramientas como Postman o curl. La API admite varios métodos HTTP como GET, POST, PUT y DELETE para interactuar con las reseñas de juegos.

##   API Endpoints

| Method | Endpoint        | Description                                  |
|--------|------------------|----------------------------------------------|
| GET    | /test            | Test endpoint verifica si la API está funcionando |
| POST   | /login         | Permite ingresar como usuario usando credenciales validas, genera un token con vencimiento                   |
| POST    | /register         | Permite Crear un User                  |
| POST    | /usuario    | Permite Crear un User        |
| PUT    | /usuario/{id}    | Permite Editar tu propio usuario         |
| DELETE | /usuario/{id}    | Permite Borrar tu propio usuario        |
| GET | /usuario/{id}    | Retorna el JSON de tu propio usuario        |
| GET | /juego/{id}    | Retorna el JSON de un juego       |
| GET | /juegos?pagina={pagina}&clasificacion={clasificacion}&texto={texto}&plataforma={plataforma}| Filtro con queryParams
| POST | /juego/    | Crea un juego, solo admin y logged user       |
| PUT | /juego/{id}    | Edita un juego, solo admin y logged user       |
| DELETE | /juego/{id}    | Borra un juego, solo admin y logged user       |
| POST | /calificacion/  | Crea una calificacion, solo logged user     |
| PUT | /calificacion/{id}  | Edita una calificacion, solo logged user     |
| DELETE | /calificacion/{id}  | Borra una calificacion, solo logged user     |

## Error Handling

La API proporciona respuestas de error en caso de solicitudes no válidas. Los códigos de error más comunes incluyen:

- 404 No encontrado: cuando el recurso solicitado no existe.
- 401 Unauthorized
- 500 Error interno del servidor: cuando hay un error del servidor.


## API Reference

#### Login 

```http
  POST /login/
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `none` | `none` | none |




#### Sign Up 

```http
  POST /register/
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `none` | `none` | none |

nombre_usuario: entre 6 y 20 alfanumerico.

clave: 8 chars, min, mayus, num y carac especiales
```bash
{
    "nombre_usuario": "uservalidation10E",
    "clave": "Password1234567890!"
}
```
#### Sign Up - Usuarios

```http
  POST /usuario/
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `none` | `none` | none |

nombre_usuario: entre 6 y 20 alfanumerico.

clave: 8 chars, min, mayus, num y carac especiales
```bash
{
    "nombre_usuario": "uservalidation10E",
    "clave": "Password1234567890!"
}
```

#### Editar - Usuario

```http
  PUT /usuario/{id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization ` | `string` | **Required**. Bearer <user_token> |

#### Borrar - Usuario

```http
  DELETE /usuario/{id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization ` | `string` | **Required**. Bearer <user_token> |


#### Retornar - Usuario

```http
  GET /usuario/{id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization ` | `string` | **Required**. Bearer <user_token> |


