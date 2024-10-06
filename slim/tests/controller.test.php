<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/test', function(Request $request, Response $response) {
    error_log("Test route accessed");
    $response->getBody()->write('Test route is working!');
    return $response;
});

$app->get('/hello', function (Request $request, Response $response) {
    $response->getBody()->write('Hello, World!');
    return $response;
});