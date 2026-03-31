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

    // ELO K-factors for score adjustments
    'elo_k_like'      => 16,
    'elo_k_superlike'  => 24,
    'elo_k_dislike'    => 8,

    // Anti-ghosting
    'ghost_nudge_hours' => 72,

    // Profile boost defaults
    'boost_duration_minutes' => 30,
    'boost_multiplier'       => 3.0,
];
