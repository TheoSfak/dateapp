<?php
namespace App\Models;

use App\Core\Model;

class Match extends Model
{
    protected static string $table = 'matches';

    /**
     * Get all matches for a user with profile info.
     */
    public static function getByUserId(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT m.id as match_id, m.matched_at,
                    CASE WHEN m.user_1_id = ? THEN m.user_2_id ELSE m.user_1_id END as other_user_id,
                    p.name, p.city,
                    TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                    ph.file_path as photo,
                    (SELECT msg.message_text FROM messages msg WHERE msg.match_id = m.id ORDER BY msg.sent_at DESC LIMIT 1) as last_message,
                    (SELECT msg.sent_at FROM messages msg WHERE msg.match_id = m.id ORDER BY msg.sent_at DESC LIMIT 1) as last_message_at,
                    (SELECT COUNT(*) FROM messages msg WHERE msg.match_id = m.id AND msg.sender_id != ? AND msg.is_read = 0) as unread_count
             FROM matches m
             JOIN profiles p ON p.user_id = CASE WHEN m.user_1_id = ? THEN m.user_2_id ELSE m.user_1_id END
             LEFT JOIN photos ph ON ph.user_id = p.user_id AND ph.is_primary = 1
             WHERE m.user_1_id = ? OR m.user_2_id = ?
             ORDER BY last_message_at DESC, m.matched_at DESC",
            [$userId, $userId, $userId, $userId, $userId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Find a match between two users.
     */
    public static function findMatch(int $userId1, int $userId2): ?array
    {
        $u1 = min($userId1, $userId2);
        $u2 = max($userId1, $userId2);
        $stmt = static::db()->query(
            "SELECT * FROM matches WHERE user_1_id = ? AND user_2_id = ? LIMIT 1",
            [$u1, $u2]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Check if two users are matched.
     */
    public static function areMatched(int $userId1, int $userId2): bool
    {
        return self::findMatch($userId1, $userId2) !== null;
    }

    /**
     * Count matches for a user.
     */
    public static function countByUserId(int $userId): int
    {
        $stmt = static::db()->query(
            "SELECT COUNT(*) as cnt FROM matches WHERE user_1_id = ? OR user_2_id = ?",
            [$userId, $userId]
        );
        return (int) $stmt->fetch()['cnt'];
    }

    /**
     * Unmatch (delete match and messages).
     */
    public static function unmatch(int $matchId, int $userId): bool
    {
        $stmt = static::db()->query(
            "DELETE FROM matches WHERE id = ? AND (user_1_id = ? OR user_2_id = ?)",
            [$matchId, $userId, $userId]
        );
        return $stmt->rowCount() > 0;
    }
}
