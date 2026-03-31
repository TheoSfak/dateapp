<?php
namespace App\Models;

use App\Core\Model;

class Verification extends Model
{
    protected static string $table = 'verification_requests';

    private static array $gestures = [
        'peace'      => '✌️ Show a peace sign',
        'thumbs_up'  => '👍 Give a thumbs up',
        'wave'       => '👋 Wave at the camera',
        'point_up'   => '☝️ Point one finger up',
        'ok_sign'    => '👌 Make an OK sign',
        'fist'       => '✊ Show a fist bump',
    ];

    public static function getRandomGesture(): array
    {
        $keys = array_keys(self::$gestures);
        $key = $keys[array_rand($keys)];
        return ['key' => $key, 'label' => self::$gestures[$key]];
    }

    public static function getGestureLabel(string $key): string
    {
        return self::$gestures[$key] ?? $key;
    }

    public static function hasPending(int $userId): ?array
    {
        $stmt = static::db()->query(
            "SELECT * FROM verification_requests WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $userId, string $gesture, string $photoPath): int
    {
        static::db()->query(
            "INSERT INTO verification_requests (user_id, gesture, photo_path) VALUES (?, ?, ?)",
            [$userId, $gesture, $photoPath]
        );
        return (int)static::db()->lastInsertId();
    }

    public static function getLatestForUser(int $userId): ?array
    {
        $stmt = static::db()->query(
            "SELECT * FROM verification_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function approve(int $requestId, int $adminId): void
    {
        $req = static::findById($requestId);
        if (!$req) return;

        static::db()->query(
            "UPDATE verification_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
            [$adminId, $requestId]
        );
        static::db()->query(
            "UPDATE users SET is_verified = 1 WHERE id = ?",
            [$req['user_id']]
        );
    }

    public static function reject(int $requestId, int $adminId): void
    {
        static::db()->query(
            "UPDATE verification_requests SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
            [$adminId, $requestId]
        );
    }

    public static function getPending(int $limit = 50): array
    {
        $stmt = static::db()->query(
            "SELECT vr.*, p.name, 
                    (SELECT file_path FROM photos WHERE user_id = vr.user_id AND is_primary = 1 LIMIT 1) as profile_photo
             FROM verification_requests vr
             JOIN profiles p ON p.user_id = vr.user_id
             WHERE vr.status = 'pending'
             ORDER BY vr.created_at ASC
             LIMIT ?",
            [$limit]
        );
        return $stmt->fetchAll();
    }

    public static function isVerified(int $userId): bool
    {
        $stmt = static::db()->query(
            "SELECT is_verified FROM users WHERE id = ?",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row && (bool)$row['is_verified'];
    }
}
