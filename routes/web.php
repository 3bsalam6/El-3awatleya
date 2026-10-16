<?php
/**
 * العواطلية - Route Definitions
 */

$router->get('/', function () {
    header('Location: /login');
    exit;
});

// Auth
$router->get('/login', 'auth/login');
$router->get('/register', 'auth/register');

// Dashboard
$router->get('/dashboard', 'dashboard/index');

// Evaluation
$router->get('/evaluation', 'evaluation/result');
