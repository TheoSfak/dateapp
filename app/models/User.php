<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';

    /**
     * Create a new user and return their ID.
     */
    public static function create(string $email, string $password): int
    {
        $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $token = bin2hex(random_bytes(32));

        static::db()->query(
            "INSERT INTO users (email, password_hash, verification_token) VALUES (?, ?, ?)",
            [$email, $hash, $token]
        );

        $userId = (int) static::db()->lastInsertId();

        // Create empty profile row
        static::db()->query(
            "INSERT INTO profiles (user_id) VALUES (?)",
            [$userId]
        );

        return $userId;
    }

    /**
     * Verify email with token.
     */
    public static function verifyEmail(string $token): bool
    {
        $stmt = static::db()->query(
            "UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE verification_token = ? AND email_verified_at IS NULL",
            [$token]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Authenticate by email/password. Returns user row or null.
     */
    public static function authenticate(string $email, string $password): ?array
    {
        $user = static::findBy('email', $email);
        if (!$user) {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }
        // Update last login
        static::db()->query("UPDATE users SET last_login_at = NOW() WHERE id = ?", [$user['id']]);
        return $user;
    }

    /**
     * Check if an email is already taken.
     */
    public static function emailExists(string $email): bool
    {
        return static::findBy('email', $email) !== null;
    }
}
