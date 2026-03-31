<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\Config;
use App\Models\Discovery;
use App\Models\Interaction;
use App\Models\Profile;
use App\Models\Boost;

class DiscoverController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();

        // Get filter params from query string or session with validation
        $minAge = max(18, min(99, (int)($_GET['min_age'] ?? \App\Core\Session::get('filter_min_age', 18))));
        $maxAge = max(18, min(99, (int)($_GET['max_age'] ?? \App\Core\Session::get('filter_max_age', 99))));
        $maxDist = max(1, min(500, (int)($_GET['max_distance'] ?? \App\Core\Session::get('filter_max_distance', 100))));
        if ($minAge > $maxAge) { $minAge = 18; $maxAge = 99; }

        $filters = [
            'min_age'      => $minAge,
            'max_age'      => $maxAge,
            'max_distance' => $maxDist,
        ];

        // Save filters to session
        Session::set('filter_min_age', $filters['min_age']);
        Session::set('filter_max_age', $filters['max_age']);
        Session::set('filter_max_distance', $filters['max_distance']);

        $stack = Discovery::getStack($user['id'], $filters, 20);

        // Compute match reasons for each profile
        $stack = array_map(function ($person) {
            $person['match_reasons'] = self::computeReasons($person);
            $person['compatibility'] = (int)round((float)($person['total_score'] ?? 0));
            return $person;
        }, $stack);

        $swipesToday = Interaction::getTodaySwipeCount($user['id']);
        $userProfile = Profile::getByUserId($user['id']);
        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);
        $dailyLimit = Config::get('app.free_daily_swipes', 50);
        $likerCount = Interaction::getLikerCount($user['id']);
        $boostActive = Boost::isActive($user['id']);
        $boostRemaining = Boost::getRemainingSeconds($user['id']);

        View::render('discover/index', [
            'stack'          => $stack,
            'filters'        => $filters,
            'swipesToday'    => $swipesToday,
            'dailyLimit'     => $dailyLimit,
            'isPremium'      => $isPremium,
            'hasProfile'     => !empty($userProfile['name']),
            'likerCount'     => $likerCount,
            'boostActive'    => $boostActive,
            'boostRemaining' => $boostRemaining,
        ]);
    }

    /**
     * Compute top 3 match reasons from score components.
     */
    private static function computeReasons(array $person): array
    {
        $components = [
            ['key' => 'distance',  'score' => (float)($person['score_distance'] ?? 0),  'max' => 17, 'emoji' => '📍', 'label' => ''],
            ['key' => 'age',       'score' => (float)($person['score_age'] ?? 0),       'max' => 17, 'emoji' => '🎂', 'label' => 'Age match'],
            ['key' => 'interests', 'score' => (float)($person['score_interests'] ?? 0), 'max' => 17, 'emoji' => '🎯', 'label' => ''],
            ['key' => 'lifestyle', 'score' => (float)($person['score_lifestyle'] ?? 0), 'max' => 17, 'emoji' => '💫', 'label' => 'Similar lifestyle'],
            ['key' => 'elo',       'score' => (float)($person['score_elo'] ?? 0),       'max' => 16, 'emoji' => '⭐', 'label' => 'Popular profile'],
            ['key' => 'freshness', 'score' => (float)($person['score_freshness'] ?? 0), 'max' => 16, 'emoji' => '🆕', 'label' => ''],
        ];

        // Custom labels based on data
        foreach ($components as &$c) {
            if ($c['key'] === 'distance') {
                $dist = $person['distance'] ?? null;
                $c['label'] = $dist !== null ? round($dist, 1) . ' km away' : 'Nearby';
            }
            if ($c['key'] === 'interests') {
                $cnt = (int)($person['shared_interest_count'] ?? 0);
                $c['label'] = $cnt > 0 ? $cnt . ' shared interest' . ($cnt > 1 ? 's' : '') : 'Common interests';
            }
            if ($c['key'] === 'freshness') {
                $c['label'] = $c['score'] >= 15 ? 'Recently active' : ($c['score'] >= 12 ? 'Active today' : 'Active recently');
            }
        }
        unset($c);

        // Sort by contribution ratio descending
        usort($components, fn($a, $b) => ($b['score'] / $b['max']) <=> ($a['score'] / $a['max']));

        // Return top 3 with score > 0
        $reasons = [];
        foreach ($components as $c) {
            if ($c['score'] > 0 && count($reasons) < 3) {
                $reasons[] = $c['emoji'] . ' ' . $c['label'];
            }
        }

        return $reasons;
    }

    /**
     * Handle swipe action (AJAX endpoint).
     */
    public function swipe(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        // Validate CSRF via header for AJAX
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
        $stored = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $token)) {
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            echo json_encode(['error' => 'Invalid request']);
            return;
        }
        $targetId = (int)($input['target_id'] ?? 0);
        $rawType  = $input['type'] ?? '';

        // Map JS 'pass' to DB 'dislike', 'super_like' to 'superlike'
        $typeMap = ['like' => 'like', 'pass' => 'dislike', 'super_like' => 'superlike', 'superlike' => 'superlike', 'dislike' => 'dislike'];
        $action = $typeMap[$rawType] ?? '';

        if ($action === '') {
            echo json_encode(['error' => 'Invalid action']);
            return;
        }

        if ($targetId <= 0 || $targetId === $user['id']) {
            echo json_encode(['error' => 'Invalid target']);
            return;
        }

        // Check swipe limit for free users
        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);
        if (!$isPremium) {
            $dailyLimit = Config::get('app.free_daily_swipes', 50);
            if (Interaction::getTodaySwipeCount($user['id']) >= $dailyLimit) {
                echo json_encode(['error' => 'Daily swipe limit reached. Go premium for unlimited!', 'limit_reached' => true]);
                return;
            }
        }

        $result = Interaction::create($user['id'], $targetId, $action);

        $response = ['result' => $result, 'match' => false];
        if ($result === 'match') {
            $matchProfile = Profile::getFullProfile($targetId);
            $response['match'] = true;
            $response['match_name'] = $matchProfile['name'] ?? 'Someone';
            $response['match_photo'] = $matchProfile['primary_photo'] ?? null;
        }

        echo json_encode($response);
    }

    /**
     * Rewind (undo) last swipe. Premium only (AJAX).
     */
    public function rewind(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $token)) {
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);
        if (!$isPremium) {
            echo json_encode(['error' => 'Premium required', 'premium_required' => true]);
            return;
        }

        $targetId = Interaction::undoLast($user['id']);
        if (!$targetId) {
            echo json_encode(['error' => 'Nothing to undo']);
            return;
        }

        // Return the undone profile so the card can be re-inserted
        $profile = Profile::getFullProfile($targetId);
        echo json_encode([
            'success'   => true,
            'target_id' => $targetId,
            'name'      => $profile['name'] ?? 'Unknown',
            'photo'     => $profile['primary_photo'] ?? null,
            'age'       => $profile['date_of_birth'] ? (int)date_diff(date_create($profile['date_of_birth']), date_create())->y : null,
            'city'      => $profile['city'] ?? '',
        ]);
    }

    /**
     * Activate a profile boost. Premium only (AJAX).
     */
    public function boost(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $token)) {
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);
        if (!$isPremium) {
            echo json_encode(['error' => 'Premium required', 'premium_required' => true]);
            return;
        }

        if (Boost::isActive($user['id'])) {
            $remaining = Boost::getRemainingSeconds($user['id']);
            echo json_encode(['error' => 'Boost already active', 'remaining' => $remaining]);
            return;
        }

        Boost::activate($user['id'], 30, 3.0);
        echo json_encode(['success' => true, 'minutes' => 30, 'remaining' => 1800]);
    }
}
