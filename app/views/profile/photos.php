<?php
    $count = count($photos);
    $canUpload = $count < $maxPhotos;
?>

<div class="photos-page">
    <div class="photos-header">
        <div>
            <h1>My Photos</h1>
            <p class="photos-subtitle"><?= $count ?> / <?= $maxPhotos ?> photos &middot; Tap a photo to manage it</p>
        </div>
        <a href="/dateapp/profile" class="btn btn-sm btn-outline">Back to Profile</a>
    </div>

    <!-- ── Photo Grid ──────────────────────────────── -->
    <div class="photos-manage-grid">
        <?php foreach ($photos as $photo): ?>
        <div class="pm-card <?= $photo['is_primary'] ? 'pm-card--primary' : '' ?>">
            <div class="pm-card-img">
                <img src="/dateapp/public/<?= htmlspecialchars($photo['file_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Photo">
                <?php if ($photo['is_primary']): ?>
                    <span class="pm-badge">Profile Photo</span>
                <?php endif; ?>
            </div>
            <div class="pm-card-actions">
                <?php if (!$photo['is_primary']): ?>
                <form method="POST" action="/dateapp/profile/photo/primary" class="pm-form">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="hidden" name="photo_id" value="<?= (int)$photo['id'] ?>">
                    <input type="hidden" name="from" value="photos">
                    <button type="submit" class="pm-btn pm-btn--primary" title="Set as profile photo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Set as Profile
                    </button>
                </form>
                <?php else: ?>
                <span class="pm-btn pm-btn--current">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Current Profile Photo
                </span>
                <?php endif; ?>
                <form method="POST" action="/dateapp/profile/photo/delete" class="pm-form" onsubmit="return confirm('Delete this photo?')">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="hidden" name="photo_id" value="<?= (int)$photo['id'] ?>">
                    <input type="hidden" name="from" value="photos">
                    <button type="submit" class="pm-btn pm-btn--delete" title="Delete photo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Upload Slot -->
        <?php if ($canUpload): ?>
        <div class="pm-card pm-card--upload">
            <form method="POST" action="/dateapp/profile/photo" enctype="multipart/form-data" class="pm-upload-form" id="photoUploadForm">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="from" value="photos">
                <label class="pm-upload-zone" id="uploadZone">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only" id="photoInput">
                    <div class="pm-upload-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </div>
                    <span class="pm-upload-label">Add Photo</span>
                    <span class="pm-upload-hint">JPG, PNG or WebP &middot; Max 5MB</span>
                </label>
                <div class="pm-upload-options">
                    <label class="pm-checkbox">
                        <input type="checkbox" name="is_primary" value="1">
                        <span>Set as profile photo</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm pm-upload-btn" id="uploadBtn" disabled>Upload</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($count === 0): ?>
    <div class="pm-empty">
        <div class="pm-empty-icon">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </div>
        <h3>No photos yet</h3>
        <p>Upload your first photo to get discovered by others.</p>
    </div>
    <?php endif; ?>

    <div class="pm-tips">
        <h3>Photo Tips</h3>
        <ul>
            <li>Use well-lit, clear photos of your face</li>
            <li>Your profile photo is the first thing people see</li>
            <li>Add at least 3 photos to get more matches</li>
            <li>Avoid group photos as your profile picture</li>
        </ul>
    </div>
</div>

<script>
(function() {
    const input = document.getElementById('photoInput');
    const btn = document.getElementById('uploadBtn');
    const zone = document.getElementById('uploadZone');
    if (!input || !btn || !zone) return;

    input.addEventListener('change', function() {
        btn.disabled = !this.files.length;
        if (this.files.length) {
            zone.querySelector('.pm-upload-label').textContent = this.files[0].name;
        }
    });

    // Drag & drop
    ['dragenter', 'dragover'].forEach(e => zone.addEventListener(e, function(ev) {
        ev.preventDefault(); zone.classList.add('pm-upload-zone--active');
    }));
    ['dragleave', 'drop'].forEach(e => zone.addEventListener(e, function(ev) {
        ev.preventDefault(); zone.classList.remove('pm-upload-zone--active');
    }));
    zone.addEventListener('drop', function(ev) {
        if (ev.dataTransfer.files.length) {
            input.files = ev.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
})();
</script>
