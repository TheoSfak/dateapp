<?php
    $otherName = htmlspecialchars($otherUser['name'] ?? 'your match', ENT_QUOTES, 'UTF-8');
?>

<section class="game-page">
    <div class="game-back">
        <a href="/dateapp/chat?match_id=<?= (int)$match['id'] ?>" class="btn btn-sm btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            Back to Chat
        </a>
    </div>

    <div class="game-picker">
        <div class="game-picker-header">
            <div class="game-picker-icon">🎮</div>
            <h1>Play a Game</h1>
            <p>Break the ice with <?= $otherName ?>! Pick a game below.</p>
        </div>

        <div class="game-picker-grid" data-match-id="<?= (int)$match['id'] ?>">
            <!-- Would You Rather -->
            <button class="game-type-card" data-type="would_you_rather">
                <div class="game-type-emoji">🤔</div>
                <h3>Would You Rather</h3>
                <p>Choose between two scenarios &mdash; see if you think alike!</p>
                <span class="game-type-meta">5 rounds &middot; ~2 min</span>
            </button>

            <!-- This or That -->
            <button class="game-type-card" data-type="this_or_that">
                <div class="game-type-emoji">⚡</div>
                <h3>This or That</h3>
                <p>Quick-fire preferences &mdash; discover what you have in common!</p>
                <span class="game-type-meta">5 rounds &middot; ~1 min</span>
            </button>

            <!-- Rapid Trivia -->
            <button class="game-type-card" data-type="trivia">
                <div class="game-type-emoji">🧠</div>
                <h3>Rapid Trivia</h3>
                <p>Test your knowledge together &mdash; who's the brainiac?</p>
                <span class="game-type-meta">5 rounds &middot; ~2 min</span>
            </button>
        </div>
    </div>
</section>
