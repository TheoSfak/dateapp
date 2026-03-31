<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\MatchModel;
use App\Models\MiniGame;
use App\Models\Profile;

class GameController extends Controller
{
    /**
     * Verify user is part of a match and return match + other user info.
     */
    private function verifyMatch(int $matchId): ?array
    {
        $userId = Session::get('user_id');
        $match = MatchModel::findById($matchId);
        if (!$match || ($match['user_1_id'] !== $userId && $match['user_2_id'] !== $userId)) {
            return null;
        }
        $otherId = $match['user_1_id'] === $userId ? $match['user_2_id'] : $match['user_1_id'];
        return ['match' => $match, 'otherId' => $otherId, 'userId' => $userId];
    }

    /**
     * Show game picker / active game for a match.
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $matchId = (int)($_GET['match_id'] ?? 0);
        $ctx = $this->verifyMatch($matchId);
        if (!$ctx) { $this->redirect('/matches'); return; }

        $activeGame = MiniGame::getActiveForMatch($matchId);
        $otherProfile = Profile::getFullProfile($ctx['otherId']);

        if ($activeGame) {
            $this->redirect("/game/play?game_id={$activeGame['id']}");
            return;
        }

        View::render('game/picker', [
            'match'     => $ctx['match'],
            'otherUser' => $otherProfile,
        ]);
    }

    /**
     * Start a new game (POST, AJAX).
     */
    public function start(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }

        $matchId = (int)($input['match_id'] ?? 0);
        $type = $input['game_type'] ?? '';

        if (!in_array($type, ['would_you_rather', 'trivia', 'this_or_that'], true)) {
            echo json_encode(['error' => 'Invalid game type']);
            return;
        }

        $ctx = $this->verifyMatch($matchId);
        if (!$ctx) { echo json_encode(['error' => 'Unauthorized']); return; }

        // Check no active game already
        $active = MiniGame::getActiveForMatch($matchId);
        if ($active) {
            echo json_encode(['error' => 'A game is already in progress', 'game_id' => $active['id']]);
            return;
        }

        $gameId = MiniGame::create($matchId, $ctx['userId'], $type, 5);

        echo json_encode(['success' => true, 'game_id' => $gameId]);
    }

    /**
     * Play / view an active game.
     */
    public function play(): void
    {
        $user = $this->requireAuth();
        $gameId = (int)($_GET['game_id'] ?? 0);

        $game = MiniGame::findById($gameId);
        if (!$game) { $this->redirect('/matches'); return; }

        $ctx = $this->verifyMatch((int)$game['match_id']);
        if (!$ctx) { $this->redirect('/matches'); return; }

        $otherProfile = Profile::getFullProfile($ctx['otherId']);
        $questions = MiniGame::getQuestions($game['game_type'], $gameId, (int)$game['total_rounds']);
        $answers = MiniGame::getAnswers($gameId);
        $hasAnswered = MiniGame::hasAnswered($gameId, $ctx['userId'], (int)$game['current_round']);

        // Build answers by round
        $answersByRound = [];
        foreach ($answers as $a) {
            $answersByRound[(int)$a['round_num']][(int)$a['user_id']] = $a['answer'];
        }

        View::render('game/play', [
            'game'           => $game,
            'otherUser'      => $otherProfile,
            'questions'      => $questions,
            'answers'        => $answersByRound,
            'hasAnswered'    => $hasAnswered,
            'userId'         => $ctx['userId'],
            'matchId'        => (int)$game['match_id'],
        ]);
    }

    /**
     * Submit answer (AJAX).
     */
    public function answer(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { echo json_encode(['error' => 'Invalid request']); return; }

        $gameId  = (int)($input['game_id'] ?? 0);
        $round   = (int)($input['round'] ?? 0);
        $answer  = trim($input['answer'] ?? '');

        if ($gameId <= 0 || $round <= 0 || $answer === '') {
            echo json_encode(['error' => 'Invalid data']);
            return;
        }

        $game = MiniGame::findById($gameId);
        if (!$game || $game['status'] === 'finished') {
            echo json_encode(['error' => 'Game not found or finished']);
            return;
        }

        $ctx = $this->verifyMatch((int)$game['match_id']);
        if (!$ctx) { echo json_encode(['error' => 'Unauthorized']); return; }

        $result = MiniGame::submitAnswer($gameId, $ctx['userId'], $round, $answer);

        // If both answered, include both answers for reveal
        if ($result['status'] === 'next_round' || $result['status'] === 'finished') {
            $roundAnswers = MiniGame::getRoundAnswers($gameId, $round);
            $reveal = [];
            foreach ($roundAnswers as $ra) {
                $reveal[(int)$ra['user_id']] = $ra['answer'];
            }
            $result['reveal'] = $reveal;
        }

        echo json_encode($result);
    }

    /**
     * Poll game state (AJAX).
     */
    public function poll(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $gameId = (int)($_GET['game_id'] ?? 0);
        $round  = (int)($_GET['round'] ?? 0);

        $game = MiniGame::findById($gameId);
        if (!$game) { echo json_encode(['error' => 'Not found']); return; }

        $ctx = $this->verifyMatch((int)$game['match_id']);
        if (!$ctx) { echo json_encode(['error' => 'Unauthorized']); return; }

        // Check if partner answered
        $roundAnswers = MiniGame::getRoundAnswers($gameId, $round);
        $bothAnswered = count($roundAnswers) >= 2;

        $response = [
            'game_status'  => $game['status'],
            'current_round' => (int)$game['current_round'],
            'both_answered' => $bothAnswered,
        ];

        if ($bothAnswered) {
            $reveal = [];
            foreach ($roundAnswers as $ra) {
                $reveal[(int)$ra['user_id']] = $ra['answer'];
            }
            $response['reveal'] = $reveal;

            // Reload game for updated state
            $game = MiniGame::findById($gameId);
            $response['game_status'] = $game['status'];
            $response['current_round'] = (int)$game['current_round'];
        }

        echo json_encode($response);
    }
}
