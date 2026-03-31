<?php
namespace App\Models;

use App\Core\Model;

class Discovery extends Model
{
    protected static string $table = 'profiles';

    private static function haversine(string $latParam, string $lngParam): string
    {
        return "(6371 * acos(LEAST(1, cos(radians({$latParam})) * cos(radians(p.latitude))
                 * cos(radians(p.longitude) - radians({$lngParam}))
                 + sin(radians({$latParam})) * sin(radians(p.latitude)))))";
    }

    /**
     * Get discovery stack with weighted compatibility scoring.
     *
     * Score formula (0-100):
     *   Distance (17) + Age Fit (17) + Shared Interests (17)
     *   + Lifestyle Match (17) + ELO/Quality (16) + Freshness (16)
     */
    public static function getStack(int $userId, array $filters = [], int $limit = 20): array
    {
        // Fetch current user's profile + interest count
        $me = static::db()->query(
            "SELECT p.latitude, p.longitude, p.looking_for, p.gender,
                    p.relationship_goal, p.smoking, p.drinking,
                    p.date_of_birth,
                    (SELECT COUNT(*) FROM user_interests WHERE user_id = ?) AS my_interest_count
             FROM profiles p WHERE p.user_id = ?",
            [$userId, $userId]
        )->fetch();

        if (!$me) return [];

        $hasLocation = $me['latitude'] && $me['longitude'];
        $lat = (float)($me['latitude'] ?? 0);
        $lng = (float)($me['longitude'] ?? 0);
        $maxDist = max(1, (int)($filters['max_distance'] ?? 100));
        $myInterestCount = max(1, (int)$me['my_interest_count']);

        // Preferred age midpoint from filters
        $minAge = (int)($filters['min_age'] ?? 18);
        $maxAge = (int)($filters['max_age'] ?? 99);
        $ageMid = ($minAge + $maxAge) / 2.0;
        $ageHalf = max(1, ($maxAge - $minAge) / 2.0);

        $params = [];

        // ── Distance score (17 pts) ────────────────────────
        if ($hasLocation) {
            $haversine = self::haversine('?', '?');
            $distScoreExpr = "ROUND(17 * GREATEST(0, 1 - {$haversine} / ?), 2)";
            $params = array_merge($params, [$lat, $lng, $lat, $maxDist]);
        } else {
            $distScoreExpr = "8.5"; // neutral if no location
        }

        // ── Age score (17 pts) ─────────────────────────────
        $ageExpr = "TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE())";
        $ageScoreExpr = "ROUND(17 * GREATEST(0, 1 - ABS({$ageExpr} - ?) / ?), 2)";
        $params[] = $ageMid;
        $params[] = $ageHalf;

        // ── Shared interests score (17 pts) ────────────────
        $interestScoreExpr = "ROUND(17 * (IFNULL(shared.cnt, 0) / ?), 2)";
        $params[] = $myInterestCount;

        // ── Lifestyle score (17 pts) ───────────────────────
        // Goal match: 6 pts, Smoking match: 5.5 pts, Drinking match: 5.5 pts
        $goalScore = "CASE WHEN p.relationship_goal = ? THEN 6
                           WHEN p.relationship_goal IS NULL OR ? IS NULL OR ? = 'undecided' OR p.relationship_goal = 'undecided' THEN 3
                           ELSE 0 END";
        $params[] = $me['relationship_goal'] ?? 'undecided';
        $params[] = $me['relationship_goal'] ?? 'undecided';
        $params[] = $me['relationship_goal'] ?? 'undecided';

        $smokingScore = "CASE WHEN p.smoking = ? THEN 5.5
                              WHEN p.smoking IS NULL OR ? IS NULL THEN 2.75
                              ELSE 0 END";
        $params[] = $me['smoking'] ?? '';
        $params[] = $me['smoking'] ?? '';

        $drinkingScore = "CASE WHEN p.drinking = ? THEN 5.5
                               WHEN p.drinking IS NULL OR ? IS NULL THEN 2.75
                               ELSE 0 END";
        $params[] = $me['drinking'] ?? '';
        $params[] = $me['drinking'] ?? '';

        $lifestyleScoreExpr = "ROUND(({$goalScore}) + ({$smokingScore}) + ({$drinkingScore}), 2)";

        // ── ELO/Quality score (16 pts) ─────────────────────
        $eloScoreExpr = "ROUND(16 * (LEAST(IFNULL(us.elo_score, 1000), 2000) / 2000), 2)";

        // ── Freshness score (16 pts) ───────────────────────
        $freshnessExpr = "CASE
            WHEN u.created_at > NOW() - INTERVAL 48 HOUR THEN 16
            WHEN u.last_active_at > NOW() - INTERVAL 1 HOUR THEN 15
            WHEN u.last_active_at > NOW() - INTERVAL 24 HOUR THEN 12
            WHEN u.last_active_at > NOW() - INTERVAL 72 HOUR THEN 8
            WHEN u.last_active_at > NOW() - INTERVAL 7 DAY THEN 4
            ELSE 1
        END";

        // ── Total score ────────────────────────────────────
        $totalScoreExpr = "({$distScoreExpr} + {$ageScoreExpr} + {$interestScoreExpr} + {$lifestyleScoreExpr} + {$eloScoreExpr} + {$freshnessExpr})";

        // ── Distance raw for display ───────────────────────
        if ($hasLocation) {
            $distRawExpr = self::haversine('?', '?');
            $distRawParams = [$lat, $lng, $lat];
        } else {
            $distRawExpr = "NULL";
            $distRawParams = [];
        }

        // ── Build SQL ──────────────────────────────────────
        $sql = "SELECT u.id AS user_id, u.is_premium,
                       p.name, p.bio, p.date_of_birth, p.gender, p.looking_for,
                       p.relationship_goal, p.height_cm, p.smoking, p.drinking,
                       p.city, p.country, p.latitude, p.longitude,
                       {$ageExpr} AS age,
                       ph.file_path AS primary_photo,
                       {$distRawExpr} AS distance,
                       IFNULL(shared.cnt, 0) AS shared_interest_count,
                       shared.names AS shared_interest_names,

                       -- Component scores for reason display
                       {$distScoreExpr} AS score_distance,
                       {$ageScoreExpr} AS score_age,
                       {$interestScoreExpr} AS score_interests,
                       {$lifestyleScoreExpr} AS score_lifestyle,
                       {$eloScoreExpr} AS score_elo,
                       {$freshnessExpr} AS score_freshness,
                       ROUND({$totalScoreExpr}, 1) AS total_score

                FROM users u
                JOIN profiles p ON p.user_id = u.id
                LEFT JOIN photos ph ON ph.user_id = u.id AND ph.is_primary = 1
                LEFT JOIN user_scores us ON us.user_id = u.id
                LEFT JOIN (
                    SELECT ui2.user_id,
                           COUNT(*) AS cnt,
                           GROUP_CONCAT(i.name ORDER BY i.name SEPARATOR ',') AS names
                    FROM user_interests ui2
                    JOIN interests i ON i.id = ui2.interest_id
                    WHERE ui2.interest_id IN (SELECT interest_id FROM user_interests WHERE user_id = ?)
                    GROUP BY ui2.user_id
                ) shared ON shared.user_id = u.id

                WHERE u.id != ?
                  AND u.status = 'active'
                  AND p.name != ''
                  AND NOT EXISTS (
                      SELECT 1 FROM interactions i2 WHERE i2.actor_id = ? AND i2.target_id = u.id
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM blocks b WHERE (b.blocker_id = ? AND b.blocked_id = u.id)
                                                OR (b.blocker_id = u.id AND b.blocked_id = ?)
                  )";

        // Build params in SQL placeholder order:
        //   1. distRaw (3 ?s if location)
        //   2. individual score_* columns (14 ?s)
        //   3. total_score re-expands all score expressions (14 ?s again)
        //   4. WHERE clause params
        $allParams = array_merge($distRawParams, $params, $params);
        $allParams[] = $userId; // shared interests subquery
        $allParams[] = $userId; // u.id != ?
        $allParams[] = $userId; // interactions
        $allParams[] = $userId; // blocks blocker
        $allParams[] = $userId; // blocks blocked

        // ── Bidirectional preference ───────────────────────
        // My looking_for filters their gender (already above in score)
        if ($me['looking_for'] && $me['looking_for'] !== 'everyone') {
            $sql .= " AND p.gender = ?";
            $allParams[] = $me['looking_for'];
        }
        // Their looking_for must include my gender
        if ($me['gender']) {
            $sql .= " AND (p.looking_for = 'everyone' OR p.looking_for = ?)";
            $allParams[] = $me['gender'];
        }

        // ── Age filter ─────────────────────────────────────
        if (!empty($filters['min_age'])) {
            $sql .= " AND {$ageExpr} >= ?";
            $allParams[] = (int)$filters['min_age'];
        }
        if (!empty($filters['max_age'])) {
            $sql .= " AND {$ageExpr} <= ?";
            $allParams[] = (int)$filters['max_age'];
        }

        // ── Distance filter (reuse haversine helper) ──────
        if (!empty($filters['max_distance']) && $hasLocation) {
            $sql .= " AND " . self::haversine('?', '?') . " <= ?";
            $allParams[] = $lat;
            $allParams[] = $lng;
            $allParams[] = $lat;
            $allParams[] = (int)$filters['max_distance'];
        }

        // ── Deal-breaker exclusion ─────────────────────────
        $sql .= " AND NOT EXISTS (
            SELECT 1 FROM user_dealbreakers ud
            WHERE ud.user_id = ?
              AND (
                  (ud.field = 'smoking' AND (p.smoking IS NULL OR p.smoking != ud.value))
              )
        )";
        $allParams[] = $userId;

        // ── Boost multiplier for ordering ──────────────────
        // Users with an active boost get their score multiplied
        $sql .= " ORDER BY (total_score * IFNULL(
            (SELECT b.multiplier FROM profile_boosts b WHERE b.user_id = u.id AND b.expires_at > NOW() ORDER BY b.expires_at DESC LIMIT 1),
            1.0
        )) DESC, RAND() LIMIT ?";
        $allParams[] = $limit;

        assert(
            substr_count($sql, '?') === count($allParams),
            'Discovery::getStack param count mismatch: '
            . substr_count($sql, '?') . ' placeholders vs '
            . count($allParams) . ' params'
        );

        $stmt = static::db()->query($sql, $allParams);
        return $stmt->fetchAll();
    }
}
