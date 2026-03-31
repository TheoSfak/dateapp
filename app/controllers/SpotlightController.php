<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\SpotlightPrompt;
use App\Models\Profile;

class SpotlightController extends Controller
{
    /**
     * Show the spotlight prompts management page.
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $myAnswers = SpotlightPrompt::getUserAnswers($user['id']);
        $unanswered = SpotlightPrompt::getUnanswered($user['id'], 6);

        View::render('spotlight/index', [
            'myAnswers'  => $myAnswers,
            'unanswered' => $unanswered,
        ]);
    }

    /**
     * Save an answer (AJAX).
     */
    public function answer(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            echo json_encode(['error' => 'Invalid request']);
            return;
        }

        $promptId = (int)($input['prompt_id'] ?? 0);
        $answer   = trim($input['answer'] ?? '');

        if ($promptId <= 0 || $answer === '' || mb_strlen($answer) > 500) {
            echo json_encode(['error' => 'Answer is required (max 500 characters)']);
            return;
        }

        // Verify prompt exists
        $prompt = SpotlightPrompt::findById($promptId);
        if (!$prompt || !$prompt['is_active']) {
            echo json_encode(['error' => 'Invalid prompt']);
            return;
        }

        // Max 5 answers per user
        $existing = SpotlightPrompt::getUserAnswers($user['id']);
        $isUpdate = false;
        foreach ($existing as $e) {
            if ((int)$e['prompt_id'] === $promptId) { $isUpdate = true; break; }
        }
        if (!$isUpdate && count($existing) >= 5) {
            echo json_encode(['error' => 'Maximum 5 prompt answers — remove one first']);
            return;
        }

        SpotlightPrompt::saveAnswer($user['id'], $promptId, $answer);

        echo json_encode([
            'success' => true,
            'prompt'  => $prompt['prompt'],
            'emoji'   => $prompt['emoji'],
            'answer'  => $answer,
        ]);
    }

    /**
     * Delete an answer (AJAX).
     */
    public function deleteAnswer(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        if (!$this->validateCSRFAjax()) return;

        $input = json_decode(file_get_contents('php://input'), true);
        $promptId = (int)($input['prompt_id'] ?? 0);

        if ($promptId <= 0) {
            echo json_encode(['error' => 'Invalid prompt']);
            return;
        }

        SpotlightPrompt::deleteAnswer($user['id'], $promptId);
        echo json_encode(['success' => true]);
    }

    /**
     * Get more unanswered prompts (AJAX).
     */
    public function more(): void
    {
        $user = $this->requireAuth();
        header('Content-Type: application/json');

        $unanswered = SpotlightPrompt::getUnanswered($user['id'], 3);
        echo json_encode(['prompts' => $unanswered]);
    }
}
