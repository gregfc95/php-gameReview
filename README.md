## Tecnologías

- PHP 8.x
- Slim Framework 4.x
- MySQL for the database
- Composer para la gestión de dependencias
- dotenv para la gestión de variables de entorno

## Instalación

Para configurar este proyecto localmente, siga estos pasos:

1. Clone el repositorio:
```bash
   git clone https://github.com/yourusername/php-gameReview.git
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
    nombre del proyecto docker-compose
    COMPOSE_PROJECT_NAME=seminariophp

    #nombre del volumen para la base de datos
    DB_VOLUME=seminariophp

    nombre, usuario y contraseña de la base de datos
    DB_NAME=seminariophp
    DB_USER=root
    DB_PASS=

    puerto de acceso a la aplicación
    SLIM_PORT=80

    puerto de acceso a phpmyadmin
    DBADMIN_PORT=8080

    JWT key
    JWT_SECRET=
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
| POST   | /         | ID                   |
| GET    | /         | ID                  |
| GET    | //{id}    | ID        |
| PUT    | //{id}    | ID         |
| DELETE | //{id}    | ID         |

## Error Handling

La API proporciona respuestas de error en caso de solicitudes no válidas. Los códigos de error más comunes incluyen:

- 404 No encontrado: cuando el recurso solicitado no existe.
- 401 Unauthorized
- 500 Error interno del servidor: cuando hay un error del servidor.