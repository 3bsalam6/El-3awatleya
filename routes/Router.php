<?php
/**
 * العواطلية - Router (Upgraded for Controllers)
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
     * Resolve the current request
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

            // Callable (closure)
            if (is_callable($handler)) {
                return call_user_func($handler);
            }

            // Array: [ControllerClass, method]
            if (is_array($handler) && count($handler) === 2) {
                $controllerClass = $handler[0];
                $method = $handler[1];

                $controller = new $controllerClass();
                return $controller->$method();
            }

            // String: view path (backwards compat)
            if (is_string($handler)) {
                return $this->renderView($handler);
            }
        }

        // 404
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
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
