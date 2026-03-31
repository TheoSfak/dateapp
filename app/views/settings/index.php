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
                <a href="/dateapp/liked-me" class="btn btn-accent btn-block">Upgrade to Premium</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="settings-section">
        <h3>Change Password</h3>
        <div class="settings-card">
            <form method="POST" action="/dateapp/settings/password">
                <?= \App\Core\CSRF::field() ?>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password (min 8 characters)</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" class="form-input">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Change Password</button>
            </form>
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
                        <form method="POST" action="/dateapp/unblock">
                            <?= \App\Core\CSRF::field() ?>
                            <input type="hidden" name="blocked_id" value="<?= (int)$b['blocked_id'] ?>">
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
            <form method="POST" action="/dateapp/logout">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-outline btn-block">Log Out</button>
            </form>
            <hr style="margin: 1rem 0; border-color: rgba(255,255,255,0.1);">
            <details class="delete-account-details">
                <summary class="btn btn-danger btn-block" style="cursor:pointer; list-style:none; text-align:center;">Delete My Account</summary>
                <form method="POST" action="/dateapp/settings/delete" style="margin-top: 1rem;">
                    <?= \App\Core\CSRF::field() ?>
                    <p class="text-muted" style="margin-bottom: 0.75rem;">This action is permanent. Enter your password to confirm.</p>
                    <div class="form-group">
                        <input type="password" name="confirm_delete_password" placeholder="Enter your password" required class="form-input">
                    </div>
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure? This cannot be undone.')">Permanently Delete Account</button>
                </form>
            </details>
        </div>
    </div>
</section>
