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
        $sql = "SELECT u.id, u.email, u.is_premium, u.email_verified_at, u.created_at as member_since,
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
}
