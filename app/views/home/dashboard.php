<?php
    $hour = (int)date('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $swipesLeft = max(0, ($dailyLimit ?? 50) - ($swipesToday ?? 0));
?>

<?php if (empty($hasProfile)): ?>
<section class="dash-onboarding">
    <div class="dash-onboarding-inner">
        <div class="dash-onboarding-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
        </div>
        <h2>Complete Your Profile</h2>
        <p>Add your name, photos, and interests so people can discover you.</p>
        <a href="/dateapp/profile/edit" class="btn btn-primary btn-lg">Get Started</a>
    </div>
</section>
<?php endif; ?>

<section class="dash">
    <!-- ── Hero ──────────────────────────────────────── -->
    <div class="dash-hero">
        <div class="dash-hero-bg"></div>
        <div class="dash-hero-content">
            <div class="dash-avatar-ring">
                <?php if (!empty($profile['primary_photo'])): ?>
                    <img src="/dateapp/public/<?= htmlspecialchars($profile['primary_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="You">
                <?php else: ?>
                    <span class="dash-avatar-letter"><?= strtoupper(substr($firstName ?: ($email ?? '?'), 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <div class="dash-hero-text">
                <h1>
                    <?= $greeting ?><?= $firstName ? ", {$firstName}" : '' ?>
                    <?php if (!empty($profile['is_verified'])): ?><span class="verified-badge" title="Verified">✓</span><?php endif; ?>
                </h1>
                <p>Here's what's happening on your dating journey</p>
            </div>
        </div>
    </div>

    <!-- ── Stats Strip ───────────────────────────────── -->
    <div class="dash-stats">
        <a href="/dateapp/matches" class="dash-stat">
            <span class="dash-stat-value"><?= $matchCount ?? 0 ?></span>
            <span class="dash-stat-label">Matches</span>
        </a>
        <div class="dash-stat-divider"></div>
        <a href="/dateapp/matches" class="dash-stat">
            <span class="dash-stat-value"><?= $unread ?? 0 ?></span>
            <span class="dash-stat-label">Unread</span>
        </a>
        <div class="dash-stat-divider"></div>
        <a href="/dateapp/discover" class="dash-stat">
            <span class="dash-stat-value"><?= $swipesLeft ?></span>
            <span class="dash-stat-label">Swipes Left</span>
        </a>
    </div>

    <!-- ── Discover CTA ──────────────────────────────── -->
    <a href="/dateapp/discover" class="dash-cta">
        <div class="dash-cta-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
        </div>
        <div class="dash-cta-text">
            <strong>Start Discovering</strong>
            <span><?= $swipesLeft ?> swipes remaining today</span>
        </div>
        <svg class="dash-cta-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </a>

    <!-- ── My Photos Strip ───────────────────────────── -->
    <div class="dash-section">
        <div class="dash-section-header">
            <h2>My Photos</h2>
            <a href="/dateapp/profile/photos" class="dash-see-all">Manage</a>
        </div>
        <?php if (!empty($photos)): ?>
        <div class="dash-photos-strip">
            <?php foreach ($photos as $p): ?>
            <a href="/dateapp/profile/photos" class="dash-photo-thumb <?= $p['is_primary'] ? 'dash-photo-thumb--primary' : '' ?>">
                <img src="/dateapp/public/<?= htmlspecialchars($p['file_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Photo">
                <?php if ($p['is_primary']): ?>
                    <span class="dash-photo-star">★</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php if (count($photos) < 6): ?>
            <a href="/dateapp/profile/photos" class="dash-photo-add">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <a href="/dateapp/profile/photos" class="dash-photos-empty">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span>Upload your first photo</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- ── Recent Matches ────────────────────────────── -->
    <?php if (!empty($recentMatches)): ?>
    <div class="dash-section">
        <div class="dash-section-header">
            <h2>Your Matches</h2>
            <a href="/dateapp/matches" class="dash-see-all">See all</a>
        </div>
        <div class="dash-matches-scroll">
            <?php foreach ($recentMatches as $match): ?>
            <a href="/dateapp/chat?match_id=<?= (int)$match['match_id'] ?>" class="dash-match-card">
                <div class="dash-match-photo">
                    <?php if (!empty($match['photo'])): ?>
                        <img src="/dateapp/public/<?= htmlspecialchars($match['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($match['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php else: ?>
                        <span><?= strtoupper(substr($match['name'], 0, 1)) ?></span>
                    <?php endif; ?>
                    <?php if ($match['unread_count'] > 0): ?>
                        <span class="dash-match-badge"><?= (int)$match['unread_count'] ?></span>
                    <?php endif; ?>
                </div>
                <span class="dash-match-name"><?= htmlspecialchars($match['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Profile Completeness ──────────────────────── -->
    <?php if (($completeness ?? 0) < 100): ?>
    <div class="dash-section">
        <div class="dash-completeness">
            <div class="dash-completeness-ring" style="--pct: <?= $completeness ?>">
                <svg viewBox="0 0 36 36">
                    <path class="dash-ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="dash-ring-fill" stroke-dasharray="<?= $completeness ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <span class="dash-ring-text"><?= $completeness ?>%</span>
            </div>
            <div class="dash-completeness-info">
                <h3>Profile Completeness</h3>
                <p>Complete profiles get up to 3x more matches.</p>
                <a href="/dateapp/profile/edit" class="btn btn-sm btn-outline">Improve Profile</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Quick Actions ─────────────────────────────── -->
    <div class="dash-actions-grid">
        <a href="/dateapp/liked-me" class="dash-action-card">
            <div class="dash-action-icon dash-action-icon--pink">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
            <span>Who Liked Me</span>
        </a>
        <a href="/dateapp/profile" class="dash-action-card">
            <div class="dash-action-icon dash-action-icon--blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <span>My Profile</span>
        </a>
        <a href="/dateapp/profile/photos" class="dash-action-card">
            <div class="dash-action-icon dash-action-icon--purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <span>My Photos</span>
        </a>
        <a href="/dateapp/settings" class="dash-action-card">
            <div class="dash-action-icon dash-action-icon--gray">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </div>
            <span>Settings</span>
        </a>
        <?php if (empty($profile['is_verified'])): ?>
        <a href="/dateapp/verify-identity" class="dash-action-card">
            <div class="dash-action-icon dash-action-icon--green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <span>Get Verified</span>
        </a>
        <?php endif; ?>
        <a href="/dateapp/settings#availCalendar" class="dash-action-card">
            <div class="dash-action-icon dash-action-icon--orange">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <span>Availability</span>
        </a>
        <?php if (!empty($isPremium)): ?>
        <button class="dash-action-card dash-boost-action" id="dashBoostBtn">
            <div class="dash-action-icon dash-action-icon--rocket">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 3 0 3 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-3 0-3"/></svg>
            </div>
            <span>Boost Profile</span>
        </button>
        <?php endif; ?>
    </div>
</section>
