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
            <a href="/dateapp/user?id=<?= (int)$otherUser['id'] ?>" class="chat-user-name"><?= htmlspecialchars($otherUser['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></a>
        </div>
        <a href="/dateapp/game?match_id=<?= (int)$match['id'] ?>" class="chat-game-btn" title="Play a Game">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="17" cy="10" r="1"/><circle cx="15" cy="13" r="1"/></svg>
        </a>
        <button class="chat-menu-btn" onclick="toggleChatMenu()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
        </button>
        <div class="chat-menu" id="chatMenu">
            <a href="/dateapp/user?id=<?= (int)$otherUser['id'] ?>">View Profile</a>
            <button onclick="unmatchUser(<?= (int)$match['id'] ?>)" class="text-danger">Unmatch</button>
        </div>
    </div>

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
                <span class="chat-time"><?= date('g:i A', strtotime($msg['sent_at'])) ?></span>
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
</section>
