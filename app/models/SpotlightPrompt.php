<?php
namespace App\Models;

use App\Core\Model;

class SpotlightPrompt extends Model
{
    protected static string $table = 'spotlight_prompts';

    /**
     * Get all active prompts.
     */
    public static function getActive(): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM spotlight_prompts WHERE is_active = 1 ORDER BY id"
        );
        return $stmt->fetchAll();
    }

    /**
     * Get a random prompt the user hasn't answered yet.
     */
    public static function getUnanswered(int $userId, int $limit = 3): array
    {
        $stmt = static::db()->query(
            "SELECT sp.* FROM spotlight_prompts sp
             WHERE sp.is_active = 1
             AND sp.id NOT IN (SELECT prompt_id FROM user_prompt_answers WHERE user_id = ?)
             ORDER BY RAND() LIMIT ?",
            [$userId, $limit]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get the user's answered prompts (with prompt text).
     */
    public static function getUserAnswers(int $userId): array
    {
        $stmt = static::db()->query(
            "SELECT upa.*, sp.prompt, sp.emoji, sp.category
             FROM user_prompt_answers upa
             JOIN spotlight_prompts sp ON sp.id = upa.prompt_id
             WHERE upa.user_id = ?
             ORDER BY upa.updated_at DESC",
            [$userId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Save (insert or update) a user's answer.
     */
    public static function saveAnswer(int $userId, int $promptId, string $answer): void
    {
        static::db()->query(
            "INSERT INTO user_prompt_answers (user_id, prompt_id, answer)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE answer = VALUES(answer), updated_at = NOW()",
            [$userId, $promptId, $answer]
        );
    }

    /**
     * Delete a user's answer.
     */
    public static function deleteAnswer(int $userId, int $promptId): void
    {
        static::db()->query(
            "DELETE FROM user_prompt_answers WHERE user_id = ? AND prompt_id = ?",
            [$userId, $promptId]
        );
    }

    /**
     * Get prompt answers for display on a discover card (up to 2).
     */
    public static function getForDiscoverCard(int $userId, int $limit = 2): array
    {
        $stmt = static::db()->query(
            "SELECT sp.prompt, sp.emoji, upa.answer
             FROM user_prompt_answers upa
             JOIN spotlight_prompts sp ON sp.id = upa.prompt_id
             WHERE upa.user_id = ?
             ORDER BY upa.updated_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
        return $stmt->fetchAll();
    }
}
