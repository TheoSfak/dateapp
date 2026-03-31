<section class="spotlight-page page-enter">
    <div class="spotlight-header">
        <h1>✨ My Spotlight</h1>
        <p>Answer prompts to show personality on your profile. Matches love them!</p>
    </div>

    <!-- Current Answers -->
    <div class="spotlight-section">
        <h2>Your Answers <span class="spotlight-count">(<?= count($myAnswers) ?>/5)</span></h2>

        <?php if (!empty($myAnswers)): ?>
        <div class="spotlight-answers" id="spotlightAnswers">
            <?php foreach ($myAnswers as $a): ?>
            <div class="spotlight-answer-card reveal" data-prompt-id="<?= (int)$a['prompt_id'] ?>">
                <div class="spotlight-answer-header">
                    <span class="spotlight-emoji"><?= $a['emoji'] ?></span>
                    <span class="spotlight-prompt-text"><?= htmlspecialchars($a['prompt'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <p class="spotlight-answer-text"><?= htmlspecialchars($a['answer'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="spotlight-answer-actions">
                    <button class="btn btn-sm btn-outline spotlight-edit-btn" onclick="editAnswer(<?= (int)$a['prompt_id'] ?>, '<?= htmlspecialchars($a['prompt'], ENT_QUOTES, 'UTF-8') ?>')">Edit</button>
                    <button class="btn btn-sm btn-ghost spotlight-delete-btn" onclick="deleteAnswer(<?= (int)$a['prompt_id'] ?>)">Remove</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="spotlight-empty reveal">
            <div class="empty-icon">💡</div>
            <p>No prompt answers yet. Pick one below to get started!</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pick a Prompt -->
    <?php if (count($myAnswers) < 5): ?>
    <div class="spotlight-section">
        <h2>Pick a Prompt</h2>
        <div class="spotlight-prompts-grid" id="promptsGrid">
            <?php foreach ($unanswered as $i => $p): ?>
            <button class="spotlight-prompt-card reveal stagger-<?= $i + 1 ?>" onclick="openAnswerModal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['prompt'], ENT_QUOTES, 'UTF-8') ?>', '<?= $p['emoji'] ?>')">
                <span class="spotlight-prompt-emoji"><?= $p['emoji'] ?></span>
                <span class="spotlight-prompt-label"><?= htmlspecialchars($p['prompt'], ENT_QUOTES, 'UTF-8') ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline spotlight-more-btn" id="loadMorePrompts">Show More Prompts</button>
    </div>
    <?php endif; ?>

    <!-- Answer Modal -->
    <div class="spotlight-modal" id="spotlightModal" style="display:none">
        <div class="spotlight-modal-overlay" onclick="closeAnswerModal()"></div>
        <div class="spotlight-modal-content">
            <div class="spotlight-modal-header">
                <span class="spotlight-modal-emoji" id="modalEmoji"></span>
                <h3 id="modalPromptText"></h3>
            </div>
            <textarea id="modalAnswer" maxlength="500" placeholder="Type your answer..." rows="4"></textarea>
            <div class="spotlight-modal-footer">
                <span class="spotlight-char-count"><span id="charCount">0</span>/500</span>
                <div>
                    <button class="btn btn-outline btn-sm" onclick="closeAnswerModal()">Cancel</button>
                    <button class="btn btn-primary btn-sm" id="modalSaveBtn" onclick="saveAnswer()">Save Answer</button>
                </div>
            </div>
            <input type="hidden" id="modalPromptId" value="">
        </div>
    </div>
</section>

<script>
let currentPromptId = 0;

function openAnswerModal(promptId, promptText, emoji) {
    currentPromptId = promptId;
    document.getElementById('modalPromptId').value = promptId;
    document.getElementById('modalPromptText').textContent = promptText;
    document.getElementById('modalEmoji').textContent = emoji;
    document.getElementById('modalAnswer').value = '';
    document.getElementById('charCount').textContent = '0';
    document.getElementById('spotlightModal').style.display = '';
    document.getElementById('modalAnswer').focus();
}

function editAnswer(promptId, promptText) {
    const card = document.querySelector('[data-prompt-id="' + promptId + '"]');
    const answer = card?.querySelector('.spotlight-answer-text')?.textContent || '';
    openAnswerModal(promptId, promptText, card?.querySelector('.spotlight-emoji')?.textContent || '✨');
    document.getElementById('modalAnswer').value = answer;
    document.getElementById('charCount').textContent = answer.length;
}

function closeAnswerModal() {
    document.getElementById('spotlightModal').style.display = 'none';
}

document.getElementById('modalAnswer')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

function saveAnswer() {
    const promptId = parseInt(document.getElementById('modalPromptId').value);
    const answer = document.getElementById('modalAnswer').value.trim();
    if (!answer) return;

    const btn = document.getElementById('modalSaveBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch('/dateapp/spotlight/answer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ prompt_id: promptId, answer: answer })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to save');
            btn.disabled = false;
            btn.textContent = 'Save Answer';
        }
    });
}

function deleteAnswer(promptId) {
    if (!confirm('Remove this answer from your profile?')) return;
    fetch('/dateapp/spotlight/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ prompt_id: promptId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector('[data-prompt-id="' + promptId + '"]');
            if (card) card.remove();
            const countEl = document.querySelector('.spotlight-count');
            if (countEl) {
                const cur = parseInt(countEl.textContent.match(/\d+/)[0]) - 1;
                countEl.textContent = '(' + cur + '/5)';
            }
        }
    });
}

document.getElementById('loadMorePrompts')?.addEventListener('click', function() {
    fetch('/dateapp/spotlight/more', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.prompts?.length) {
            const grid = document.getElementById('promptsGrid');
            data.prompts.forEach(p => {
                const btn = document.createElement('button');
                btn.className = 'spotlight-prompt-card reveal visible';
                btn.onclick = () => openAnswerModal(p.id, p.prompt, p.emoji);
                btn.innerHTML = '<span class="spotlight-prompt-emoji">' + p.emoji + '</span><span class="spotlight-prompt-label">' + p.prompt.replace(/</g, '&lt;') + '</span>';
                grid.appendChild(btn);
            });
        }
    });
});
</script>
