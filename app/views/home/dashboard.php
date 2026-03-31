<section class="dashboard">
    <h2>Welcome back, <?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>!</h2>
    <p class="text-muted">Your dashboard is coming soon. We're building the matching engine!</p>

    <div class="dashboard-cards">
        <div class="dash-card">
            <h3>0</h3>
            <p>New Matches</p>
        </div>
        <div class="dash-card">
            <h3>0</h3>
            <p>Messages</p>
        </div>
        <div class="dash-card">
            <h3>50</h3>
            <p>Swipes Left Today</p>
        </div>
    </div>
</section>
