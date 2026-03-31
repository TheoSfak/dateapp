<section class="admin-page">
    <div class="admin-header">
        <h2>Manage Users</h2>
        <a href="/dateapp/admin" class="btn btn-outline btn-sm">← Back</a>
    </div>

    <form class="admin-search-bar" method="GET" action="/dateapp/admin/users">
        <input type="text" name="q" placeholder="Search by name or email..." value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <select name="status">
            <option value="">All Status</option>
            <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            <option value="banned" <?= ($_GET['status'] ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
        </select>
        <button class="btn btn-primary btn-sm">Search</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Premium</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center">No users found.</td></tr>
            <?php else: foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td>
                        <a href="/dateapp/profile/view/<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></a>
                    </td>
                    <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($u['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= $u['is_premium'] ? '⭐' : '—' ?></td>
                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="/dateapp/admin/users/status" style="display:inline">
                            <?= \App\Core\CSRF::field() ?>
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <?php if ($u['status'] === 'active'): ?>
                                <input type="hidden" name="status" value="suspended">
                                <button class="btn btn-xs btn-warning">Suspend</button>
                            <?php elseif ($u['status'] === 'suspended'): ?>
                                <input type="hidden" name="status" value="active">
                                <button class="btn btn-xs btn-success">Activate</button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="active">
                                <button class="btn btn-xs btn-success">Unban</button>
                            <?php endif; ?>
                        </form>
                        <?php if ($u['status'] !== 'banned'): ?>
                        <form method="POST" action="/dateapp/admin/users/status" style="display:inline">
                            <?= \App\Core\CSRF::field() ?>
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="status" value="banned">
                            <button class="btn btn-xs btn-danger">Ban</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
