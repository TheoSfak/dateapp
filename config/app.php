<?php
/**
 * Application Configuration
 */
return [
    'name' => 'DateApp',
    'url'  => 'http://localhost/dateapp',
    'debug' => true,

    // Session
    'session_lifetime' => 3600, // 1 hour

    // Upload limits
    'max_photo_size' => 5 * 1024 * 1024, // 5MB
    'allowed_photo_types' => ['image/jpeg', 'image/png', 'image/webp'],
    'max_photos_per_user' => 6,

    // Swipe limits (free tier)
    'free_daily_swipes' => 50,
];
