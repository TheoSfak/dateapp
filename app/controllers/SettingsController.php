<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;
use App\Models\Block;
use App\Models\Report;
use App\Models\Interaction;
use App\Models\Availability;

class SettingsController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $dbUser = \App\Models\User::findById($user['id']);
        $blocked = Block::getBlockedByUser($user['id']);
        $availSlots = Availability::getByUserId($user['id']);

        View::render('settings/index', [
            'user'        => $dbUser,
            'blocked'     => $blocked,
            'availSlots'  => $availSlots,
        ]);
    }

    public function blockUser(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }
        $targetId = (int)($input['blocked_id'] ?? $input['user_id'] ?? 0);
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
        $targetId = (int)($_POST['blocked_id'] ?? $_POST['user_id'] ?? 0);
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

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }
        $targetId = (int)($input['reported_id'] ?? $input['user_id'] ?? 0);
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
        $isPremium = (bool)($dbUser['is_premium'] ?? false);

        $likers = Interaction::getLikers($user['id']);
        View::render('discover/liked_me', [
            'likers'    => $likers,
            'isPremium' => $isPremium,
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new === '' || strlen($new) < 8 || strlen($new) > 128) {
            Session::flash('error', 'New password must be 8–128 characters.');
            $this->redirect('/settings');
            return;
        }

        if ($new !== $confirm) {
            Session::flash('error', 'New passwords do not match.');
            $this->redirect('/settings');
            return;
        }

        $dbUser = \App\Models\User::findById($user['id']);
        if (!password_verify($current, $dbUser['password_hash'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirect('/settings');
            return;
        }

        $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        \App\Core\Database::getInstance()->query(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$newHash, $user['id']]
        );

        Session::flash('success', 'Password changed successfully.');
        $this->redirect('/settings');
    }

    /**
     * Delete account.
     */
    public function deleteAccount(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        $password = $_POST['confirm_delete_password'] ?? '';
        $dbUser = \App\Models\User::findById($user['id']);

        if (!password_verify($password, $dbUser['password_hash'])) {
            Session::flash('error', 'Incorrect password. Account not deleted.');
            $this->redirect('/settings');
            return;
        }

        // Delete user (cascades to profiles, photos, interactions, matches, messages, reports, blocks)
        \App\Core\Database::getInstance()->query("DELETE FROM users WHERE id = ?", [$user['id']]);

        Session::destroy();
        session_start();
        Session::flash('success', 'Your account has been deleted.');
        $this->redirect('/');
    }

    /**
     * Save availability calendar slots (AJAX).
     */
    public function saveAvailability(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input) || !isset($input['slots'])) {
            echo json_encode(['error' => 'Invalid request']);
            return;
        }

        $slots = $input['slots'];
        if (!is_array($slots) || count($slots) > 21) {
            echo json_encode(['error' => 'Too many slots (max 21)']);
            return;
        }

        Availability::saveSlots($user['id'], $slots);
        echo json_encode(['success' => true]);
    }
}
