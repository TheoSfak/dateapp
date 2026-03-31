<?php
/**
 * Front Controller – all requests enter here.
 * Apache/XAMPP must be configured to route everything through this file.
 */

define('BASE_PATH', dirname(__DIR__));

// Autoloader (PSR-4 style for App\ namespace)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = BASE_PATH . '/app/' . $relative . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Boot core services
use App\Core\Session;
use App\Core\Router;

Session::start();

// Dispatch the request
$router = new Router();
$router->loadRoutes(BASE_PATH . '/config/routes.php');
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
