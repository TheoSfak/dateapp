<section class="settings-page">
    <h2>Settings</h2>

    <div class="settings-section">
        <h3>Account</h3>
        <div class="settings-card">
            <div class="setting-row">
                <span>Email</span>
                <span class="setting-value"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="setting-row">
                <span>Membership</span>
                <span class="setting-value badge badge-<?= $user['is_premium'] ? 'premium' : 'free' ?>">
                    <?= $user['is_premium'] ? '⭐ Premium' : 'Free' ?>
                </span>
            </div>
            <?php if (!$user['is_premium']): ?>
                <a href="/dateapp/premium" class="btn btn-accent btn-block">Upgrade to Premium</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="settings-section">
        <h3>Blocked Users</h3>
        <div class="settings-card">
            <?php if (empty($blocked)): ?>
                <p class="text-muted">No blocked users.</p>
            <?php else: ?>
                <div class="blocked-list">
                    <?php foreach ($blocked as $b): ?>
                    <div class="blocked-item">
                        <div class="blocked-info">
                            <div class="blocked-avatar">
                                <?php if (!empty($b['photo'])): ?>
                                    <img src="/dateapp/public/<?= htmlspecialchars($b['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                                <?php else: ?>
                                    <div class="avatar-placeholder-sm"><?= strtoupper(substr($b['name'] ?? '?', 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <span><?= htmlspecialchars($b['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <form method="POST" action="/dateapp/settings/unblock">
                            <?= \App\Core\CSRF::field() ?>
                            <input type="hidden" name="blocked_id" value="<?= (int)$b['user_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline">Unblock</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="settings-section">
        <h3>Danger Zone</h3>
        <div class="settings-card">
            <a href="/dateapp/logout" class="btn btn-outline btn-block">Log Out</a>
        </div>
    </div>
</section>
