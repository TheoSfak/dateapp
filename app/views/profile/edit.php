<section class="profile-edit-page">
    <h2>Edit Profile</h2>

    <form method="POST" action="/dateapp/profile/update" class="profile-form">
        <?= \App\Core\CSRF::field() ?>

        <div class="form-card">
            <h3>Basic Info</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Your first name" required>
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($profile['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8') ?>" max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="bio">About Me</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell people about yourself..."><?= htmlspecialchars($profile['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <div class="form-card">
            <h3>Preferences</h3>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="gender">I am</label>
                    <select id="gender" name="gender">
                        <option value="">Select...</option>
                        <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="non-binary" <?= ($profile['gender'] ?? '') === 'non-binary' ? 'selected' : '' ?>>Non-binary</option>
                        <option value="other" <?= ($profile['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="looking_for">Looking for</label>
                    <select id="looking_for" name="looking_for">
                        <option value="">Select...</option>
                        <option value="male" <?= ($profile['looking_for'] ?? '') === 'male' ? 'selected' : '' ?>>Men</option>
                        <option value="female" <?= ($profile['looking_for'] ?? '') === 'female' ? 'selected' : '' ?>>Women</option>
                        <option value="everyone" <?= ($profile['looking_for'] ?? '') === 'everyone' ? 'selected' : '' ?>>Everyone</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="relationship_goal">Goal</label>
                    <select id="relationship_goal" name="relationship_goal">
                        <option value="undecided" <?= ($profile['relationship_goal'] ?? '') === 'undecided' ? 'selected' : '' ?>>Undecided</option>
                        <option value="long-term" <?= ($profile['relationship_goal'] ?? '') === 'long-term' ? 'selected' : '' ?>>Long-term</option>
                        <option value="short-term" <?= ($profile['relationship_goal'] ?? '') === 'short-term' ? 'selected' : '' ?>>Short-term</option>
                        <option value="friendship" <?= ($profile['relationship_goal'] ?? '') === 'friendship' ? 'selected' : '' ?>>Friendship</option>
                        <option value="casual" <?= ($profile['relationship_goal'] ?? '') === 'casual' ? 'selected' : '' ?>>Casual</option>
                    </select>
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="height_cm">Height (cm)</label>
                    <input type="number" id="height_cm" name="height_cm" value="<?= (int)($profile['height_cm'] ?? 0) ?: '' ?>" min="100" max="250" placeholder="170">
                </div>
                <div class="form-group">
                    <label for="smoking">Smoking</label>
                    <select id="smoking" name="smoking">
                        <option value="">Prefer not to say</option>
                        <option value="never" <?= ($profile['smoking'] ?? '') === 'never' ? 'selected' : '' ?>>Never</option>
                        <option value="sometimes" <?= ($profile['smoking'] ?? '') === 'sometimes' ? 'selected' : '' ?>>Sometimes</option>
                        <option value="regularly" <?= ($profile['smoking'] ?? '') === 'regularly' ? 'selected' : '' ?>>Regularly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="drinking">Drinking</label>
                    <select id="drinking" name="drinking">
                        <option value="">Prefer not to say</option>
                        <option value="never" <?= ($profile['drinking'] ?? '') === 'never' ? 'selected' : '' ?>>Never</option>
                        <option value="sometimes" <?= ($profile['drinking'] ?? '') === 'sometimes' ? 'selected' : '' ?>>Sometimes</option>
                        <option value="regularly" <?= ($profile['drinking'] ?? '') === 'regularly' ? 'selected' : '' ?>>Regularly</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3>Location</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($profile['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Your city">
                </div>
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($profile['country'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Your country">
                </div>
            </div>
            <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($profile['latitude'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($profile['longitude'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button type="button" id="detectLocationBtn" class="btn btn-outline btn-sm">📍 Detect My Location</button>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Save Changes</button>
    </form>

    <div class="form-card photos-section">
        <h3>Photos (<?= count($photos) ?>/<?= \App\Core\Config::get('app.max_photos_per_user', 6) ?>)</h3>

        <?php if (!empty($photos)): ?>
        <div class="photo-grid photo-grid-edit">
            <?php foreach ($photos as $photo): ?>
            <div class="photo-grid-item">
                <img src="/dateapp/public/<?= htmlspecialchars($photo['file_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Photo">
                <?php if ($photo['is_primary']): ?>
                    <span class="photo-badge">Primary</span>
                <?php else: ?>
                    <form method="POST" action="/dateapp/profile/photo/primary" class="photo-action">
                        <?= \App\Core\CSRF::field() ?>
                        <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                        <button type="submit" class="photo-btn" title="Set as primary">⭐</button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="/dateapp/profile/photo/delete" class="photo-action photo-action-delete">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                    <button type="submit" class="photo-btn photo-btn-delete" title="Delete photo">✕</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (count($photos) < \App\Core\Config::get('app.max_photos_per_user', 6)): ?>
        <form method="POST" action="/dateapp/profile/photo" enctype="multipart/form-data" class="upload-form">
            <?= \App\Core\CSRF::field() ?>
            <div class="upload-zone" id="uploadZone">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>Click or drag a photo here</p>
                <span class="text-muted">JPG, PNG or WebP · Max 5MB</span>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="photoInput" class="upload-input">
            </div>
            <label class="checkbox-label">
                <input type="checkbox" name="is_primary" value="1"> Set as primary photo
            </label>
            <button type="submit" class="btn btn-primary">Upload Photo</button>
        </form>
        <?php endif; ?>
    </div>
</section>
