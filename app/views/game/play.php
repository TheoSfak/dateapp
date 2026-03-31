<?php
    $otherName = htmlspecialchars($otherUser['name'] ?? 'Partner', ENT_QUOTES, 'UTF-8');
    $type = $game['game_type'];
    $currentRound = (int)$game['current_round'];
    $totalRounds = (int)$game['total_rounds'];
    $isFinished = $game['status'] === 'finished';

    $typeLabel = match($type) {
        'would_you_rather' => 'Would You Rather',
        'this_or_that'     => 'This or That',
        'trivia'           => 'Rapid Trivia',
        default            => 'Mini Game',
    };
    $typeEmoji = match($type) {
        'would_you_rather' => '🤔',
        'this_or_that'     => '⚡',
        'trivia'           => '🧠',
        default            => '🎮',
    };

    // JSON-encode data for JS
    $questionsJson = json_encode($questions);
    $answersJson = json_encode($answers);
?>

<section class="game-page">
    <div class="game-play"
         data-game-id="<?= (int)$game['id'] ?>"
         data-match-id="<?= $matchId ?>"
         data-user-id="<?= $userId ?>"
         data-other-id="<?= (int)$otherUser['id'] ?>"
         data-type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
         data-current-round="<?= $currentRound ?>"
         data-total-rounds="<?= $totalRounds ?>"
         data-status="<?= htmlspecialchars($game['status'], ENT_QUOTES, 'UTF-8') ?>"
         data-has-answered="<?= $hasAnswered ? '1' : '0' ?>">

        <!-- Header -->
        <div class="game-play-header">
            <a href="/dateapp/chat?match_id=<?= $matchId ?>" class="game-play-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
            <div class="game-play-title">
                <span class="game-play-emoji"><?= $typeEmoji ?></span>
                <span><?= $typeLabel ?></span>
            </div>
            <div class="game-play-round">
                <span id="roundIndicator"><?= $isFinished ? $totalRounds : $currentRound ?></span> / <?= $totalRounds ?>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="game-progress">
            <div class="game-progress-bar" id="progressBar" style="width: <?= $isFinished ? 100 : (($currentRound - 1) / $totalRounds * 100) ?>%"></div>
        </div>

        <!-- Game area -->
        <div class="game-arena" id="gameArena">
            <?php if ($isFinished): ?>
                <!-- Results rendered by JS -->
            <?php endif; ?>
        </div>

        <!-- Waiting state -->
        <div class="game-waiting" id="gameWaiting" style="display:none">
            <div class="game-waiting-pulse"></div>
            <p>Waiting for <?= $otherName ?> to answer...</p>
        </div>

        <!-- Versus names -->
        <div class="game-players">
            <span class="game-player game-player--you">You</span>
            <span class="game-player-vs">VS</span>
            <span class="game-player game-player--them"><?= $otherName ?></span>
        </div>
    </div>
</section>

<script>
window._gameData = {
    questions: <?= $questionsJson ?>,
    answers: <?= $answersJson ?>,
    otherName: <?= json_encode($otherName) ?>
};
</script>
