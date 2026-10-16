<?php
/**
 * العواطلية - Simple Router
 * Maps URL paths to view files
 */

class Router
{
    private $routes = [];

    /**
     * Register a GET route
     */
    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Register a POST route
     */
    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Resolve the current request to a route
     */
    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash (except root)
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];

            if (is_callable($handler)) {
                return call_user_func($handler);
            }

            // If handler is a string, treat as view path
            if (is_string($handler)) {
                return $this->renderView($handler);
            }
        }

        // 404
        http_response_code(404);
        echo '<div style="text-align:center;padding:80px;font-family:Cairo,sans-serif;direction:rtl;">';
        echo '<h1 style="font-size:4rem;">404</h1>';
        echo '<h2>404 - الصفحة مش موجودة</h2>';
        echo '<p>الصفحة اللي بتدور عليها مش هنا</p>';
        echo '<a href="/login" style="color:#c4712b;">ارجع للرئيسية</a>';
        echo '</div>';
    }

    /**
     * Render a view file
     */
    private function renderView($view)
    {
        $viewPath = __DIR__ . '/../app/views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            http_response_code(500);
            echo "View not found: {$view}";
        }
    }
}
