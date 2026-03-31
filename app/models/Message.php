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
     * Send a text message.
     */
    public static function send(int $matchId, int $senderId, string $text): int
    {
        static::db()->query(
            "INSERT INTO messages (match_id, sender_id, message_text, message_type) VALUES (?, ?, ?, 'text')",
            [$matchId, $senderId, $text]
        );
        return (int) static::db()->lastInsertId();
    }

    /**
     * Send a voice message.
     */
    public static function sendVoice(int $matchId, int $senderId, string $voicePath, int $duration): int
    {
        static::db()->query(
            "INSERT INTO messages (match_id, sender_id, message_text, message_type, voice_path, voice_duration)
             VALUES (?, ?, '', 'voice', ?, ?)",
            [$matchId, $senderId, $voicePath, $duration]
        );
        return (int) static::db()->lastInsertId();
    }

    /**
     * Mark messages as read (sets both is_read flag and read_at timestamp).
     */
    public static function markRead(int $matchId, int $readerId): void
    {
        static::db()->query(
            "UPDATE messages SET is_read = 1, read_at = NOW() WHERE match_id = ? AND sender_id != ? AND is_read = 0",
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

    /**
     * Get the very last message in a match (any sender).
     */
    public static function getLastInMatch(int $matchId): ?array
    {
        $stmt = static::db()->query(
            "SELECT * FROM messages WHERE match_id = ? ORDER BY sent_at DESC LIMIT 1",
            [$matchId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Check ghost status: returns hours since the other user last sent a message.
     * Null if no messages exist yet.
     */
    public static function getGhostInfo(int $matchId, int $currentUserId): ?array
    {
        // Last message from *other* person to us
        $stmt = static::db()->query(
            "SELECT * FROM messages WHERE match_id = ? AND sender_id != ? ORDER BY sent_at DESC LIMIT 1",
            [$matchId, $currentUserId]
        );
        $lastFromThem = $stmt->fetch();

        // Last message from us
        $stmt2 = static::db()->query(
            "SELECT * FROM messages WHERE match_id = ? AND sender_id = ? ORDER BY sent_at DESC LIMIT 1",
            [$matchId, $currentUserId]
        );
        $lastFromUs = $stmt2->fetch();

        if (!$lastFromThem && !$lastFromUs) return null;

        // If the last message overall is from them and we haven't replied in 72h
        $lastOverall = static::getLastInMatch($matchId);
        if (!$lastOverall) return null;

        $hoursSinceLastMsg = (time() - strtotime($lastOverall['sent_at'])) / 3600;
        $isOurTurn = $lastOverall['sender_id'] !== $currentUserId;
        $ghostHours = \App\Core\Config::get('app.ghost_nudge_hours', 72);

        return [
            'hours_since_last' => round($hoursSinceLastMsg, 1),
            'is_our_turn' => $isOurTurn,
            'last_sender_id' => (int)$lastOverall['sender_id'],
            'needs_nudge' => $isOurTurn && $hoursSinceLastMsg >= $ghostHours,
            'partner_waiting' => !$isOurTurn && $hoursSinceLastMsg >= $ghostHours,
        ];
    }
}
