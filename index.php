<?php
/**
 * العواطلية - Application Entry Point
 * All requests are routed through this file
 */

// Serve static files directly in PHP's built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If requesting a static file that exists, serve it
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    // Set correct content type for CSS and JS
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'pdf'  => 'application/pdf',
    ];

    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $uri);
        return true;
    }

    return false; // Let PHP's built-in server handle it
}

require_once __DIR__ . '/routes/Router.php';

$router = new Router();

require_once __DIR__ . '/routes/web.php';

$router->resolve();
