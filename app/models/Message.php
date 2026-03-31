<?php
namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    protected static string $table = 'messages';

    /**
     * Get messages for a match (paginated).
     */
    public static function getByMatchId(int $matchId, int $limit = 50, int $offset = 0): array
    {
        $stmt = static::db()->query(
            "SELECT msg.*, p.name as sender_name, ph.file_path as sender_photo
             FROM messages msg
             JOIN profiles p ON p.user_id = msg.sender_id
             LEFT JOIN photos ph ON ph.user_id = msg.sender_id AND ph.is_primary = 1
             WHERE msg.match_id = ?
             ORDER BY msg.sent_at ASC
             LIMIT ? OFFSET ?",
            [$matchId, $limit, $offset]
        );
        return $stmt->fetchAll();
    }

    /**
     * Send a message.
     */
    public static function send(int $matchId, int $senderId, string $text): int
    {
        static::db()->query(
            "INSERT INTO messages (match_id, sender_id, message_text) VALUES (?, ?, ?)",
            [$matchId, $senderId, $text]
        );
        return (int) static::db()->lastInsertId();
    }

    /**
     * Mark messages as read.
     */
    public static function markRead(int $matchId, int $readerId): void
    {
        static::db()->query(
            "UPDATE messages SET is_read = 1 WHERE match_id = ? AND sender_id != ? AND is_read = 0",
            [$matchId, $readerId]
        );
    }

    /**
     * Get total unread count for a user.
     */
    public static function totalUnread(int $userId): int
    {
        $stmt = static::db()->query(
            "SELECT COUNT(*) as cnt FROM messages msg
             JOIN matches m ON m.id = msg.match_id
             WHERE (m.user_1_id = ? OR m.user_2_id = ?)
             AND msg.sender_id != ? AND msg.is_read = 0",
            [$userId, $userId, $userId]
        );
        return (int) $stmt->fetch()['cnt'];
    }

    /**
     * Get new messages since a given ID (for polling).
     */
    public static function getNewMessages(int $matchId, int $afterId): array
    {
        $stmt = static::db()->query(
            "SELECT msg.*, p.name as sender_name, ph.file_path as sender_photo
             FROM messages msg
             JOIN profiles p ON p.user_id = msg.sender_id
             LEFT JOIN photos ph ON ph.user_id = msg.sender_id AND ph.is_primary = 1
             WHERE msg.match_id = ? AND msg.id > ?
             ORDER BY msg.sent_at ASC",
            [$matchId, $afterId]
        );
        return $stmt->fetchAll();
    }
}
