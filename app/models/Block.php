<?php
namespace App\Models;

use App\Core\Model;

class Block extends Model
{
    protected static string $table = 'blocks';

    public static function block(int $blockerId, int $blockedId): void
    {
        static::db()->query(
            "INSERT IGNORE INTO blocks (blocker_id, blocked_id) VALUES (?, ?)",
            [$blockerId, $blockedId]
        );
    }

    public static function unblock(int $blockerId, int $blockedId): void
    {
        static::db()->query(
            "DELETE FROM blocks WHERE blocker_id = ? AND blocked_id = ?",
            [$blockerId, $blockedId]
        );
    }

    public static function isBlocked(int $userId1, int $userId2): bool
    {
        $stmt = static::db()->query(
            "SELECT id FROM blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?) LIMIT 1",
            [$userId1, $userId2, $userId2, $userId1]
        );
        return (bool) $stmt->fetch();
    }

    public static function getBlockedByUser(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT b.*, p.name, ph.file_path as photo
             FROM blocks b
             JOIN profiles p ON p.user_id = b.blocked_id
             LEFT JOIN photos ph ON ph.user_id = b.blocked_id AND ph.is_primary = 1
             WHERE b.blocker_id = ?
             ORDER BY b.created_at DESC",
            [$userId]
        );
        return $stmt->fetchAll();
    }
}
