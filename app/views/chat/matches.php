<section class="matches-page">
    <h2>💕 Your Matches</h2>

    <?php if (empty($matches)): ?>
        <div class="empty-state">
            <div class="empty-icon">💕</div>
            <h3>No matches yet</h3>
            <p>Start swiping to find your first match!</p>
            <a href="/dateapp/discover" class="btn btn-primary">Discover People</a>
        </div>
    <?php else: ?>
        <div class="matches-list">
            <?php foreach ($matches as $match): ?>
            <a href="/dateapp/chat?match_id=<?= (int)$match['match_id'] ?>" class="match-item <?= ($match['unread_count'] ?? 0) > 0 ? 'match-unread' : '' ?>">
                <div class="match-avatar">
                    <?php if (!empty($match['photo'])): ?>
                        <img src="/dateapp/public/<?= htmlspecialchars($match['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <?php else: ?>
                        <div class="match-avatar-placeholder"><?= strtoupper(substr($match['name'] ?? '?', 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="match-info">
                    <div class="match-name-row">
                        <h4><?= htmlspecialchars($match['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?><?php if ($match['age']): ?>, <?= (int)$match['age'] ?><?php endif; ?></h4>
                        <?php if (!empty($match['last_message_at'])): ?>
                            <span class="match-time"><?= date('M j', strtotime($match['last_message_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($match['last_message'])): ?>
                        <p class="match-preview"><?= htmlspecialchars(mb_strimwidth($match['last_message'], 0, 60, '...'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php else: ?>
                        <p class="match-preview match-new">New match! Say hello 👋</p>
                    <?php endif; ?>
                </div>
                <?php if (($match['unread_count'] ?? 0) > 0): ?>
                    <span class="match-unread-badge"><?= (int)$match['unread_count'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
