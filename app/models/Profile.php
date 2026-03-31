<?php
namespace App\Models;

use App\Core\Model;

class Profile extends Model
{
    protected static string $table = 'profiles';

    public static function getByUserId(int $userId): ?array
    {
        return static::findBy('user_id', $userId);
    }

    public static function update(int $userId, array $data): void
    {
        $allowed = ['name', 'bio', 'date_of_birth', 'gender', 'looking_for',
                     'relationship_goal', 'height_cm', 'smoking', 'drinking',
                     'latitude', 'longitude', 'city', 'country'];

        $fields = [];
        $values = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $values[] = $data[$col] === '' ? null : $data[$col];
            }
        }
        if (empty($fields)) return;

        $values[] = $userId;
        $sql = "UPDATE profiles SET " . implode(', ', $fields) . " WHERE user_id = ?";
        static::db()->query($sql, $values);
    }

    /**
     * Get full profile with primary photo for display.
     */
    public static function getFullProfile(int $userId): ?array
    {
        $sql = "SELECT u.id, u.email, u.is_premium, u.is_verified, u.email_verified_at, u.created_at as member_since,
                       p.name, p.bio, p.date_of_birth, p.gender, p.looking_for,
                       p.relationship_goal, p.height_cm, p.smoking, p.drinking,
                       p.city, p.country, p.latitude, p.longitude,
                       ph.file_path as primary_photo
                FROM users u
                JOIN profiles p ON p.user_id = u.id
                LEFT JOIN photos ph ON ph.user_id = u.id AND ph.is_primary = 1
                WHERE u.id = ? AND u.status = 'active'
                LIMIT 1";
        $stmt = static::db()->query($sql, [$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Calculate age from date of birth.
     */
    public static function calculateAge(?string $dob): ?int
    {
        if (!$dob) return null;
        return (int) date_diff(date_create($dob), date_create('today'))->y;
    }

    // ── Interest Tags ──────────────────────────────────────

    /**
     * Get all available interest tags grouped by category.
     */
    public static function getAllInterests(): array
    {
        $stmt = static::db()->query("SELECT * FROM interests ORDER BY category, name");
        return $stmt->fetchAll();
    }

    /**
     * Get interest IDs for a user.
     */
    public static function getUserInterestIds(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT interest_id FROM user_interests WHERE user_id = ?",
            [$userId]
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Save user interests (max 10).
     */
    public static function saveInterests(int $userId, array $interestIds): void
    {
        // Delete existing
        static::db()->query("DELETE FROM user_interests WHERE user_id = ?", [$userId]);

        // Insert new (max 10)
        $ids = array_slice(array_map('intval', $interestIds), 0, 10);
        foreach ($ids as $id) {
            static::db()->query(
                "INSERT IGNORE INTO user_interests (user_id, interest_id) VALUES (?, ?)",
                [$userId, $id]
            );
        }

        // Update profile completeness
        self::recalcCompleteness($userId);
    }

    // ── Deal-Breakers ──────────────────────────────────────

    /**
     * Get user's deal-breakers.
     */
    public static function getDealbreakers(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT field, value FROM user_dealbreakers WHERE user_id = ?",
            [$userId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Save deal-breaker. Free users: 1 max, premium: unlimited.
     */
    public static function saveDealbreakers(int $userId, array $dealbreakers, bool $isPremium): bool
    {
        $maxAllowed = $isPremium ? 100 : 1;
        $dealbreakers = array_slice($dealbreakers, 0, $maxAllowed);

        // Delete existing
        static::db()->query("DELETE FROM user_dealbreakers WHERE user_id = ?", [$userId]);

        $allowedFields = ['smoking'];
        foreach ($dealbreakers as $db) {
            $field = $db['field'] ?? '';
            $value = $db['value'] ?? '';
            if (!in_array($field, $allowedFields) || $value === '') continue;
            static::db()->query(
                "INSERT IGNORE INTO user_dealbreakers (user_id, field, value) VALUES (?, ?, ?)",
                [$userId, $field, $value]
            );
        }
        return true;
    }

    // ── Profile Completeness ───────────────────────────────

    /**
     * Recalculate and store profile completeness (0-100).
     */
    public static function recalcCompleteness(int $userId): int
    {
        $profile = static::getByUserId($userId);
        $score = 0;

        if (!empty($profile['name']))              $score += 10;
        if (!empty($profile['bio']))               $score += 15;
        if (!empty($profile['date_of_birth']))     $score += 10;
        if (!empty($profile['gender']) && !empty($profile['looking_for'])) $score += 10;
        if (!empty($profile['relationship_goal']) && $profile['relationship_goal'] !== 'undecided') $score += 10;
        if (!empty($profile['height_cm']))         $score += 5;
        if (!empty($profile['smoking']) || !empty($profile['drinking'])) $score += 5;

        // Photo + interest counts in a single query
        $counts = static::db()->query(
            "SELECT
                (SELECT COUNT(*) FROM photos WHERE user_id = ?) AS photo_count,
                (SELECT COUNT(*) FROM user_interests WHERE user_id = ?) AS interest_count",
            [$userId, $userId]
        )->fetch();
        $photoCount    = (int)$counts['photo_count'];
        $interestCount = (int)$counts['interest_count'];

        if ($photoCount >= 1) $score += 20;
        if ($photoCount >= 3) $score += 10;
        if ($interestCount >= 3) $score += 5;

        $score = min(100, $score);

        // Upsert user_scores
        static::db()->query(
            "INSERT INTO user_scores (user_id, profile_completeness, photo_count)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE profile_completeness = ?, photo_count = ?",
            [$userId, $score, $photoCount, $score, $photoCount]
        );

        return $score;
    }
}
