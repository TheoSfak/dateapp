<section class="discover-page">
    <?php if (empty($hasProfile)): ?>
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <h2>Complete Your Profile First</h2>
            <p>Add your name and details so others can find you!</p>
            <a href="/dateapp/profile/edit" class="btn btn-primary">Complete Profile</a>
        </div>
    <?php elseif (empty($stack)): ?>
        <div class="empty-state">
            <div class="empty-icon">🌍</div>
            <h2>No More Profiles</h2>
            <p>You've seen everyone nearby! Check back later or expand your filters.</p>
            <button class="btn btn-outline" onclick="document.getElementById('filterPanel').classList.toggle('open')">Adjust Filters</button>
        </div>
    <?php else: ?>

    <!-- Swipe Counter -->
    <div class="swipe-counter">
        <span class="swipe-count"><?= ($dailyLimit - $swipesToday) ?></span> swipes left today
        <?php if (!$isPremium): ?>
            <a href="/dateapp/liked-me" class="premium-link">Go Premium ⚡</a>
        <?php endif; ?>
    </div>

    <!-- Card Stack -->
    <div class="swipe-stack" id="swipeStack">
        <?php foreach (array_reverse($stack) as $i => $person): ?>
        <div class="swipe-card <?= $i === count($stack) - 1 ? 'swipe-card-active' : '' ?>"
             data-user-id="<?= (int)$person['user_id'] ?>"
             style="z-index: <?= $i + 1 ?>">
            <div class="swipe-card-image">
                <?php if (!empty($person['primary_photo'])): ?>
                    <img src="/dateapp/public/<?= htmlspecialchars($person['primary_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($person['name'], ENT_QUOTES, 'UTF-8') ?>" draggable="false">
                <?php else: ?>
                    <div class="swipe-card-placeholder">
                        <?= strtoupper(substr($person['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <!-- Compatibility Badge -->
                <?php if (!empty($person['compatibility'])): ?>
                <div class="compatibility-badge"><?= (int)$person['compatibility'] ?>%</div>
                <?php endif; ?>

                <div class="swipe-card-gradient"></div>
                <div class="swipe-card-info">
                    <h2><?= htmlspecialchars($person['name'], ENT_QUOTES, 'UTF-8') ?><?php if ($person['age']): ?>, <?= (int)$person['age'] ?><?php endif; ?></h2>
                    <p>
                        <?php if (!empty($person['city'])): ?>📍 <?= htmlspecialchars($person['city'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                        <?php if ($person['distance'] !== null): ?> · <?= round($person['distance'], 1) ?> km<?php endif; ?>
                    </p>
                    <?php if (!empty($person['bio'])): ?>
                        <p class="swipe-card-bio"><?= htmlspecialchars(mb_strimwidth($person['bio'], 0, 120, '...'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <!-- Match Reasons -->
                    <?php if (!empty($person['match_reasons'])): ?>
                    <div class="match-reasons">
                        <?php foreach ($person['match_reasons'] as $reason): ?>
                            <span class="reason-pill"><?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Shared Interests -->
                    <?php if (!empty($person['shared_interest_names'])): ?>
                    <div class="shared-interests">
                        <?php foreach (array_slice(explode(',', $person['shared_interest_names']), 0, 4) as $tag): ?>
                            <span class="interest-pill"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="swipe-stamp swipe-stamp-like">LIKE</div>
                <div class="swipe-stamp swipe-stamp-nope">NOPE</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Action Buttons -->
    <div class="swipe-actions">
        <button class="swipe-btn swipe-btn-nope" id="btnNope" title="Dislike">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <button class="swipe-btn swipe-btn-super" id="btnSuper" title="Super Like">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </button>
        <button class="swipe-btn swipe-btn-like" id="btnLike" title="Like">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
    </div>
    <?php endif; ?>

    <!-- Filter Panel -->
    <div class="filter-panel" id="filterPanel">
        <div class="filter-header">
            <h3>Discovery Filters</h3>
            <button class="filter-close" onclick="document.getElementById('filterPanel').classList.remove('open')">✕</button>
        </div>
        <form method="GET" action="/dateapp/discover" class="filter-form">
            <div class="form-group">
                <label>Age Range</label>
                <div class="range-group">
                    <input type="number" name="min_age" value="<?= (int)($filters['min_age'] ?? 18) ?>" min="18" max="99" class="range-input">
                    <span>to</span>
                    <input type="number" name="max_age" value="<?= (int)($filters['max_age'] ?? 99) ?>" min="18" max="99" class="range-input">
                </div>
            </div>
            <div class="form-group">
                <label>Max Distance (km)</label>
                <input type="number" name="max_distance" value="<?= (int)($filters['max_distance'] ?? 100) ?>" min="1" max="500">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
        </form>
    </div>
    <button class="filter-toggle" onclick="document.getElementById('filterPanel').classList.toggle('open')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
    </button>
</section>

<!-- Match Modal -->
<div class="modal" id="matchModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content match-modal">
        <div class="match-celebration">
            <h2>🎉 It's a Match!</h2>
            <p id="matchName"></p>
            <img id="matchPhoto" src="" alt="" class="match-photo">
            <div class="match-modal-actions">
                <a href="/dateapp/matches" class="btn btn-primary">Send a Message</a>
                <button class="btn btn-outline" onclick="closeMatchModal()">Keep Swiping</button>
            </div>
        </div>
    </div>
</div>
