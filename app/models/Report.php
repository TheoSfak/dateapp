<?php
namespace App\Models;

use App\Core\Model;

class Report extends Model
{
    protected static string $table = 'reports';

    public static function create(int $reporterId, int $reportedId, string $reason): int
    {
        static::db()->query(
            "INSERT INTO reports (reporter_id, reported_id, reason) VALUES (?, ?, ?)",
            [$reporterId, $reportedId, $reason]
        );
        return (int) static::db()->lastInsertId();
    }

    public static function getPending(int $limit = 50): array
    {
        $stmt = static::db()->query(
            "SELECT r.*, 
                    rp.name as reporter_name, 
                    rd.name as reported_name,
                    ru.email as reported_email,
                    ru.status as reported_status
             FROM reports r
             JOIN profiles rp ON rp.user_id = r.reporter_id
             JOIN profiles rd ON rd.user_id = r.reported_id
             JOIN users ru ON ru.id = r.reported_id
             WHERE r.status = 'pending'
             ORDER BY r.created_at DESC
             LIMIT ?",
            [$limit]
        );
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $reportId, string $status): void
    {
        static::db()->query(
            "UPDATE reports SET status = ? WHERE id = ?",
            [$status, $reportId]
        );
    }
}
