<?php
namespace App\Models;

use App\Core\Model;

class Availability extends Model
{
    protected static string $table = 'availability_slots';

    private static array $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    public static function getDayName(int $day): string
    {
        return self::$dayNames[$day] ?? '';
    }

    public static function getDayNames(): array
    {
        return self::$dayNames;
    }

    public static function getByUserId(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM availability_slots WHERE user_id = ? ORDER BY day_of_week, start_time",
            [$userId]
        );
        return $stmt->fetchAll();
    }

    public static function saveSlots(int $userId, array $slots): void
    {
        // Delete existing
        static::db()->query("DELETE FROM availability_slots WHERE user_id = ?", [$userId]);

        // Insert new
        foreach ($slots as $slot) {
            $day = (int)($slot['day'] ?? -1);
            $start = $slot['start'] ?? '';
            $end = $slot['end'] ?? '';
            if ($day < 0 || $day > 6 || !$start || !$end) continue;
            if ($start >= $end) continue;

            static::db()->query(
                "INSERT INTO availability_slots (user_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)",
                [$userId, $day, $start, $end]
            );
        }
    }

    /**
     * Get overlapping availability between two users.
     */
    public static function getOverlap(int $userId1, int $userId2): array
    {
        $stmt = static::db()->query(
            "SELECT a.day_of_week,
                    GREATEST(a.start_time, b.start_time) as overlap_start,
                    LEAST(a.end_time, b.end_time) as overlap_end
             FROM availability_slots a
             JOIN availability_slots b ON a.day_of_week = b.day_of_week
                AND a.start_time < b.end_time AND b.start_time < a.end_time
             WHERE a.user_id = ? AND b.user_id = ?
             ORDER BY a.day_of_week, overlap_start",
            [$userId1, $userId2]
        );
        return $stmt->fetchAll();
    }

    /**
     * Check if a user has any availability set.
     */
    public static function hasSlots(int $userId): bool
    {
        $stmt = static::db()->query(
            "SELECT 1 FROM availability_slots WHERE user_id = ? LIMIT 1",
            [$userId]
        );
        return (bool)$stmt->fetch();
    }
}
