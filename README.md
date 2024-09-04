Seminario de PHP, React, y API Rest
===================================

## Configuración inicial

1. Crear archivo `.env` a partir de `.env.dist`

```bash
cp .env.dist .env
```

2. Crear volumen para la base de datos

```bash
docker volume create seminariophp
```

donde *seminariophp* es el valor de la variable `DB_VOLUME`

## Iniciar servicios

```bash
docker compose up -d
```

## Terminar servicios

```bash
docker compose down -v
```

## Eliminar base de datos

```bash
docker volume rm seminariophp
```

## Instalacion de Librerias externas

Instalar las siguientes librerias dentro de slim:
vlucas/phpdotenv
Instalado para utilizar las env variables para hacer la conexion a la db usando .env.dist
```bash
composer require vlucas/phpdotenv
```

JWT 
Instalado para trabajar con JSON web Tokens, el cual es un estandar para la creacion de tokens seguros el cual es verificado por el servidor y asegura que el usuario tiene permisos para realizar acciones.
```bash
 composer require firebase/php-jwt
```
