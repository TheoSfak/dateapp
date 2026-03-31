<?php
namespace App\Models;

use App\Core\Model;

class MiniGame extends Model
{
    protected static string $table = 'mini_games';

    // ── Question Banks ─────────────────────────────────
    private static array $wouldYouRather = [
        ['A' => 'Travel to the future', 'B' => 'Travel to the past'],
        ['A' => 'Always be overdressed', 'B' => 'Always be underdressed'],
        ['A' => 'Have unlimited money', 'B' => 'Have unlimited time'],
        ['A' => 'Live in the mountains', 'B' => 'Live by the ocean'],
        ['A' => 'Be able to fly', 'B' => 'Be able to read minds'],
        ['A' => 'Never use social media again', 'B' => 'Never watch a movie again'],
        ['A' => 'Have a personal chef', 'B' => 'Have a personal trainer'],
        ['A' => 'Speak every language', 'B' => 'Play every instrument'],
        ['A' => 'Always know the truth', 'B' => 'Always get away with lying'],
        ['A' => 'Be famous and poor', 'B' => 'Be unknown and rich'],
        ['A' => 'Live without music', 'B' => 'Live without TV'],
        ['A' => 'Relive the same day forever', 'B' => 'Never know what day it is'],
        ['A' => 'Have dinner with your future self', 'B' => 'Have dinner with your 10-year-old self'],
        ['A' => 'Only eat sweet food', 'B' => 'Only eat savory food'],
        ['A' => 'Be a morning person', 'B' => 'Be a night owl'],
        ['A' => 'Have a rewind button for life', 'B' => 'Have a pause button for life'],
        ['A' => 'Always be 10 minutes late', 'B' => 'Always be 20 minutes early'],
        ['A' => 'Know how you die', 'B' => 'Know when you die'],
        ['A' => 'Live in a treehouse', 'B' => 'Live in a houseboat'],
        ['A' => 'Have a photographic memory', 'B' => 'Have an incredibly fast metabolism'],
    ];

    private static array $thisOrThat = [
        ['A' => 'Coffee', 'B' => 'Tea'],
        ['A' => 'Netflix', 'B' => 'Going out'],
        ['A' => 'Cats', 'B' => 'Dogs'],
        ['A' => 'Summer', 'B' => 'Winter'],
        ['A' => 'Sweet', 'B' => 'Salty'],
        ['A' => 'Early bird', 'B' => 'Night owl'],
        ['A' => 'Text', 'B' => 'Call'],
        ['A' => 'City life', 'B' => 'Country life'],
        ['A' => 'Pizza', 'B' => 'Sushi'],
        ['A' => 'Books', 'B' => 'Podcasts'],
        ['A' => 'Beach vacation', 'B' => 'Mountain trip'],
        ['A' => 'Cooking at home', 'B' => 'Eating out'],
        ['A' => 'Road trip', 'B' => 'Plane trip'],
        ['A' => 'Spontaneous', 'B' => 'Planned'],
        ['A' => 'Introvert', 'B' => 'Extrovert'],
        ['A' => 'Rain', 'B' => 'Sunshine'],
        ['A' => 'Breakfast', 'B' => 'Dinner'],
        ['A' => 'Comedy', 'B' => 'Drama'],
        ['A' => 'Wine', 'B' => 'Beer'],
        ['A' => 'Sneakers', 'B' => 'Dress shoes'],
    ];

    private static array $trivia = [
        ['q' => 'What is the most spoken language in the world?', 'a' => 'English', 'options' => ['English', 'Mandarin', 'Spanish', 'Hindi']],
        ['q' => 'What planet is known as the Red Planet?', 'a' => 'Mars', 'options' => ['Jupiter', 'Mars', 'Venus', 'Saturn']],
        ['q' => 'Which country has the most islands?', 'a' => 'Sweden', 'options' => ['Indonesia', 'Philippines', 'Sweden', 'Japan']],
        ['q' => 'What year was the first iPhone released?', 'a' => '2007', 'options' => ['2005', '2006', '2007', '2008']],
        ['q' => 'Which ocean is the largest?', 'a' => 'Pacific', 'options' => ['Atlantic', 'Indian', 'Pacific', 'Arctic']],
        ['q' => 'How many bones does an adult human have?', 'a' => '206', 'options' => ['186', '196', '206', '216']],
        ['q' => 'What is the capital of Australia?', 'a' => 'Canberra', 'options' => ['Sydney', 'Melbourne', 'Canberra', 'Brisbane']],
        ['q' => 'How many hearts does an octopus have?', 'a' => '3', 'options' => ['1', '2', '3', '5']],
        ['q' => 'In what year did the Titanic sink?', 'a' => '1912', 'options' => ['1905', '1912', '1918', '1920']],
        ['q' => 'What is the smallest country in the world?', 'a' => 'Vatican City', 'options' => ['Monaco', 'Vatican City', 'San Marino', 'Liechtenstein']],
        ['q' => 'What element does "O" represent on the periodic table?', 'a' => 'Oxygen', 'options' => ['Gold', 'Oxygen', 'Osmium', 'Oganesson']],
        ['q' => 'Which planet has the most moons?', 'a' => 'Saturn', 'options' => ['Jupiter', 'Saturn', 'Uranus', 'Neptune']],
        ['q' => 'What is the hardest natural substance?', 'a' => 'Diamond', 'options' => ['Titanium', 'Diamond', 'Quartz', 'Topaz']],
        ['q' => 'How many colors are in a rainbow?', 'a' => '7', 'options' => ['5', '6', '7', '8']],
        ['q' => 'What is the longest river in the world?', 'a' => 'Nile', 'options' => ['Amazon', 'Nile', 'Yangtze', 'Mississippi']],
        ['q' => 'Who painted the Mona Lisa?', 'a' => 'Leonardo da Vinci', 'options' => ['Michelangelo', 'Leonardo da Vinci', 'Raphael', 'Botticelli']],
        ['q' => 'What gas do plants absorb from the atmosphere?', 'a' => 'Carbon dioxide', 'options' => ['Oxygen', 'Nitrogen', 'Carbon dioxide', 'Hydrogen']],
        ['q' => 'Which country invented pizza?', 'a' => 'Italy', 'options' => ['Greece', 'Italy', 'Turkey', 'France']],
        ['q' => 'How many strings does a standard guitar have?', 'a' => '6', 'options' => ['4', '5', '6', '8']],
        ['q' => 'What is the currency of Japan?', 'a' => 'Yen', 'options' => ['Won', 'Yuan', 'Yen', 'Ringgit']],
    ];

