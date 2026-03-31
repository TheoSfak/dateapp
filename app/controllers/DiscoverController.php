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

class DiscoverController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();

        // Get filter params from query string or session
        $filters = [
            'min_age'      => $_GET['min_age'] ?? Session::get('filter_min_age', 18),
            'max_age'      => $_GET['max_age'] ?? Session::get('filter_max_age', 99),
            'max_distance' => $_GET['max_distance'] ?? Session::get('filter_max_distance', 100),
        ];

        // Save filters to session
        Session::set('filter_min_age', $filters['min_age']);
        Session::set('filter_max_age', $filters['max_age']);
        Session::set('filter_max_distance', $filters['max_distance']);

        $stack = Discovery::getStack($user['id'], $filters, 20);
        $swipesToday = Interaction::getTodaySwipeCount($user['id']);
        $userProfile = Profile::getByUserId($user['id']);
        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);
        $dailyLimit = Config::get('app.free_daily_swipes', 50);

        View::render('discover/index', [
            'stack'       => $stack,
            'filters'     => $filters,
            'swipesToday' => $swipesToday,
            'dailyLimit'  => $dailyLimit,
            'isPremium'   => $isPremium,
            'hasProfile'  => !empty($userProfile['name']),
        ]);
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
        $targetId = (int)($input['target_id'] ?? 0);
        $action   = $input['action'] ?? '';

        if (!in_array($action, ['like', 'dislike', 'superlike'])) {
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

        $response = ['result' => $result];
        if ($result === 'match') {
            $matchProfile = Profile::getFullProfile($targetId);
            $response['match'] = [
                'name'  => $matchProfile['name'] ?? 'Someone',
                'photo' => $matchProfile['primary_photo'] ?? null,
            ];
        }

        echo json_encode($response);
    }
}
