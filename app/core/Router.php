<?php
namespace App\Core;

/**
 * Simple front-controller router.
 * Matches "METHOD /path" against the route table.
 */
class Router
{
    private array $routes = [];

    public function loadRoutes(string $file): void
    {
        $this->routes = require $file;
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string and trailing slash
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Strip the base path (e.g. /dateapp) so routes are relative
        $basePath = parse_url(Config::get('app.url'), PHP_URL_PATH) ?? '';
        $basePath = rtrim($basePath, '/');
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $key = strtoupper($method) . ' ' . $uri;

        if (!isset($this->routes[$key])) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        [$controllerClass, $action] = $this->routes[$key];
        $controller = new $controllerClass();
        $controller->$action();
    }
}
