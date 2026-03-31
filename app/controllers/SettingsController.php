<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;
use App\Models\Block;
use App\Models\Report;
use App\Models\Interaction;

class SettingsController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $dbUser = \App\Models\User::findById($user['id']);
        $blocked = Block::getBlockedByUser($user['id']);

        View::render('settings/index', [
            'user'    => $dbUser,
            'blocked' => $blocked,
        ]);
    }

    public function blockUser(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals(Session::get('_csrf_token', ''), $token)) {
            echo json_encode(['error' => 'Invalid CSRF']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $targetId = (int)($input['user_id'] ?? 0);
        if ($targetId > 0 && $targetId !== $user['id']) {
            Block::block($user['id'], $targetId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid user']);
        }
    }

    public function unblockUser(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();
        $targetId = (int)($_POST['user_id'] ?? 0);
        if ($targetId > 0) {
            Block::unblock($user['id'], $targetId);
            Session::flash('success', 'User unblocked.');
        }
        $this->redirect('/settings');
    }

    public function reportUser(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals(Session::get('_csrf_token', ''), $token)) {
            echo json_encode(['error' => 'Invalid CSRF']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $targetId = (int)($input['user_id'] ?? 0);
        $reason   = trim($input['reason'] ?? '');

        if ($targetId > 0 && $reason !== '' && $targetId !== $user['id']) {
            Report::create($user['id'], $targetId, $reason);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid report']);
        }
    }

    /**
     * "Who liked me" – premium feature.
     */
    public function likedMe(): void
    {
        $user = $this->requireAuth();
        $dbUser = \App\Models\User::findById($user['id']);

        if (!$dbUser['is_premium']) {
            View::render('premium/upsell', ['feature' => 'See Who Liked You']);
            return;
        }

        $likers = Interaction::getLikers($user['id']);
        View::render('discover/liked_me', ['likers' => $likers]);
    }
}
