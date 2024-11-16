<?php

// CORS Middleware
$corsMiddleware = function ($request, $handler) {
    // GET la solicitud del siguiente middleware
    $response = $handler->handle($request);
    
    //URL from .env file
    $reactAppUrl = $_ENV['REACT_APP_URL'] ?? 'http://localhost:3000';
    // Set CORS headers

    return $response
    ->withHeader('Access-Control-Allow-Origin', $reactAppUrl)
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Credentials', 'true')
    ->withHeader('Access-Control-Expose-Headers', 'Authorization');

};
