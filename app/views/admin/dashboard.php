<section class="admin-page">
    <h2>Admin Dashboard</h2>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= (int)($stats['total_users'] ?? 0) ?></div>
            <div class="admin-stat-label">Total Users</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= (int)($stats['active_today'] ?? 0) ?></div>
            <div class="admin-stat-label">Active Today</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= (int)($stats['total_matches'] ?? 0) ?></div>
            <div class="admin-stat-label">Matches</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= (int)($stats['total_messages'] ?? 0) ?></div>
            <div class="admin-stat-label">Messages</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= (int)($stats['pending_reports'] ?? 0) ?></div>
            <div class="admin-stat-label">Pending Reports</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-num"><?= (int)($stats['premium_users'] ?? 0) ?></div>
            <div class="admin-stat-label">Premium Users</div>
        </div>
    </div>

    <div class="admin-nav-cards">
        <a href="/dateapp/admin/users" class="admin-nav-card">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            <h3>Manage Users</h3>
            <p>View, search, ban, and promote users</p>
        </a>
        <a href="/dateapp/admin/reports" class="admin-nav-card">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            <h3>Reports</h3>
            <p><?= (int)($stats['pending_reports'] ?? 0) ?> pending reports to review</p>
        </a>
        <a href="/dateapp/admin/verifications" class="admin-nav-card">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            <h3>Verifications</h3>
            <p>Review pending identity verification requests</p>
        </a>
    </div>
</section>
