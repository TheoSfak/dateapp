<section class="liked-me-page">
    <h2>💕 Who Liked You</h2>

    <?php if (empty($likers)): ?>
        <div class="empty-state">
            <div class="empty-icon">💔</div>
            <h3>No likes yet</h3>
            <p>Keep discovering and your likes will show up here!</p>
            <a href="/dateapp/discover" class="btn btn-primary">Discover People</a>
        </div>
    <?php else: ?>
        <div class="likers-grid">
            <?php foreach ($likers as $liker): ?>
            <div class="liker-card">
                <?php if (!empty($liker['photo'])): ?>
                    <img src="/dateapp/public/<?= htmlspecialchars($liker['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="liker-photo">
                <?php else: ?>
                    <div class="liker-photo liker-photo-placeholder"><?= strtoupper(substr($liker['name'] ?? '?', 0, 1)) ?></div>
                <?php endif; ?>
                <div class="liker-info">
                    <h4><?= htmlspecialchars($liker['name'], ENT_QUOTES, 'UTF-8') ?><?php if ($liker['age']): ?>, <?= (int)$liker['age'] ?><?php endif; ?></h4>
                    <?php if (!empty($liker['city'])): ?>
                        <p>📍 <?= htmlspecialchars($liker['city'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if ($liker['action_type'] === 'superlike'): ?>
                        <span class="super-badge">⭐ Super Like</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
