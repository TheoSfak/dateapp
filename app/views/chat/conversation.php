<section class="chat-page">
    <div class="chat-header">
        <a href="/dateapp/matches" class="chat-back-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <div class="chat-user-info">
            <div class="chat-user-avatar">
                <?php if (!empty($otherUser['primary_photo'])): ?>
                    <img src="/dateapp/public/<?= htmlspecialchars($otherUser['primary_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= strtoupper(substr($otherUser['name'] ?? '?', 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="chat-user-name-wrap">
                <a href="/dateapp/user?id=<?= (int)$otherUser['id'] ?>" class="chat-user-name">
                    <?= htmlspecialchars($otherUser['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($otherUser['is_verified'])): ?>
                        <span class="verified-badge" title="Verified">✓</span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <a href="/dateapp/game?match_id=<?= (int)$match['id'] ?>" class="chat-game-btn" title="Play a Game">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="17" cy="10" r="1"/><circle cx="15" cy="13" r="1"/></svg>
        </a>
        <button class="chat-feature-btn" id="dateIdeasToggle" title="Date Ideas">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </button>
        <button class="chat-menu-btn" onclick="toggleChatMenu()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
        </button>
        <div class="chat-menu" id="chatMenu">
            <a href="/dateapp/user?id=<?= (int)$otherUser['id'] ?>">View Profile</a>
            <button onclick="unmatchUser(<?= (int)$match['id'] ?>)" class="text-danger">Unmatch</button>
        </div>
    </div>

    <!-- Date Ideas Panel (hidden by default) -->
    <div class="chat-ideas-panel" id="dateIdeasPanel" style="display:none">
        <div class="chat-ideas-header">
            <h3>💡 Date Ideas for You Two</h3>
            <button class="chat-ideas-close" id="dateIdeasClose">&times;</button>
        </div>
        <?php if (!empty($dateIdeas['shared_interests'])): ?>
            <p class="chat-ideas-shared">Shared interests: <strong><?= htmlspecialchars(implode(', ', $dateIdeas['shared_interests']), ENT_QUOTES, 'UTF-8') ?></strong></p>
        <?php endif; ?>
        <div class="chat-ideas-list">
            <?php foreach ($dateIdeas['ideas'] ?? [] as $idea): ?>
            <div class="chat-idea-card">
                <span class="chat-idea-emoji"><?= $idea['emoji'] ?></span>
                <div>
                    <strong><?= htmlspecialchars($idea['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($idea['description'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($availOverlap)): ?>
        <div class="chat-avail-overlap">
            <h4>📅 Overlapping Free Time</h4>
            <?php foreach ($availOverlap as $o): ?>
            <div class="chat-avail-slot">
                <span class="chat-avail-day"><?= \App\Models\Availability::getDayName((int)$o['day_of_week']) ?></span>
                <span class="chat-avail-time"><?= date('g:i A', strtotime($o['overlap_start'])) ?> – <?= date('g:i A', strtotime($o['overlap_end'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Anti-Ghosting Nudge -->
    <?php if (!empty($ghostInfo) && $ghostInfo['needs_nudge']): ?>
    <div class="chat-ghost-nudge" id="ghostNudge">
        <div class="chat-ghost-nudge-inner">
            <span class="chat-ghost-icon">👋</span>
            <div class="chat-ghost-text">
                <strong>Don't leave them hanging!</strong>
                <p>It's been <?= (int)$ghostInfo['hours_since_last'] ?> hours since their last message.</p>
            </div>
            <button class="btn btn-sm btn-outline" id="politePassBtn" data-match-id="<?= (int)$match['id'] ?>">Polite Pass</button>
            <button class="chat-ghost-dismiss" id="ghostDismiss">&times;</button>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($ghostInfo) && $ghostInfo['partner_waiting']): ?>
    <div class="chat-ghost-nudge chat-ghost-nudge--waiting" id="ghostNudgeWait">
        <div class="chat-ghost-nudge-inner">
            <span class="chat-ghost-icon">⏳</span>
            <div class="chat-ghost-text">
                <p>You sent your last message <?= (int)$ghostInfo['hours_since_last'] ?>h ago. Be patient — they might be busy!</p>
            </div>
            <button class="chat-ghost-dismiss" onclick="this.closest('.chat-ghost-nudge').remove()">&times;</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="chat-messages" id="chatMessages" data-match-id="<?= (int)$match['id'] ?>" data-user-id="<?= (int)$userId ?>">
        <?php if (empty($messages)): ?>
            <div class="chat-empty">
                <p>You matched! Start the conversation 🎉</p>
            </div>
        <?php endif; ?>
        <?php
        $lastDate = '';
        foreach ($messages as $msg):
            $msgDate = date('M j, Y', strtotime($msg['sent_at']));
            if ($msgDate !== $lastDate):
                $lastDate = $msgDate;
        ?>
            <div class="chat-date-sep"><?= htmlspecialchars($msgDate, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
            <div class="chat-bubble <?= (int)$msg['sender_id'] === (int)$userId ? 'chat-bubble-mine' : 'chat-bubble-theirs' ?>">
                <p><?= nl2br(htmlspecialchars($msg['message_text'], ENT_QUOTES, 'UTF-8')) ?></p>
                <span class="chat-time">
                    <?= date('g:i A', strtotime($msg['sent_at'])) ?>
                    <?php if ((int)$msg['sender_id'] === (int)$userId): ?>
                        <?php if (!empty($isPremium) && !empty($msg['read_at'])): ?>
                            <span class="chat-receipt chat-receipt--read" title="Read <?= htmlspecialchars(date('M j, g:i A', strtotime($msg['read_at'])), ENT_QUOTES, 'UTF-8') ?>">✓✓</span>
                        <?php elseif (!empty($msg['is_read'])): ?>
                            <span class="chat-receipt chat-receipt--delivered">✓✓</span>
                        <?php else: ?>
                            <span class="chat-receipt chat-receipt--sent">✓</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <form class="chat-input-bar" id="chatForm" onsubmit="return sendMessage(event)">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::token() ?>">
        <input type="text" name="body" id="chatInput" placeholder="Type a message..." autocomplete="off" maxlength="2000" required>
        <button type="submit" class="chat-send-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
    </form>

    <!-- Polite Pass Modal -->
    <div class="polite-pass-modal" id="politePassModal" style="display:none">
        <div class="polite-pass-overlay" id="politePassOverlay"></div>
        <div class="polite-pass-content">
            <h3>Send a Kind Goodbye</h3>
            <p>Choose a message to close things kindly:</p>
            <div class="polite-pass-options">
                <button class="polite-pass-option" data-index="0">
                    "I've really enjoyed chatting with you, but I don't feel a romantic connection. Wishing you all the best! 💛"
                </button>
                <button class="polite-pass-option" data-index="1">
                    "Thanks for the great conversations! I think we're better as friends though. Best of luck out there! 🌟"
                </button>
                <button class="polite-pass-option" data-index="2">
                    "I appreciate getting to know you, but I don't think we're the right match. I hope you find someone amazing! ✨"
                </button>
            </div>
            <button class="btn btn-outline btn-sm polite-pass-cancel" id="politePassCancel">Cancel</button>
        </div>
    </div>
</section>
