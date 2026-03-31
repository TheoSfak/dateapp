<section class="profile-page profile-view-page">
    <div class="profile-header-card">
        <div class="profile-photo-wrap">
            <?php if (!empty($profile['primary_photo'])): ?>
                <img src="/dateapp/public/<?= htmlspecialchars($profile['primary_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Photo" class="profile-photo-lg">
            <?php else: ?>
                <div class="profile-photo-lg profile-photo-placeholder">
                    <?= strtoupper(substr($profile['name'] ?? '?', 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-header-info">
            <h1><?= htmlspecialchars($profile['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?><?php if ($age): ?>, <span class="profile-age"><?= $age ?></span><?php endif; ?></h1>
            <?php if (!empty($profile['city'])): ?>
                <p class="profile-location">📍 <?= htmlspecialchars($profile['city'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($isMatched): ?>
                <p class="match-badge">✨ You're matched!</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($profile['bio'])): ?>
    <div class="profile-section-card">
        <h3>About</h3>
        <p><?= nl2br(htmlspecialchars($profile['bio'], ENT_QUOTES, 'UTF-8')) ?></p>
    </div>
    <?php endif; ?>

    <div class="profile-section-card">
        <h3>Details</h3>
        <div class="profile-details-grid">
            <?php if (!empty($profile['gender'])): ?>
            <div class="detail-item"><span class="detail-label">Gender</span><span class="detail-value"><?= ucfirst(htmlspecialchars($profile['gender'], ENT_QUOTES, 'UTF-8')) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($profile['relationship_goal'])): ?>
            <div class="detail-item"><span class="detail-label">Looking for</span><span class="detail-value"><?= ucfirst(str_replace('-',' ',htmlspecialchars($profile['relationship_goal'], ENT_QUOTES, 'UTF-8'))) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($profile['height_cm'])): ?>
            <div class="detail-item"><span class="detail-label">Height</span><span class="detail-value"><?= (int)$profile['height_cm'] ?> cm</span></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($photos)): ?>
    <div class="profile-section-card">
        <h3>Photos</h3>
        <div class="photo-grid">
            <?php foreach ($photos as $photo): ?>
            <div class="photo-grid-item">
                <img src="/dateapp/public/<?= htmlspecialchars($photo['file_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Photo">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="profile-view-actions">
        <button class="btn btn-outline btn-sm" onclick="reportUser(<?= (int)$profile['id'] ?>)">🚩 Report</button>
        <button class="btn btn-outline btn-sm" onclick="blockUser(<?= (int)$profile['id'] ?>)">🚫 Block</button>
    </div>
</section>
