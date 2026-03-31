<?php
namespace App\Models;

use App\Core\Model;

class Discovery extends Model
{
    protected static string $table = 'profiles';

    /**
     * Get discovery stack: profiles the user hasn't interacted with,
     * filtered by preferences, sorted by distance.
     */
    public static function getStack(int $userId, array $filters = [], int $limit = 10): array
    {
        $params = [$userId, $userId, $userId, $userId];
        $where  = [];

        // Base: exclude self, already interacted, blocked
        $sql = "SELECT u.id as user_id, u.is_premium,
                       p.name, p.bio, p.date_of_birth, p.gender, p.looking_for,
                       p.relationship_goal, p.height_cm, p.smoking, p.drinking,
                       p.city, p.country, p.latitude, p.longitude,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                       ph.file_path as primary_photo";

        // Get user's location for distance calculation
        $userProfile = static::db()->query(
            "SELECT latitude, longitude, looking_for, gender FROM profiles WHERE user_id = ?",
            [$userId]
        )->fetch();

        if ($userProfile && $userProfile['latitude'] && $userProfile['longitude']) {
            $lat = (float)$userProfile['latitude'];
            $lng = (float)$userProfile['longitude'];
            $sql .= ", (6371 * acos(cos(radians(?)) * cos(radians(p.latitude))
                     * cos(radians(p.longitude) - radians(?))
                     + sin(radians(?)) * sin(radians(p.latitude)))) AS distance";
            $params = [$lat, $lng, $lat, $userId, $userId, $userId, $userId];
        } else {
            $sql .= ", NULL as distance";
        }

        $sql .= " FROM users u
                   JOIN profiles p ON p.user_id = u.id
                   LEFT JOIN photos ph ON ph.user_id = u.id AND ph.is_primary = 1
                   WHERE u.id != ?
                   AND u.status = 'active'
                   AND NOT EXISTS (
                       SELECT 1 FROM interactions i WHERE i.actor_id = ? AND i.target_id = u.id
                   )
                   AND NOT EXISTS (
                       SELECT 1 FROM blocks b WHERE (b.blocker_id = ? AND b.blocked_id = u.id)
                                                 OR (b.blocker_id = u.id AND b.blocked_id = ?)
                   )";

        // Gender preference filter
        if ($userProfile && $userProfile['looking_for'] && $userProfile['looking_for'] !== 'everyone') {
            $where[] = "p.gender = ?";
            $params[] = $userProfile['looking_for'];
        }

        // Age filter
        if (!empty($filters['min_age'])) {
            $where[] = "TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) >= ?";
            $params[] = (int) $filters['min_age'];
        }
        if (!empty($filters['max_age'])) {
            $where[] = "TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) <= ?";
            $params[] = (int) $filters['max_age'];
        }

        // Distance filter (km)
        if (!empty($filters['max_distance']) && $userProfile && $userProfile['latitude']) {
            $where[] = "(6371 * acos(cos(radians(?)) * cos(radians(p.latitude))
                        * cos(radians(p.longitude) - radians(?))
                        + sin(radians(?)) * sin(radians(p.latitude)))) <= ?";
            $lat = (float)$userProfile['latitude'];
            $lng = (float)$userProfile['longitude'];
            $params[] = $lat;
            $params[] = $lng;
            $params[] = $lat;
            $params[] = (int) $filters['max_distance'];
        }

        // Must have a name set (profile completed)
        $where[] = "p.name != ''";

        if (!empty($where)) {
            $sql .= " AND " . implode(" AND ", $where);
        }

        // Order: premium first, then by distance or random
        if ($userProfile && $userProfile['latitude'] && $userProfile['longitude']) {
            $sql .= " ORDER BY u.is_premium DESC, distance ASC, RAND()";
        } else {
            $sql .= " ORDER BY u.is_premium DESC, RAND()";
        }

        $sql .= " LIMIT ?";
        $params[] = $limit;

        $stmt = static::db()->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get all photos for a user (for the swipe card detail view).
     */
    public static function getUserPhotos(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT file_path FROM photos WHERE user_id = ? ORDER BY is_primary DESC, uploaded_at ASC",
            [$userId]
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
