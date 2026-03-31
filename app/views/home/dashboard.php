<?php if (empty($hasProfile)): ?>
<section class="onboarding-banner">
    <div class="onboarding-content">
        <h2>👋 Complete Your Profile</h2>
        <p>Add your name, photos, and interests to start getting matches!</p>
        <a href="/dateapp/profile/edit" class="btn btn-primary">Complete Profile</a>
    </div>
</section>
<?php endif; ?>

<section class="dashboard">
    <div class="dashboard-welcome">
        <div class="welcome-text">
            <?php if (!empty($profile['primary_photo'])): ?>
                <img src="/dateapp/public/<?= htmlspecialchars($profile['primary_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Profile" class="welcome-avatar">
            <?php else: ?>
                <div class="welcome-avatar welcome-avatar-placeholder">
                    <?= strtoupper(substr($profile['name'] ?? $email ?? '?', 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div>
                <h2>Welcome back<?= !empty($profile['name']) ? ', ' . htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') : '' ?>!</h2>
                <p class="text-muted">Here's what's happening today.</p>
            </div>
        </div>
    </div>

    <div class="dashboard-cards">
        <a href="/dateapp/matches" class="dash-card dash-card-link">
            <div class="dash-card-icon">💕</div>
            <h3><?= $matchCount ?? 0 ?></h3>
            <p>Matches</p>
        </a>
        <a href="/dateapp/matches" class="dash-card dash-card-link">
            <div class="dash-card-icon">💬</div>
            <h3><?= $unread ?? 0 ?></h3>
            <p>Unread Messages</p>
        </a>
        <a href="/dateapp/discover" class="dash-card dash-card-link">
            <div class="dash-card-icon">🎯</div>
            <h3><?= ($dailyLimit ?? 50) - ($swipesToday ?? 0) ?></h3>
            <p>Swipes Left Today</p>
        </a>
    </div>

    <div class="dashboard-actions">
        <a href="/dateapp/discover" class="btn btn-primary btn-lg">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
            Start Discovering
        </a>
        <a href="/dateapp/liked-me" class="btn btn-outline btn-lg">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Who Liked Me
        </a>
    </div>
</section>
