<section class="admin-page">
    <h2>🛡️ Verification Requests</h2>
    <a href="/dateapp/admin" class="btn btn-sm btn-outline" style="margin-bottom:1rem;">&larr; Back to Dashboard</a>

    <?php if (empty($requests)): ?>
        <div class="settings-card">
            <p class="text-muted">No pending verification requests.</p>
        </div>
    <?php else: ?>
        <div class="admin-verify-grid">
            <?php foreach ($requests as $req): ?>
            <div class="admin-verify-card">
                <div class="admin-verify-photos">
                    <div class="admin-verify-photo">
                        <span class="admin-verify-label">Profile Photo</span>
                        <?php if (!empty($req['profile_photo'])): ?>
                            <img src="/dateapp/public/<?= htmlspecialchars($req['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Profile">
                        <?php else: ?>
                            <div class="admin-verify-no-photo">No photo</div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-verify-photo">
                        <span class="admin-verify-label">Verification Photo</span>
                        <img src="/dateapp/public/<?= htmlspecialchars($req['photo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Verification">
                    </div>
                </div>
                <div class="admin-verify-info">
                    <strong><?= htmlspecialchars($req['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></strong>
                    (User #<?= (int)$req['user_id'] ?>)
                    <br>
                    <span class="text-muted">Gesture: <?= htmlspecialchars(\App\Models\Verification::getGestureLabel($req['gesture']), ENT_QUOTES, 'UTF-8') ?></span>
                    <br>
                    <span class="text-muted">Submitted: <?= date('M j, Y g:i A', strtotime($req['created_at'])) ?></span>
                </div>
                <div class="admin-verify-actions">
                    <form method="POST" action="/dateapp/admin/verify/handle" style="display:inline">
                        <?= \App\Core\CSRF::field() ?>
                        <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-primary">✅ Approve</button>
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">❌ Reject</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
