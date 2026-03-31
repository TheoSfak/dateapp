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

    <!-- Availability Calendar Section -->
    <div class="settings-section">
        <h3>📅 Availability Calendar</h3>
        <p class="text-muted" style="margin-bottom:0.75rem;">Set your typical free times so we can find matches with overlapping schedules.</p>
        <div class="settings-card">
            <div class="avail-calendar" id="availCalendar">
                <?php
                    $dayNames = \App\Models\Availability::getDayNames();
                    $grouped = [];
                    foreach ($availSlots ?? [] as $s) {
                        $grouped[(int)$s['day_of_week']][] = $s;
                    }
                ?>
                <?php foreach ($dayNames as $dayIdx => $dayName): ?>
                <div class="avail-day-row" data-day="<?= $dayIdx ?>">
                    <span class="avail-day-label"><?= substr($dayName, 0, 3) ?></span>
                    <div class="avail-day-slots">
                        <?php if (!empty($grouped[$dayIdx])): ?>
                            <?php foreach ($grouped[$dayIdx] as $slot): ?>
                            <span class="avail-slot-tag">
                                <?= date('g:iA', strtotime($slot['start_time'])) ?>–<?= date('g:iA', strtotime($slot['end_time'])) ?>
                                <button class="avail-slot-remove" data-day="<?= $dayIdx ?>" data-start="<?= htmlspecialchars($slot['start_time'], ENT_QUOTES, 'UTF-8') ?>" data-end="<?= htmlspecialchars($slot['end_time'], ENT_QUOTES, 'UTF-8') ?>">&times;</button>
                            </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="avail-empty">No times set</span>
                        <?php endif; ?>
                    </div>
                    <button class="avail-add-btn" data-day="<?= $dayIdx ?>">+ Add</button>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Slot Mini Form (appears inline) -->
            <div class="avail-add-form" id="availAddForm" style="display:none">
                <div class="avail-add-form-inner">
                    <span id="availAddDay" class="avail-add-day-label"></span>
                    <input type="hidden" id="availAddDayVal">
                    <select id="availStartTime" class="form-input form-input-sm">
                        <?php for ($h = 6; $h <= 22; $h++): ?>
                        <option value="<?= sprintf('%02d:00', $h) ?>"><?= date('g:i A', strtotime("$h:00")) ?></option>
                        <option value="<?= sprintf('%02d:30', $h) ?>"><?= date('g:i A', strtotime("$h:30")) ?></option>
                        <?php endfor; ?>
                    </select>
                    <span>to</span>
                    <select id="availEndTime" class="form-input form-input-sm">
                        <?php for ($h = 7; $h <= 23; $h++): ?>
                        <option value="<?= sprintf('%02d:00', $h) ?>"><?= date('g:i A', strtotime("$h:00")) ?></option>
                        <?php if ($h < 23): ?>
                        <option value="<?= sprintf('%02d:30', $h) ?>"><?= date('g:i A', strtotime("$h:30")) ?></option>
                        <?php endif; ?>
                        <?php endfor; ?>
                    </select>
                    <button class="btn btn-sm btn-primary" id="availAddConfirm">Add</button>
                    <button class="btn btn-sm btn-outline" id="availAddCancel">Cancel</button>
                </div>
            </div>

            <button class="btn btn-primary btn-block" id="availSaveBtn" style="margin-top:0.75rem">Save Availability</button>
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
