<?php
namespace App\Models;

use App\Core\Model;

class Interaction extends Model
{
    protected static string $table = 'interactions';

    /**
     * Record a like/dislike/superlike. Returns 'match' if mutual like occurred.
     */
    public static function create(int $actorId, int $targetId, string $action): string
    {
        // Upsert interaction
        static::db()->query(
            "INSERT INTO interactions (actor_id, target_id, action_type) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE action_type = VALUES(action_type), created_at = NOW()",
            [$actorId, $targetId, $action]
        );

        // Check for mutual like (match)
        if ($action === 'like' || $action === 'superlike') {
            $stmt = static::db()->query(
                "SELECT id FROM interactions
                 WHERE actor_id = ? AND target_id = ? AND action_type IN ('like','superlike')",
                [$targetId, $actorId]
            );
            if ($stmt->fetch()) {
                // Create match if not exists
                $u1 = min($actorId, $targetId);
                $u2 = max($actorId, $targetId);
                static::db()->query(
                    "INSERT IGNORE INTO matches (user_1_id, user_2_id) VALUES (?, ?)",
                    [$u1, $u2]
                );
                return 'match';
            }
        }

        return $action;
    }

    /**
     * Get today's swipe count for a user.
     */
    public static function getTodaySwipeCount(int $userId): int
    {
        $stmt = static::db()->query(
            "SELECT COUNT(*) as cnt FROM interactions WHERE actor_id = ? AND DATE(created_at) = CURDATE()",
            [$userId]
        );
        return (int) $stmt->fetch()['cnt'];
    }

    /**
     * Check if user already interacted with target.
     */
    public static function hasInteracted(int $actorId, int $targetId): bool
    {
        $stmt = static::db()->query(
            "SELECT id FROM interactions WHERE actor_id = ? AND target_id = ? LIMIT 1",
            [$actorId, $targetId]
        );
        return (bool) $stmt->fetch();
    }

    /**
     * Get users who liked the given user (for premium "see who liked you").
     */
    public static function getLikers(int $userId, int $limit = 50): array
    {
        $stmt = static::db()->query(
            "SELECT i.actor_id, i.action_type, i.created_at,
                    p.name, ph.file_path as photo, p.city,
                    TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age
             FROM interactions i
             JOIN profiles p ON p.user_id = i.actor_id
             LEFT JOIN photos ph ON ph.user_id = i.actor_id AND ph.is_primary = 1
             WHERE i.target_id = ? AND i.action_type IN ('like','superlike')
             AND NOT EXISTS (SELECT 1 FROM interactions i2 WHERE i2.actor_id = ? AND i2.target_id = i.actor_id)
             ORDER BY i.created_at DESC
             LIMIT ?",
            [$userId, $userId, $limit]
        );
        return $stmt->fetchAll();
    }
}
