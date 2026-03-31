<?php
/**
 * Route Definitions
 * Format: 'METHOD /path' => [ControllerClass, 'method']
 */

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\DiscoverController;
use App\Controllers\ChatController;
use App\Controllers\SettingsController;
use App\Controllers\AdminController;
use App\Controllers\GameController;

return [
    // Public pages
    'GET /'          => [HomeController::class, 'index'],

    // Authentication
    'GET /register'  => [AuthController::class, 'showRegister'],
    'POST /register' => [AuthController::class, 'register'],
    'GET /login'     => [AuthController::class, 'showLogin'],
    'POST /login'    => [AuthController::class, 'login'],
    'POST /logout'   => [AuthController::class, 'logout'],
    'GET /verify'    => [AuthController::class, 'verify'],

    // Profile
    'GET /profile'           => [ProfileController::class, 'show'],
    'GET /profile/photos'    => [ProfileController::class, 'photos'],
    'GET /profile/edit'      => [ProfileController::class, 'edit'],
    'POST /profile/update'   => [ProfileController::class, 'update'],
    'POST /profile/interests'     => [ProfileController::class, 'updateInterests'],
    'POST /profile/dealbreakers'  => [ProfileController::class, 'updateDealbreakers'],
    'POST /profile/photo'    => [ProfileController::class, 'uploadPhoto'],
    'POST /profile/photo/primary' => [ProfileController::class, 'setPrimaryPhoto'],
    'POST /profile/photo/delete'  => [ProfileController::class, 'deletePhoto'],
    'GET /user'              => [ProfileController::class, 'viewUser'],

    // Discovery / Swiping
    'GET /discover'    => [DiscoverController::class, 'index'],
    'POST /swipe'      => [DiscoverController::class, 'swipe'],

    // Matches & Chat
    'GET /matches'        => [ChatController::class, 'matches'],
    'GET /chat'           => [ChatController::class, 'conversation'],
    'POST /chat/send'     => [ChatController::class, 'send'],
    'GET /chat/poll'      => [ChatController::class, 'poll'],
    'POST /chat/unmatch'  => [ChatController::class, 'unmatch'],

    // Mini-Games
    'GET /game'           => [GameController::class, 'index'],
    'POST /game/start'    => [GameController::class, 'start'],
    'GET /game/play'      => [GameController::class, 'play'],
    'POST /game/answer'   => [GameController::class, 'answer'],
    'GET /game/poll'      => [GameController::class, 'poll'],

    // Settings & Safety
    'GET /settings'         => [SettingsController::class, 'index'],
    'POST /block'           => [SettingsController::class, 'blockUser'],
    'POST /unblock'         => [SettingsController::class, 'unblockUser'],
    'POST /report'          => [SettingsController::class, 'reportUser'],
    'GET /liked-me'         => [SettingsController::class, 'likedMe'],
    'POST /settings/password' => [SettingsController::class, 'changePassword'],
    'POST /settings/delete'   => [SettingsController::class, 'deleteAccount'],

    // Admin
    'GET /admin'               => [AdminController::class, 'dashboard'],
    'GET /admin/users'         => [AdminController::class, 'users'],
    'POST /admin/users/status' => [AdminController::class, 'updateUserStatus'],
    'GET /admin/reports'       => [AdminController::class, 'reports'],
    'POST /admin/reports/handle' => [AdminController::class, 'handleReport'],
];
