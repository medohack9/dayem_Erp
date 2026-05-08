<?php

namespace App\Core;

use App\Middleware\AuthMiddleware;

class Router
{
    private array $routes = [];

    public function addRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function dispatch(string $method, string $uri): void
    {
        $basePath = ($GLOBALS['app_config']['base_path'] ?? '');
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $path = parse_url($uri, PHP_URL_PATH);
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $key = "{$method} {$path}";

        if (isset($this->routes[$key])) {
            $routeConfig = $this->routes[$key];
            AuthMiddleware::check($routeConfig);

            $controllerName = $routeConfig[0];
            $action = $routeConfig[1];
            $fullClass = "App\\Controllers\\{$controllerName}";
            $controller = new $fullClass();
            $controller->$action();
            return;
        }

        foreach ($this->routes as $routeKey => $handler) {
            if ($this->matchRouteWithParams($routeKey, $key, $handler)) {
                return;
            }
        }

        http_response_code(404);
        $controller = new \App\Controllers\HomeController();
        $controller->notFound();
    }

    private function matchRouteWithParams(string $routeKey, string $requestKey, array $handler): bool
    {
        $routeParts = explode(' ', $routeKey, 2);
        if (count($routeParts) !== 2) {
            return false;
        }

        $routeMethod = $routeParts[0];
        $routePattern = $routeParts[1];
        $requestParts = explode(' ', $requestKey, 2);
        $requestMethod = $requestParts[0];
        $requestPath = $requestParts[1];

        if ($routeMethod !== $requestMethod) {
            return false;
        }

        $routeSegments = explode('/', trim($routePattern, '/'));
        $requestSegments = explode('/', trim($requestPath, '/'));

        if (count($routeSegments) !== count($requestSegments)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeSegments); $i++) {
            if (preg_match('/^\{(\w+)\}$/', $routeSegments[$i], $m)) {
                $params[$m[1]] = $requestSegments[$i];
            } elseif (str_starts_with($routeSegments[$i], ':')) {
                $params[substr($routeSegments[$i], 1)] = $requestSegments[$i];
            } elseif ($routeSegments[$i] !== $requestSegments[$i]) {
                return false;
            }
        }

        AuthMiddleware::check($handler);

        $controllerName = $handler[0];
        $action = $handler[1];
        $fullClass = "App\\Controllers\\{$controllerName}";
        $controller = new $fullClass();
        $controller->$action(...array_values($params));
        return true;
    }
}