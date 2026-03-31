<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Config;

class Photo extends Model
{
    protected static string $table = 'photos';

    public static function getByUserId(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM photos WHERE user_id = ? ORDER BY is_primary DESC, uploaded_at ASC",
            [$userId]
        );
        return $stmt->fetchAll();
    }

    public static function countByUserId(int $userId): int
    {
        $stmt = static::db()->query("SELECT COUNT(*) as cnt FROM photos WHERE user_id = ?", [$userId]);
        return (int) $stmt->fetch()['cnt'];
    }

    /**
     * Upload and store a photo. Returns the photo record or null on failure.
     */
    public static function upload(int $userId, array $file, bool $isPrimary = false): ?array
    {
        // Validate
        $maxSize = Config::get('app.max_photo_size', 5 * 1024 * 1024);
        $allowedTypes = Config::get('app.allowed_photo_types', ['image/jpeg', 'image/png', 'image/webp']);
        $maxPhotos = Config::get('app.max_photos_per_user', 6);

        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $maxSize) return null;

        // Verify MIME type using finfo (not trusting client)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedTypes, true)) return null;

        // Check limit
        if (self::countByUserId($userId) >= $maxPhotos) return null;

        // Generate safe filename
        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/uploads/photos/';
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return null;

        // If setting as primary, unset existing primary
        if ($isPrimary) {
            static::db()->query("UPDATE photos SET is_primary = 0 WHERE user_id = ?", [$userId]);
        }

        static::db()->query(
            "INSERT INTO photos (user_id, file_path, is_primary) VALUES (?, ?, ?)",
            [$userId, 'uploads/photos/' . $filename, $isPrimary ? 1 : 0]
        );

        return [
            'id'        => (int) static::db()->lastInsertId(),
            'file_path' => 'uploads/photos/' . $filename,
            'is_primary' => $isPrimary,
        ];
    }

    public static function setPrimary(int $photoId, int $userId): bool
    {
        static::db()->query("UPDATE photos SET is_primary = 0 WHERE user_id = ?", [$userId]);
        $stmt = static::db()->query(
            "UPDATE photos SET is_primary = 1 WHERE id = ? AND user_id = ?",
            [$photoId, $userId]
        );
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $photoId, int $userId): bool
    {
        $photo = static::findById($photoId);
        if (!$photo || $photo['user_id'] !== $userId) return false;

        $filePath = BASE_PATH . '/public/' . $photo['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        static::db()->query("DELETE FROM photos WHERE id = ? AND user_id = ?", [$photoId, $userId]);
        return true;
    }
}
