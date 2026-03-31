<section class="admin-page">
    <div class="admin-header">
        <h2>User Reports</h2>
        <a href="/dateapp/admin" class="btn btn-outline btn-sm">← Back</a>
    </div>

    <?php if (empty($reports)): ?>
        <div class="empty-state">
            <div class="empty-icon">✅</div>
            <h3>All clear!</h3>
            <p>No pending reports to review.</p>
        </div>
    <?php else: ?>
    <div class="reports-list">
        <?php foreach ($reports as $r): ?>
        <div class="report-card">
            <div class="report-header">
                <span class="badge badge-<?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($r['status']), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="report-date"><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></span>
            </div>
            <div class="report-body">
                <div class="report-users">
                    <span><strong>Reporter:</strong> <?= htmlspecialchars($r['reporter_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?> (#<?= (int)$r['reporter_id'] ?>)</span>
                    <span><strong>Reported:</strong> <a href="/dateapp/user?id=<?= (int)$r['reported_id'] ?>"><?= htmlspecialchars($r['reported_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></a> (#<?= (int)$r['reported_id'] ?>)</span>
                </div>
                <div class="report-reason">
                    <strong>Reason:</strong> <?= htmlspecialchars($r['reason'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php if (!empty($r['details'])): ?>
                    <div class="report-details"><?= nl2br(htmlspecialchars($r['details'], ENT_QUOTES, 'UTF-8')) ?></div>
                <?php endif; ?>
            </div>
            <?php if ($r['status'] === 'pending'): ?>
            <div class="report-actions">
                <form method="POST" action="/dateapp/admin/reports/handle" style="display:inline">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="action" value="dismiss">
                    <button class="btn btn-sm btn-outline">Dismiss</button>
                </form>
                <form method="POST" action="/dateapp/admin/reports/handle" style="display:inline">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="action" value="warn">
                    <button class="btn btn-sm btn-warning">Warn</button>
                </form>
                <form method="POST" action="/dateapp/admin/reports/handle" style="display:inline">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="action" value="ban">
                    <button class="btn btn-sm btn-danger">Ban User</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
