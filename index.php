<?php
/**
 * العواطلية - Application Entry Point
 * All requests are routed through this file
 *  php -S localhost:8000 index.php
 *  http://localhost:8000/login
 */

// Serve static files directly in PHP's built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $uri);
        return true;
    }

    return false;
}

// ===========================
// Load Configuration
// ===========================
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// ===========================
// Load Helpers
// ===========================
require_once __DIR__ . '/app/helpers/Session.php';
require_once __DIR__ . '/app/helpers/Auth.php';
require_once __DIR__ . '/app/helpers/Mailer.php';

// ===========================
// Load Models
// ===========================
require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/app/models/Job.php';
require_once __DIR__ . '/app/models/Application.php';

// ===========================
// Load Controllers
// ===========================
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/JobController.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/controllers/EmployerController.php';
require_once __DIR__ . '/app/controllers/AdminController.php';

// ===========================
// Start Session
// ===========================
Session::start();

// ===========================
// Route
// ===========================
require_once __DIR__ . '/routes/Router.php';

$router = new Router();

require_once __DIR__ . '/routes/web.php';

$router->resolve();
