<?php
namespace App\Models;

use App\Core\Model;

class Boost extends Model
{
    protected static string $table = 'profile_boosts';

    /**
     * Get the active boost for a user, if any.
     */
    public static function getActive(int $userId): ?array
    {
        $stmt = static::db()->query(
            "SELECT * FROM profile_boosts WHERE user_id = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Check if a user currently has an active boost.
     */
    public static function isActive(int $userId): bool
    {
        return self::getActive($userId) !== null;
    }

    /**
     * Activate a boost for a user (30-minute window).
     */
    public static function activate(int $userId, int $minutes = 30, float $multiplier = 3.0): void
    {
        static::db()->query(
            "INSERT INTO profile_boosts (user_id, expires_at, multiplier) VALUES (?, DATE_ADD(NOW(), INTERVAL ? MINUTE), ?)",
            [$userId, $minutes, $multiplier]
        );
    }

    /**
     * Get remaining seconds on active boost.
     */
    public static function getRemainingSeconds(int $userId): int
    {
        $boost = self::getActive($userId);
        if (!$boost) return 0;
        $remaining = strtotime($boost['expires_at']) - time();
        return max(0, $remaining);
    }

    /**
     * Get the multiplier for a user in discovery queries.
     * Returns 1.0 if no active boost.
     */
    public static function getMultiplier(int $userId): float
    {
        $boost = self::getActive($userId);
        return $boost ? (float)$boost['multiplier'] : 1.0;
    }
}
