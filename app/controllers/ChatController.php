<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Availability;
use App\Models\DateIdea;
use App\Models\SpotlightPrompt;

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

        // Anti-ghosting info
        $ghostInfo = Message::getGhostInfo($matchId, $user['id']);

        // Availability overlap
        $availOverlap = Availability::getOverlap($user['id'], $otherId);

        // Date ideas
        $dateIdeas = DateIdea::generate($user['id'], $otherId);

        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);

        // Generate icebreakers
        $icebreakers = self::generateIcebreakers($user['id'], $otherId, $otherProfile);

        View::render('chat/conversation', [
            'match'         => $match,
            'otherUser'     => $otherProfile,
            'messages'      => $messages,
            'userId'        => $user['id'],
            'ghostInfo'     => $ghostInfo,
            'availOverlap'  => $availOverlap,
            'dateIdeas'     => $dateIdeas,
            'isPremium'     => $isPremium,
            'icebreakers'   => $icebreakers,
        ]);
    }

    /**
     * Send message (AJAX).
     */
    public function send(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

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

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }
        $matchId = (int)($input['match_id'] ?? 0);

        MatchModel::unmatch($matchId, $user['id']);
        echo json_encode(['success' => true]);
    }

    /**
     * Send a polite pass (kind closure message, then unmatch).
     */
    public function politePass(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }

        $matchId = (int)($input['match_id'] ?? 0);
        $msgIndex = (int)($input['message_index'] ?? 0);

        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $user['id'] && $match['user_2_id'] !== $user['id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $politeMessages = [
            "I've really enjoyed chatting with you, but I don't feel a romantic connection. Wishing you all the best! 💛",
            "Thanks for the great conversations! I think we're better as friends though. Best of luck out there! 🌟",
            "I appreciate getting to know you, but I don't think we're the right match. I hope you find someone amazing! ✨",
        ];

        $msg = $politeMessages[min($msgIndex, count($politeMessages) - 1)];
        Message::send($matchId, $user['id'], $msg);

        echo json_encode(['success' => true, 'message' => $msg]);
    }

    /**
     * Send a voice note (AJAX, multipart form).
     */
    public function sendVoice(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        // CSRF from header (can't use JSON body for FormData)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
        if (empty($token) || !\App\Core\CSRF::validate($token)) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $matchId  = (int)($_POST['match_id'] ?? 0);
        $duration = max(1, min(120, (int)($_POST['duration'] ?? 0)));

        if ($matchId <= 0 || empty($_FILES['voice']) || $_FILES['voice']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'Invalid voice data']);
            return;
        }

        // Verify user is in match
        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $user['id'] && $match['user_2_id'] !== $user['id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Validate file (max 2MB, audio MIME)
        $file = $_FILES['voice'];
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['error' => 'Voice note too large (max 2MB)']);
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = ['audio/webm', 'audio/ogg', 'audio/mp4', 'audio/mpeg', 'audio/wav', 'video/webm'];
        if (!in_array($mime, $allowedMimes, true)) {
            echo json_encode(['error' => 'Invalid audio format']);
            return;
        }

        // Save file
        $ext = 'webm';
        if (str_contains($mime, 'ogg')) $ext = 'ogg';
        elseif (str_contains($mime, 'mp4')) $ext = 'm4a';
        elseif (str_contains($mime, 'wav')) $ext = 'wav';

        $filename = 'voice_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir = __DIR__ . '/../../public/uploads/voice';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $dest = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['error' => 'Upload failed']);
            return;
        }

        $voicePath = 'uploads/voice/' . $filename;
        $msgId = Message::sendVoice($matchId, $user['id'], $voicePath, $duration);

        echo json_encode([
            'success' => true,
            'message' => [
                'id'             => $msgId,
                'sender_id'      => $user['id'],
                'message_type'   => 'voice',
                'voice_path'     => $voicePath,
                'voice_duration' => $duration,
                'body'           => '',
                'created_at'     => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Generate smart icebreakers (AJAX).
     */
    public function icebreakers(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $matchId = (int)($_GET['match_id'] ?? 0);
        if ($matchId <= 0) {
            echo json_encode(['icebreakers' => []]);
            return;
        }

        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $user['id'] && $match['user_2_id'] !== $user['id'])) {
            echo json_encode(['icebreakers' => []]);
            return;
        }

        $otherId = $match['user_1_id'] === $user['id'] ? $match['user_2_id'] : $match['user_1_id'];
        $otherProfile = Profile::getFullProfile($otherId);

        $icebreakers = self::generateIcebreakers($user['id'], $otherId, $otherProfile);
        echo json_encode(['icebreakers' => $icebreakers]);
    }

    /**
     * Build personalized icebreaker suggestions.
     */
    private static function generateIcebreakers(int $userId, int $otherId, array $other): array
    {
        $icebreakers = [];
        $otherName = $other['name'] ?? 'them';

        // 1. Based on shared interests
        $stmt = \App\Core\Database::getInstance()->query(
            "SELECT i.name, i.emoji FROM user_interests ui1
             JOIN user_interests ui2 ON ui1.interest_id = ui2.interest_id AND ui2.user_id = ?
             JOIN interests i ON i.id = ui1.interest_id
             WHERE ui1.user_id = ? LIMIT 5",
            [$otherId, $userId]
        );
        $shared = $stmt->fetchAll();
        if (!empty($shared)) {
            $pick = $shared[array_rand($shared)];
            $icebreakers[] = [
                'emoji' => $pick['emoji'] ?: '🎯',
                'text'  => "I see we both love {$pick['name']}! What got you into it?",
                'type'  => 'shared_interest',
            ];
        }

        // 2. Based on their bio
        if (!empty($other['bio'])) {
            $bio = $other['bio'];
            if (mb_strlen($bio) > 30) {
                $icebreakers[] = [
                    'emoji' => '📖',
                    'text'  => "Your bio caught my eye — tell me more about the " . mb_strimwidth($bio, 0, 40, '...') . " part!",
                    'type'  => 'bio',
                ];
            }
        }

        // 3. Based on their spotlight prompt answers
        $promptAnswers = SpotlightPrompt::getForDiscoverCard($otherId, 2);
        foreach ($promptAnswers as $pa) {
            $icebreakers[] = [
                'emoji' => $pa['emoji'] ?: '✨',
                'text'  => "Love your answer to \"{$pa['prompt']}\" — " . mb_strimwidth($pa['answer'], 0, 50, '...') . " Tell me more!",
                'type'  => 'prompt',
            ];
        }

        // 4. Based on their city
        if (!empty($other['city'])) {
            $icebreakers[] = [
                'emoji' => '📍',
                'text'  => "Hey! What's your favorite spot in {$other['city']}?",
                'type'  => 'location',
            ];
        }

        // 5. Relationship goal based
        $goalLabels = [
            'long-term'  => "What does your ideal relationship look like?",
            'short-term' => "So what's the most fun date you've been on?",
            'friendship' => "Always cool to make new friends! What do you do for fun?",
            'casual'     => "What's the most spontaneous thing you've done lately?",
        ];
        $goal = $other['relationship_goal'] ?? '';
        if (isset($goalLabels[$goal])) {
            $icebreakers[] = [
                'emoji' => '💬',
                'text'  => $goalLabels[$goal],
                'type'  => 'goal',
            ];
        }

        // 6. Universal fallbacks
        $fallbacks = [
            ['emoji' => '🌟', 'text' => "If you could have dinner with anyone, living or dead, who would it be?"],
            ['emoji' => '🎬', 'text' => "What's the last show you binge-watched?"],
            ['emoji' => '✈️', 'text' => "If you could wake up anywhere in the world tomorrow, where?"],
            ['emoji' => '🍕', 'text' => "Hot take: pineapple on pizza — yes or no?"],
        ];
        shuffle($fallbacks);
        foreach ($fallbacks as $fb) {
            $fb['type'] = 'fallback';
            $icebreakers[] = $fb;
        }

        // Deduplicate by type
        $seen = [];
        $unique = [];
        foreach ($icebreakers as $ib) {
            if (!isset($seen[$ib['type']])) {
                $seen[$ib['type']] = true;
                $unique[] = $ib;
            }
        }

        return array_slice($unique, 0, 5);
    }
}
