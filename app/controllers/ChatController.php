<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\Profile;

class ChatController extends Controller
{
    /**
     * Show matches list (inbox).
     */
    public function matches(): void
    {
        $user = $this->requireAuth();
        $matches = MatchModel::getByUserId($user['id']);
        $unread  = Message::totalUnread($user['id']);

        View::render('chat/matches', [
            'matches' => $matches,
            'unread'  => $unread,
        ]);
    }

    /**
     * Show conversation with a match.
     */
    public function conversation(): void
    {
        $user = $this->requireAuth();
        $matchId = (int)($_GET['match_id'] ?? 0);
        if ($matchId <= 0) $this->redirect('/matches');

        // Verify user is part of this match
        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $user['id'] && $match['user_2_id'] !== $user['id'])) {
            $this->redirect('/matches');
        }

        $otherId = $match['user_1_id'] === $user['id'] ? $match['user_2_id'] : $match['user_1_id'];
        $otherProfile = Profile::getFullProfile($otherId);
        $messages = Message::getByMatchId($matchId);

        // Mark messages as read
        Message::markRead($matchId, $user['id']);

        View::render('chat/conversation', [
            'match'     => $match,
            'otherUser' => $otherProfile,
            'messages'  => $messages,
            'userId'    => $user['id'],
        ]);
    }

    /**
     * Send message (AJAX).
     */
    public function send(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
        $stored = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $token)) {
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }
        $matchId = (int)($input['match_id'] ?? 0);
        $text    = trim($input['body'] ?? '');

        if ($matchId <= 0 || $text === '') {
            echo json_encode(['error' => 'Invalid message']);
            return;
        }

        // Verify user is part of match
        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $user['id'] && $match['user_2_id'] !== $user['id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $msgId = Message::send($matchId, $user['id'], $text);

        echo json_encode([
            'success' => true,
            'message' => [
                'id'         => $msgId,
                'sender_id'  => $user['id'],
                'body'       => $text,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Poll for new messages (AJAX long-polling lite).
     */
    public function poll(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $matchId = (int)($_GET['match_id'] ?? 0);
        $afterId = (int)($_GET['last_id'] ?? 0);

        if ($matchId <= 0) {
            echo json_encode(['messages' => []]);
            return;
        }

        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $user['id'] && $match['user_2_id'] !== $user['id'])) {
            echo json_encode(['messages' => []]);
            return;
        }

        Message::markRead($matchId, $user['id']);
        $newMessages = Message::getNewMessages($matchId, $afterId);

        echo json_encode(['messages' => $newMessages]);
    }

    /**
     * Unmatch a user.
     */
    public function unmatch(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
        $stored = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $token)) {
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }
        $matchId = (int)($input['match_id'] ?? 0);

        MatchModel::unmatch($matchId, $user['id']);
        echo json_encode(['success' => true]);
    }
}