    /**
     * Get questions for a game type, seeded by game ID for consistency.
     */
    public static function getQuestions(string $type, int $gameId, int $totalRounds): array
    {
        $bank = match ($type) {
            'would_you_rather' => self::$wouldYouRather,
            'this_or_that'     => self::$thisOrThat,
            'trivia'           => self::$trivia,
            default            => self::$wouldYouRather,
        };

        // Deterministic shuffle using game ID as seed
        mt_srand($gameId * 7919);
        $indices = range(0, count($bank) - 1);
        shuffle($indices);
        mt_srand();

        $questions = [];
        for ($i = 0; $i < min($totalRounds, count($bank)); $i++) {
            $questions[] = $bank[$indices[$i]];
        }
        return $questions;
    }

    /**
     * Create a new game session.
     */
    public static function create(int $matchId, int $startedBy, string $type, int $rounds = 5): int
    {
        static::db()->query(
            "INSERT INTO mini_games (match_id, started_by, game_type, current_round, total_rounds, status)
             VALUES (?, ?, ?, 1, ?, 'active')",
            [$matchId, $startedBy, $type, $rounds]
        );
        return (int) static::db()->lastInsertId();
    }

    /**
     * Get the active game for a match (if any).
     */
    public static function getActiveForMatch(int $matchId): ?array
    {
        $stmt = static::db()->query(
            "SELECT * FROM mini_games WHERE match_id = ? AND status IN ('waiting','active') ORDER BY id DESC LIMIT 1",
            [$matchId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get a game by ID, ensure it belongs to this match.
     */
    public static function getForMatch(int $gameId, int $matchId): ?array
    {
        $stmt = static::db()->query(
            "SELECT * FROM mini_games WHERE id = ? AND match_id = ?",
            [$gameId, $matchId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Record an answer and advance the round if both players answered.
     */
    public static function submitAnswer(int $gameId, int $userId, int $roundNum, string $answer): array
    {
        // Insert answer (ignore duplicate)
        static::db()->query(
            "INSERT IGNORE INTO mini_game_answers (game_id, user_id, round_num, answer) VALUES (?, ?, ?, ?)",
            [$gameId, $userId, $roundNum, $answer]
        );

        // Count answers for this round
        $stmt = static::db()->query(
            "SELECT COUNT(*) as cnt FROM mini_game_answers WHERE game_id = ? AND round_num = ?",
            [$gameId, $roundNum]
        );
        $count = (int) $stmt->fetch()['cnt'];

        // Load game
        $game = static::findById($gameId);
        if (!$game) return ['error' => 'Game not found'];

        // If both answered, advance round
        if ($count >= 2) {
            $nextRound = $roundNum + 1;
            if ($nextRound > $game['total_rounds']) {
                // Game finished
                static::db()->query(
                    "UPDATE mini_games SET status = 'finished', finished_at = NOW() WHERE id = ?",
                    [$gameId]
                );
                return ['status' => 'finished', 'round' => $roundNum];
            } else {
                static::db()->query(
                    "UPDATE mini_games SET current_round = ? WHERE id = ?",
                    [$nextRound, $gameId]
                );
                return ['status' => 'next_round', 'round' => $nextRound];
            }
        }

        return ['status' => 'waiting', 'round' => $roundNum];
    }

    /**
     * Get all answers for a game.
     */
    public static function getAnswers(int $gameId): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM mini_game_answers WHERE game_id = ? ORDER BY round_num ASC, user_id ASC",
            [$gameId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get answers for a specific round.
     */
    public static function getRoundAnswers(int $gameId, int $roundNum): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM mini_game_answers WHERE game_id = ? AND round_num = ?",
            [$gameId, $roundNum]
        );
        return $stmt->fetchAll();
    }

    /**
     * Check if user already answered this round.
     */
    public static function hasAnswered(int $gameId, int $userId, int $roundNum): bool
    {
        $stmt = static::db()->query(
            "SELECT 1 FROM mini_game_answers WHERE game_id = ? AND user_id = ? AND round_num = ?",
            [$gameId, $userId, $roundNum]
        );
        return (bool) $stmt->fetch();
    }

    /**
     * Recent completed games for a match (for chat display).
     */
    public static function getCompletedForMatch(int $matchId, int $limit = 5): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM mini_games WHERE match_id = ? AND status = 'finished' ORDER BY finished_at DESC LIMIT ?",
            [$matchId, $limit]
        );
        return $stmt->fetchAll();
    }
}
