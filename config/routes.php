<?php
/**
 * Route Definitions
 * Format: 'METHOD /path' => [ControllerClass, 'method']
 */

use App\Controllers\HomeController;
use App\Controllers\AuthController;

return [
    // Public pages
    'GET /'          => [HomeController::class, 'index'],

    // Authentication
    'GET /register'  => [AuthController::class, 'showRegister'],
    'POST /register' => [AuthController::class, 'register'],
    'GET /login'     => [AuthController::class, 'showLogin'],
    'POST /login'    => [AuthController::class, 'login'],
    'POST /logout'   => [AuthController::class, 'logout'],

    // Email verification
    'GET /verify'    => [AuthController::class, 'verify'],
];
