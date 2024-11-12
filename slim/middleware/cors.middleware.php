<?php

// CORS Middleware
$corsMiddleware = function ($request, $handler) {
    // GET la solicitud del siguiente middleware
    $response = $handler->handle($request);

    //URL from .env file
    $reactAppUrl = $_ENV['REACT_APP_URL'] ?? 'http://localhost:3000';
    // Set CORS headers
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', $reactAppUrl)
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Expose-Headers', 'Authorization');

    // Handle  (OPTIONS) requests
    if ($request->getMethod() === 'OPTIONS') {
        return $response->withStatus(200);  // Return a 200 OK response for OPTIONS
    }

    return $response;
};
