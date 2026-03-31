/**
 * DateApp – Full JavaScript
 * Swipe engine, chat, AJAX handlers, geolocation
 */
(function() {
    'use strict';

    const BASE = '/dateapp';
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function ajax(method, url, data) {
        return fetch(BASE + url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data ? JSON.stringify(data) : undefined
        }).then(r => r.json());
    }

    document.addEventListener('DOMContentLoaded', () => {
        // ─── Auto-dismiss alerts ───────────────────────
        document.querySelectorAll('.alert').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s, transform 0.5s';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-8px)';
                setTimeout(() => el.remove(), 500);
            }, 5000);
        });

        // ─── Swipe Engine ──────────────────────────────
        initSwipe();

        // ─── Chat ──────────────────────────────────────
        initChat();

        // ─── Upload Zone ───────────────────────────────
        initUploadZone();

        // ─── Geolocation ───────────────────────────────
        initGeoDetect();
        // ─── Interest Tag Picker ───────────────────────────
        initInterestPicker();
        // ─── Mini-Games ────────────────────────────────
        initGamePicker();
        initGamePlay();
        // ─── Filter Panel ──────────────────────────────
        const filterBtn = document.querySelector('.filter-toggle');
        const filterPanel = document.querySelector('.filter-panel');
        if (filterBtn && filterPanel) {
            filterBtn.addEventListener('click', () => filterPanel.classList.toggle('show'));
        }
        // ─── Anti-Ghosting / Polite Pass ───────────────
        initPolitePass();
        // ─── Date Ideas Panel ──────────────────────────
        initDateIdeas();
        // ─── Availability Calendar ─────────────────────
        initAvailability();
        // ─── Premium Features ──────────────────────────
        initRewind();
        initBoost();
    });

    // ═══════════════════════════════════════════════════════
    // SWIPE ENGINE
    // ═══════════════════════════════════════════════════════
    function initSwipe() {
        const stack = document.querySelector('.swipe-stack');
        if (!stack) return;

        let startX = 0, startY = 0, currentX = 0, isDragging = false;
        const THRESHOLD = 100;

        // Track remaining swipes from the counter element
        function getRemaining() {
            const el = document.querySelector('.swipe-counter');
            if (!el) return Infinity; // no counter = premium/unlimited
            const m = el.textContent.match(/(\d+)/);
            return m ? parseInt(m[1], 10) : 0;
        }

        function showLimitReached() {
            stack.innerHTML =
                '<div class="empty-state">' +
                '<div class="empty-icon">🔒</div>' +
                '<h3>Daily swipe limit reached</h3>' +
                '<p>Upgrade to Premium for unlimited swipes!</p>' +
                '<a href="' + BASE + '/premium" class="btn btn-accent" style="margin-top:.75rem">Go Premium ⚡</a>' +
                '</div>';
            // Disable swipe buttons
            document.querySelectorAll('.swipe-btn-like, .swipe-btn-nope, .swipe-btn-super, .swipe-btn-rewind')
                .forEach(b => b.disabled = true);
        }

        function getTopCard() { return stack.querySelector('.swipe-card:first-child'); }

        function bindCard(card) {
            if (!card) return;
            card.addEventListener('pointerdown', onStart);
            card.addEventListener('pointermove', onMove);
            card.addEventListener('pointerup', onEnd);
            card.addEventListener('pointercancel', onEnd);
        }

        function onStart(e) {
            if (getRemaining() <= 0) { showLimitReached(); return; }
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            currentX = 0;
            this.style.transition = 'none';
            this.setPointerCapture(e.pointerId);
        }

        function onMove(e) {
            if (!isDragging) return;
            currentX = e.clientX - startX;
            const rotate = currentX * 0.08;
            this.style.transform = `translateX(${currentX}px) rotate(${rotate}deg)`;

            const likeStamp = this.querySelector('.swipe-stamp-like');
            const nopeStamp = this.querySelector('.swipe-stamp-nope');
            if (likeStamp) likeStamp.style.opacity = Math.max(0, Math.min(1, currentX / THRESHOLD));
            if (nopeStamp) nopeStamp.style.opacity = Math.max(0, Math.min(1, -currentX / THRESHOLD));
        }

        function onEnd(e) {
            if (!isDragging) return;
            isDragging = false;
            const card = this;
            card.style.transition = 'transform 0.4s ease, opacity 0.4s ease';

            if (Math.abs(currentX) > THRESHOLD) {
                const direction = currentX > 0 ? 'like' : 'pass';
                const flyX = currentX > 0 ? 1000 : -1000;
                card.style.transform = `translateX(${flyX}px) rotate(${flyX * 0.04}deg)`;
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    updateCounter(-1);
                    sendSwipe(card.dataset.userId, direction);
                    const next = getTopCard();
                    if (next) {
                        next.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                        next.style.transform = '';
                        next.style.opacity = '1';
                    }
                    bindCard(getTopCard());
                    if (!getTopCard()) showEmpty();
                    // Check if that was the last free swipe
                    if (getRemaining() <= 0) showLimitReached();
                }, 350);
            } else {
                card.style.transform = '';
                const likeStamp = card.querySelector('.swipe-stamp-like');
                const nopeStamp = card.querySelector('.swipe-stamp-nope');
                if (likeStamp) likeStamp.style.opacity = '0';
                if (nopeStamp) nopeStamp.style.opacity = '0';
            }
        }

        bindCard(getTopCard());

        // Button handlers
        document.querySelector('.swipe-btn-like')?.addEventListener('click', () => triggerSwipe('like'));
        document.querySelector('.swipe-btn-nope')?.addEventListener('click', () => triggerSwipe('pass'));
        document.querySelector('.swipe-btn-super')?.addEventListener('click', () => triggerSwipe('super_like'));

        let swipeLocked = false;

        function triggerSwipe(direction) {
            const card = getTopCard();
            if (!card || swipeLocked) return;
            if (getRemaining() <= 0) { showLimitReached(); return; }
            swipeLocked = true;
            const flyX = direction === 'pass' ? -1000 : 1000;
            card.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
            card.style.transform = `translateX(${flyX}px) rotate(${flyX * 0.04}deg)`;
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                updateCounter(-1);
                sendSwipe(card.dataset.userId, direction);
                bindCard(getTopCard());
                if (!getTopCard()) showEmpty();
                if (getRemaining() <= 0) showLimitReached();
            }, 350);
        }

        function sendSwipe(userId, type) {
            ajax('POST', '/swipe', { target_id: userId, type: type })
                .then(data => {
                    swipeLocked = false;
                    if (data.limit_reached) {
                        showLimitReached();
                        return;
                    }
                    if (data.match) showMatchModal(data);
                })
                .catch(() => { swipeLocked = false; });
        }

        function updateCounter(delta) {
            const el = document.querySelector('.swipe-counter');
            if (!el) return;
            const m = el.textContent.match(/(\d+)/);
            if (m) {
                const n = Math.max(0, parseInt(m[1], 10) + delta);
                el.textContent = el.textContent.replace(/\d+/, n);
            }
        }

        function showEmpty() {
            stack.innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div><h3>No more profiles</h3><p>Check back later or adjust your filters</p></div>';
        }
    }

    // ═══════════════════════════════════════════════════════
    // MATCH MODAL
    // ═══════════════════════════════════════════════════════
    window.showMatchModal = function(data) {
        const modal = document.getElementById('matchModal');
        if (!modal) return;
        if (data.match_name) {
            const nameEl = document.getElementById('matchName');
            if (nameEl) nameEl.textContent = 'You matched with ' + data.match_name + '!';
        }
        if (data.match_photo) {
            const photoEl = document.getElementById('matchPhoto');
            if (photoEl) { photoEl.src = BASE + '/public/' + data.match_photo; photoEl.style.display = 'block'; }
        }
        modal.classList.add('show');
    };
    window.closeMatchModal = function() {
        const modal = document.getElementById('matchModal');
        if (modal) modal.classList.remove('show');
    };

    // ═══════════════════════════════════════════════════════
    // CHAT
    // ═══════════════════════════════════════════════════════
    function initChat() {
        const messages = document.getElementById('chatMessages');
        if (!messages) return;

        const matchId = messages.dataset.matchId;
        if (!matchId) return;

        // Scroll to bottom
        messages.scrollTop = messages.scrollHeight;

        // Poll for new messages every 3 seconds
        let lastMsgId = getLastMsgId();
        const pollInterval = setInterval(() => {
            fetch(BASE + '/chat/poll?match_id=' + matchId + '&last_id=' + lastMsgId, {
                headers: { 'X-CSRF-Token': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        appendMessage(msg);
                        lastMsgId = msg.id;
                    });
                    messages.scrollTop = messages.scrollHeight;
                }
            })
            .catch(() => {});
        }, 3000);

        // Clear polling when leaving page
        window.addEventListener('beforeunload', () => clearInterval(pollInterval));

        function getLastMsgId() {
            const bubbles = messages.querySelectorAll('.chat-bubble');
            return bubbles.length > 0 ? (bubbles[bubbles.length - 1].dataset.msgId || '0') : '0';
        }

        function appendMessage(msg) {
            // Dedup: skip if this message ID is already in the DOM
            if (msg.id && messages.querySelector('[data-msg-id="' + msg.id + '"]')) return;
            const userId = document.getElementById('chatMessages')?.dataset.userId || '0';
            const isMine = String(msg.sender_id) === String(userId);
            const div = document.createElement('div');
            div.className = 'chat-bubble ' + (isMine ? 'chat-bubble-mine' : 'chat-bubble-theirs');
            div.dataset.msgId = msg.id;

            if (msg.message_type === 'voice' && msg.voice_path) {
                const dur = parseInt(msg.voice_duration || 0, 10);
                const mins = Math.floor(dur / 60);
                const secs = String(dur % 60).padStart(2, '0');
                let bars = '';
                for (let b = 0; b < 20; b++) bars += '<span class="voice-bar" style="height:' + (20 + Math.random() * 80) + '%"></span>';
                div.innerHTML = '<div class="voice-msg">' +
                    '<button class="voice-play-btn" onclick="playVoice(this)" data-src="' + BASE + '/public/' + escapeHtml(msg.voice_path) + '">' +
                    '<svg class="voice-icon-play" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>' +
                    '<svg class="voice-icon-pause" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>' +
                    '</button>' +
                    '<div class="voice-waveform"><div class="voice-progress" style="width:0%"></div>' + bars + '</div>' +
                    '<span class="voice-duration">' + mins + ':' + secs + '</span>' +
                    '</div>' +
                    '<span class="chat-time">' + formatTime(msg.created_at || msg.sent_at) + '</span>';
            } else {
                const text = msg.body || msg.message_text || '';
                div.innerHTML = '<p>' + escapeHtml(text) + '</p><span class="chat-time">' + formatTime(msg.created_at || msg.sent_at) + '</span>';
            }
            messages.appendChild(div);
            // Hide icebreakers after first message is sent
            const iceSection = document.getElementById('icebreakers');
            if (iceSection) iceSection.remove();
            const emptyMsg = messages.querySelector('.chat-empty');
            if (emptyMsg) emptyMsg.remove();
            // Keep lastMsgId in sync so polling skips this message
            if (msg.id && Number(msg.id) > Number(lastMsgId)) lastMsgId = msg.id;
        }

        // Send message (defined inside initChat to reuse appendMessage)
        window.sendMessage = function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            if (!matchId || !input.value.trim()) return false;

            const body = input.value.trim();
            input.value = '';

            ajax('POST', '/chat/send', { match_id: matchId, body: body })
                .then(data => {
                    if (data.success && data.message) {
                        appendMessage(data.message);
                        messages.scrollTop = messages.scrollHeight;
                    }
                })
                .catch(() => {});
            return false;
        };
    }

    window.toggleChatMenu = function() {
        document.getElementById('chatMenu')?.classList.toggle('show');
    };

    window.unmatchUser = function(matchId) {
        if (!confirm('Are you sure you want to unmatch?')) return;
        ajax('POST', '/chat/unmatch', { match_id: matchId })
            .then(() => { window.location.href = BASE + '/matches'; })
            .catch(() => {});
    };

    // ═══════════════════════════════════════════════════════
    // REPORT / BLOCK
    // ═══════════════════════════════════════════════════════
    window.reportUser = function(userId) {
        const reason = prompt('Why are you reporting this user?');
        if (!reason) return;
        ajax('POST', '/report', { reported_id: userId, reason: reason })
            .then(data => {
                if (data.success) alert('Report submitted. Thank you.');
                else alert(data.error || 'Failed to submit report.');
            })
            .catch(() => alert('Error submitting report.'));
    };

    window.blockUser = function(userId) {
        if (!confirm('Block this user? They won\'t be able to see or message you.')) return;
        ajax('POST', '/block', { blocked_id: userId })
            .then(data => {
                if (data.success) { alert('User blocked.'); window.location.reload(); }
                else alert(data.error || 'Failed to block user.');
            })
            .catch(() => alert('Error blocking user.'));
    };

    // ═══════════════════════════════════════════════════════
    // UPLOAD ZONE
    // ═══════════════════════════════════════════════════════
    function initUploadZone() {
        const zone = document.querySelector('.upload-zone');
        const input = document.getElementById('photoInput');
        if (!zone || !input) return;

        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.closest('form')?.submit();
            }
        });
        input.addEventListener('change', () => { if (input.files.length) input.closest('form')?.submit(); });
    }

    // ═══════════════════════════════════════════════════════
    // GEOLOCATION DETECT
    // ═══════════════════════════════════════════════════════
    function initGeoDetect() {
        const btn = document.getElementById('detectLocationBtn');
        if (!btn) return;
        btn.addEventListener('click', () => {
            if (!navigator.geolocation) { alert('Geolocation not supported'); return; }
            btn.textContent = 'Detecting...';
            btn.disabled = true;
            navigator.geolocation.getCurrentPosition(pos => {
                const latInput = document.querySelector('input[name="latitude"]');
                const lngInput = document.querySelector('input[name="longitude"]');
                if (latInput) latInput.value = pos.coords.latitude.toFixed(6);
                if (lngInput) lngInput.value = pos.coords.longitude.toFixed(6);
                btn.textContent = '✓ Location detected';
            }, () => {
                btn.textContent = 'Detection failed';
                btn.disabled = false;
            });
        });
    }

    // ═══════════════════════════════════════════════════════
    // INTEREST TAG PICKER
    // ═══════════════════════════════════════════════════════
    function initInterestPicker() {
        const tags = document.querySelectorAll('.interest-tag');
        if (!tags.length) return;
        const MAX = 10;

        tags.forEach(tag => {
            const cb = tag.querySelector('input[type="checkbox"]');
            tag.addEventListener('click', (e) => {
                // Prevent form submit
                e.preventDefault();

                if (cb.checked) {
                    cb.checked = false;
                    tag.classList.remove('selected');
                } else {
                    const checked = document.querySelectorAll('.interest-tag input:checked').length;
                    if (checked >= MAX) {
                        // Shake the tag
                        tag.style.animation = 'shake 0.3s ease';
                        setTimeout(() => tag.style.animation = '', 300);
                        return;
                    }
                    cb.checked = true;
                    tag.classList.add('selected');
                }
            });
        });
    }

    // ═══════════════════════════════════════════════════════
    // MINI-GAMES — PICKER
    // ═══════════════════════════════════════════════════════
    function initGamePicker() {
        const grid = document.querySelector('.game-picker-grid');
        if (!grid) return;
        const matchId = grid.dataset.matchId;

        grid.querySelectorAll('.game-type-card').forEach(card => {
            card.addEventListener('click', () => {
                if (card.classList.contains('loading')) return;
                card.classList.add('loading');
                card.style.opacity = '0.6';

                ajax('POST', '/game/start', {
                    match_id: parseInt(matchId, 10),
                    game_type: card.dataset.type
                }).then(res => {
                    if (res.game_id) {
                        window.location.href = BASE + '/game/play?game_id=' + res.game_id;
                    } else {
                        card.classList.remove('loading');
                        card.style.opacity = '';
                        if (res.error && res.game_id) {
                            window.location.href = BASE + '/game/play?game_id=' + res.game_id;
                        }
                    }
                }).catch(() => {
                    card.classList.remove('loading');
                    card.style.opacity = '';
                });
            });
        });
    }

    // ═══════════════════════════════════════════════════════
    // MINI-GAMES — PLAY ENGINE
    // ═══════════════════════════════════════════════════════
    function initGamePlay() {
        const el = document.querySelector('.game-play');
        if (!el || !window._gameData) return;

        const gameId     = parseInt(el.dataset.gameId, 10);
        const userId     = parseInt(el.dataset.userId, 10);
        const otherId    = parseInt(el.dataset.otherId, 10);
        const type       = el.dataset.type;
        const totalRounds = parseInt(el.dataset.totalRounds, 10);
        const status     = el.dataset.status;

        const questions  = window._gameData.questions;
        const answersMap = window._gameData.answers;   // {round: {userId: answer}}
        const otherName  = window._gameData.otherName;

        const arena   = document.getElementById('gameArena');
        const waiting = document.getElementById('gameWaiting');
        const progBar = document.getElementById('progressBar');
        const roundInd = document.getElementById('roundIndicator');

        let currentRound = parseInt(el.dataset.currentRound, 10);
        let hasAnswered  = el.dataset.hasAnswered === '1';
        let pollTimer    = null;

        // ── If game is finished, show results ──
        if (status === 'finished') {
            showResults();
            return;
        }

        // ── If already answered this round, resume waiting ──
        if (hasAnswered) {
            renderQuestionLocked();
            startPolling();
            return;
        }

        // ── Render current question ──
        renderQuestion();

        function updateProgress(round) {
            const pct = ((round - 1) / totalRounds) * 100;
            progBar.style.width = pct + '%';
            roundInd.textContent = round;
        }

        function renderQuestion() {
            updateProgress(currentRound);
            const q = questions[currentRound - 1];
            if (!q) { showResults(); return; }

            if (type === 'trivia') {
                renderTrivia(q);
            } else {
                renderChoice(q);
            }
        }

        function renderChoice(q) {
            const isWYR = type === 'would_you_rather';
            const prefix = isWYR ? 'Would you rather...' : 'Which do you prefer?';
            arena.innerHTML =
                '<div class="game-question">' + escapeHtml(prefix) + '</div>' +
                '<div class="game-choices">' +
                    '<button class="game-choice-btn" data-answer="A">' +
                        '<span class="choice-label">A</span>' +
                        '<span>' + escapeHtml(q.A) + '</span>' +
                    '</button>' +
                    '<button class="game-choice-btn" data-answer="B">' +
                        '<span class="choice-label">B</span>' +
                        '<span>' + escapeHtml(q.B) + '</span>' +
                    '</button>' +
                '</div>';

            arena.querySelectorAll('.game-choice-btn').forEach(btn => {
                btn.addEventListener('click', () => submitAnswer(btn.dataset.answer));
            });
            waiting.style.display = 'none';
        }

        function renderTrivia(q) {
            let optionsHtml = '';
            q.options.forEach(opt => {
                optionsHtml += '<button class="game-trivia-btn" data-answer="' + escapeHtml(opt) + '">' + escapeHtml(opt) + '</button>';
            });
            arena.innerHTML =
                '<div class="game-question">' + escapeHtml(q.q) + '</div>' +
                '<div class="game-trivia-options">' + optionsHtml + '</div>';

            arena.querySelectorAll('.game-trivia-btn').forEach(btn => {
                btn.addEventListener('click', () => submitAnswer(btn.dataset.answer));
            });
            waiting.style.display = 'none';
        }

        function renderQuestionLocked() {
            const q = questions[currentRound - 1];
            if (!q) return;
            updateProgress(currentRound);

            if (type === 'trivia') {
                renderTrivia(q);
            } else {
                renderChoice(q);
            }

            // Disable buttons and show selected
            arena.querySelectorAll('.game-choice-btn, .game-trivia-btn').forEach(btn => {
                btn.disabled = true;
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.6';
            });
            waiting.style.display = '';
        }

        function submitAnswer(answer) {
            // Disable all buttons immediately
            arena.querySelectorAll('.game-choice-btn, .game-trivia-btn').forEach(btn => {
                btn.disabled = true;
                btn.style.pointerEvents = 'none';
            });

            // Highlight selected
            arena.querySelectorAll('[data-answer="' + CSS.escape(answer) + '"]').forEach(btn => {
                btn.classList.add('selected');
            });

            hasAnswered = true;
            waiting.style.display = '';

            ajax('POST', '/game/answer', {
                game_id: gameId,
                round: currentRound,
                answer: answer
            }).then(res => {
                if (res.reveal) {
                    stopPolling();
                    showReveal(res.reveal, res.status === 'finished');
                } else if (res.status === 'waiting') {
                    startPolling();
                }
            });
        }

        function startPolling() {
            stopPolling();
            pollTimer = setInterval(() => {
                fetch(BASE + '/game/poll?game_id=' + gameId + '&round=' + currentRound, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.both_answered && res.reveal) {
                        stopPolling();
                        showReveal(res.reveal, res.game_status === 'finished');
                    }
                });
            }, 2000);
        }

        function stopPolling() {
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        }

        function showReveal(reveal, isLast) {
            waiting.style.display = 'none';
            const q = questions[currentRound - 1];
            const myAns = reveal[userId] || '?';
            const theirAns = reveal[otherId] || '?';
            const same = myAns === theirAns;

            // Store for results
            if (!answersMap[currentRound]) answersMap[currentRound] = {};
            answersMap[currentRound][userId] = myAns;
            answersMap[currentRound][otherId] = theirAns;

            // For trivia, show correct/wrong on the current buttons
            let triviaExtra = '';
            if (type === 'trivia' && q) {
                const correct = q.a;
                const youRight = myAns === correct;
                const theyRight = theirAns === correct;
                triviaExtra =
                    '<div style="font-size:0.82rem; color:var(--gray); margin-top:0.5rem;">' +
                    'Correct: <strong>' + escapeHtml(correct) + '</strong></div>';
            }

            let matchIcon = same ? '🎉' : '😅';
            let matchText = same ? 'You both said the same!' : 'Different picks!';
            if (type === 'trivia') {
                const myRight = myAns === q.a;
                const theirRight = theirAns === q.a;
                if (myRight && theirRight) { matchIcon = '🎉'; matchText = 'Both correct!'; }
                else if (myRight) { matchIcon = '💪'; matchText = 'You got it right!'; }
                else if (theirRight) { matchIcon = '😅'; matchText = otherName + ' got it right!'; }
                else { matchIcon = '🤷'; matchText = 'Nobody got it!'; }
            }

            arena.innerHTML =
                '<div class="game-reveal">' +
                    '<div class="game-reveal-match">' + matchIcon + '</div>' +
                    '<div class="game-reveal-text">' + matchText + '</div>' +
                    '<div class="game-reveal-answers">' +
                        '<div class="game-reveal-player">' +
                            '<div class="name">You</div>' +
                            '<div class="ans' + (same ? ' same' : '') + '">' + escapeHtml(myAns) + '</div>' +
                        '</div>' +
                        '<div class="game-reveal-player">' +
                            '<div class="name">' + escapeHtml(otherName) + '</div>' +
                            '<div class="ans' + (same ? ' same' : '') + '">' + escapeHtml(theirAns) + '</div>' +
                        '</div>' +
                    '</div>' +
                    triviaExtra +
                    (isLast
                        ? '<button class="game-next-btn" id="showResultsBtn">See Results</button>'
                        : '<button class="game-next-btn" id="nextRoundBtn">Next Round →</button>') +
                '</div>';

            // Update progress
            updateProgress(currentRound + (isLast ? 1 : 0));

            if (isLast) {
                progBar.style.width = '100%';
                document.getElementById('showResultsBtn').addEventListener('click', showResults);
            } else {
                document.getElementById('nextRoundBtn').addEventListener('click', () => {
                    currentRound++;
                    hasAnswered = false;
                    renderQuestion();
                });
            }
        }

        function showResults() {
            stopPolling();
            waiting.style.display = 'none';
            progBar.style.width = '100%';
            roundInd.textContent = totalRounds;

            let sameCount = 0;
            let roundsHtml = '';

            for (let r = 1; r <= totalRounds; r++) {
                const q = questions[r - 1];
                const rData = answersMap[r] || answersMap[String(r)] || {};
                const myAns = rData[userId] || rData[String(userId)] || '—';
                const theirAns = rData[otherId] || rData[String(otherId)] || '—';

                let isSame;
                if (type === 'trivia') {
                    isSame = q && myAns === q.a && theirAns === q.a;
                } else {
                    isSame = myAns === theirAns;
                }
                if (isSame) sameCount++;

                roundsHtml +=
                    '<div class="game-results-round">' +
                        '<span>Round ' + r + '</span>' +
                        '<span>' + escapeHtml(myAns) + ' / ' + escapeHtml(theirAns) + '</span>' +
                        '<span class="game-results-badge ' + (isSame ? 'same' : 'diff') + '">' +
                            (isSame ? (type === 'trivia' ? '✓ Both' : '✓ Match') : (type === 'trivia' ? '✗' : '✗ Diff')) +
                        '</span>' +
                    '</div>';
            }

            const pct = Math.round((sameCount / totalRounds) * 100);
            const icon = pct >= 60 ? '🎉' : pct >= 40 ? '😊' : '🤷';
            const label = type === 'trivia' ? 'Rounds Both Correct' : 'Compatibility';
            const matchId = parseInt(el.dataset.matchId, 10);

            arena.innerHTML =
                '<div class="game-results">' +
                    '<div class="game-results-icon">' + icon + '</div>' +
                    '<h2>Game Over!</h2>' +
                    '<div class="game-results-score">' + sameCount + ' / ' + totalRounds + '</div>' +
                    '<div class="game-results-label">' + label + '</div>' +
                    roundsHtml +
                    '<div class="game-results-actions">' +
                        '<a href="' + BASE + '/game?match_id=' + matchId + '" class="btn btn-primary">Play Again</a>' +
                        '<a href="' + BASE + '/chat?match_id=' + matchId + '" class="btn btn-outline">Back to Chat</a>' +
                    '</div>' +
                '</div>';
        }
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        let h = d.getHours(), m = d.getMinutes();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + (m < 10 ? '0' : '') + m + ' ' + ampm;
    }

    // ═══════════════════════════════════════════════════════
    // ANTI-GHOSTING / POLITE PASS
    // ═══════════════════════════════════════════════════════
    function initPolitePass() {
        const dismissBtn = document.getElementById('ghostDismiss');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                dismissBtn.closest('.chat-ghost-nudge').remove();
            });
        }

        const passBtn = document.getElementById('politePassBtn');
        const modal = document.getElementById('politePassModal');
        const overlay = document.getElementById('politePassOverlay');
        const cancel = document.getElementById('politePassCancel');
        if (!passBtn || !modal) return;

        passBtn.addEventListener('click', () => { modal.style.display = 'flex'; });
        if (overlay) overlay.addEventListener('click', () => { modal.style.display = 'none'; });
        if (cancel)  cancel.addEventListener('click',  () => { modal.style.display = 'none'; });

        document.querySelectorAll('.polite-pass-option').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = btn.dataset.index;
                const matchId = passBtn.dataset.matchId;
                fetch('/dateapp/chat/polite-pass', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ match_id: matchId, message_index: idx })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            modal.style.display = 'none';
                            const nudge = document.getElementById('ghostNudge');
                            if (nudge) nudge.remove();
                            // Append the sent message to the chat
                            const container = document.getElementById('chatMessages');
                            if (container && data.text) {
                                const div = document.createElement('div');
                                div.className = 'chat-bubble chat-bubble-mine';
                                div.innerHTML = '<p>' + escapeHtml(data.text) + '</p>';
                                container.appendChild(div);
                                container.scrollTop = container.scrollHeight;
                            }
                        }
                    });
            });
        });
    }

    // ═══════════════════════════════════════════════════════
    // DATE IDEAS PANEL
    // ═══════════════════════════════════════════════════════
    function initDateIdeas() {
        const toggle = document.getElementById('dateIdeasToggle');
        const panel = document.getElementById('dateIdeasPanel');
        const close = document.getElementById('dateIdeasClose');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', () => {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });
        if (close) close.addEventListener('click', () => { panel.style.display = 'none'; });
    }

    // ═══════════════════════════════════════════════════════
    // AVAILABILITY CALENDAR (Settings)
    // ═══════════════════════════════════════════════════════
    function initAvailability() {
        const calendar = document.getElementById('availCalendar');
        const addForm = document.getElementById('availAddForm');
        const saveBtn = document.getElementById('availSaveBtn');
        if (!calendar) return;

        const dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        let activeDay = null;

        // Show add-slot form for a specific day
        calendar.querySelectorAll('.avail-add-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                activeDay = parseInt(btn.dataset.day);
                const label = document.getElementById('availAddDay');
                const dayVal = document.getElementById('availAddDayVal');
                if (label) label.textContent = dayNames[activeDay] || '';
                if (dayVal) dayVal.value = activeDay;
                if (addForm) addForm.style.display = 'block';
            });
        });

        // Cancel add
        const cancelBtn = document.getElementById('availAddCancel');
        if (cancelBtn) cancelBtn.addEventListener('click', () => {
            if (addForm) addForm.style.display = 'none';
            activeDay = null;
        });

        // Confirm add — inserts a tag into the day row
        const confirmBtn = document.getElementById('availAddConfirm');
        if (confirmBtn) confirmBtn.addEventListener('click', () => {
            if (activeDay === null) return;
            const startSel = document.getElementById('availStartTime');
            const endSel = document.getElementById('availEndTime');
            if (!startSel || !endSel) return;
            const start = startSel.value;
            const end = endSel.value;
            if (start >= end) { alert('Start time must be before end time.'); return; }

            const row = calendar.querySelector('.avail-day-row[data-day="' + activeDay + '"]');
            if (!row) return;
            const slotsContainer = row.querySelector('.avail-day-slots');

            // Remove "No times set" placeholder
            const empty = slotsContainer.querySelector('.avail-empty');
            if (empty) empty.remove();

            const formatTime = (t) => {
                const [hh, mm] = t.split(':');
                let h = parseInt(hh), suffix = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return h + ':' + mm + suffix;
            };

            const tag = document.createElement('span');
            tag.className = 'avail-slot-tag';
            tag.innerHTML = formatTime(start) + '–' + formatTime(end) +
                ' <button class="avail-slot-remove" data-day="' + activeDay + '" data-start="' + start + ':00" data-end="' + end + ':00">&times;</button>';
            slotsContainer.appendChild(tag);

            if (addForm) addForm.style.display = 'none';
            activeDay = null;
        });

        // Remove slot tag
        calendar.addEventListener('click', (e) => {
            if (e.target.classList.contains('avail-slot-remove')) {
                const tag = e.target.closest('.avail-slot-tag');
                const row = e.target.closest('.avail-day-row');
                if (tag) tag.remove();
                // If no more tags, show placeholder
                if (row) {
                    const slots = row.querySelector('.avail-day-slots');
                    if (slots && !slots.querySelector('.avail-slot-tag')) {
                        const empty = document.createElement('span');
                        empty.className = 'avail-empty';
                        empty.textContent = 'No times set';
                        slots.appendChild(empty);
                    }
                }
            }
        });

        // Save all slots via AJAX
        if (saveBtn) saveBtn.addEventListener('click', () => {
            const slots = [];
            calendar.querySelectorAll('.avail-day-row').forEach(row => {
                const day = parseInt(row.dataset.day);
                row.querySelectorAll('.avail-slot-remove').forEach(rm => {
                    slots.push({
                        day_of_week: day,
                        start_time: rm.dataset.start,
                        end_time: rm.dataset.end
                    });
                });
            });

                fetch('/dateapp/settings/availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ slots: slots })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        saveBtn.textContent = '✓ Saved!';
                        setTimeout(() => { saveBtn.textContent = 'Save Availability'; }, 2000);
                    } else {
                        alert(data.error || 'Failed to save.');
                    }
                });
        });
    }


/* ═══════════════════════════════════════════════════════════
   ✨ VISUAL ENHANCEMENT – Micro-interactions & Animations
   ═══════════════════════════════════════════════════════════ */

/* ─── Scroll-Reveal (Intersection Observer) ─────────────── */
(function initReveal() {
    const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
    if (!revealEls.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(el => obs.observe(el));
})();

/* ─── Auto-add reveal classes to key sections ───────────── */
(function autoReveal() {
    const selectors = [
        '.dash-section',
        '.dash-cta',
        '.dash-stats',
        '.how-step',
        '.feature-card',
        '.testimonial-card',
        '.landing-stats',
        '.landing-how',
        '.profile-section',
        '.settings-card',
        '.game-option',
        '.premium-feature-card',
        '.admin-card',
        '.admin-stat-card'
    ];
    selectors.forEach(sel => {
        document.querySelectorAll(sel).forEach((el, i) => {
            if (!el.classList.contains('reveal') &&
                !el.classList.contains('reveal-left') &&
                !el.classList.contains('reveal-right')) {
                el.classList.add('reveal');
                el.style.transitionDelay = (i * 0.07) + 's';
            }
        });
    });
    // Re-observe newly added elements
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal:not(.visible)').forEach(el => obs.observe(el));
})();

/* ─── Button Ripple Effect ──────────────────────────────── */
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn');
    if (!btn) return;
    const ripple = document.createElement('span');
    ripple.classList.add('btn-ripple');
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
    btn.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove());
});

/* ─── Animated Counter for Stats ────────────────────────── */
(function animateCounters() {
    const counters = document.querySelectorAll('.dash-stat-value, .stat-number');
    if (!counters.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(ent => {
            if (!ent.isIntersecting) return;
            const el = ent.target;
            const text = el.textContent.trim();
            const match = text.match(/^([\d,.]+)(\D*)$/);
            if (!match) return;
            const target = parseFloat(match[1].replace(/,/g, ''));
            const suffix = match[2] || '';
            const isFloat = match[1].includes('.');
            const duration = 1200;
            const start = performance.now();
            function tick(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = target * eased;
                el.textContent = (isFloat ? current.toFixed(1) : Math.round(current).toLocaleString()) + suffix;
                if (progress < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
            obs.unobserve(el);
        });
    }, { threshold: 0.5 });
    counters.forEach(c => obs.observe(c));
})();

/* ─── Confetti Effect on Match ──────────────────────────── */
function launchConfetti() {
    const colors = ['#e63946', '#ff6b6b', '#ffd700', '#00d4ff', '#ff8e53', '#2ee59d', '#764ba2'];
    const container = document.body;
    for (let i = 0; i < 60; i++) {
        const piece = document.createElement('div');
        piece.classList.add('confetti-piece');
        piece.style.left = Math.random() * 100 + 'vw';
        piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        piece.style.animationDuration = (2 + Math.random() * 2) + 's';
        piece.style.animationDelay = (Math.random() * 0.5) + 's';
        const shapes = ['circle', ''];
        if (Math.random() > 0.5) {
            piece.style.borderRadius = '50%';
        } else {
            piece.style.width = (6 + Math.random() * 8) + 'px';
            piece.style.height = (6 + Math.random() * 8) + 'px';
        }
        container.appendChild(piece);
        piece.addEventListener('animationend', () => piece.remove());
    }
}

// Hook confetti to match modal appearance
(function watchMatchModal() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            m.addedNodes.forEach(node => {
                if (node.nodeType === 1 && (node.classList?.contains('match-modal') || node.querySelector?.('.match-modal'))) {
                    launchConfetti();
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();

/* ─── Page Enter Animation ──────────────────────────────── */
(function pageEnter() {
    const main = document.querySelector('main') || document.querySelector('.container');
    if (main) main.classList.add('page-enter');
})();

/* ─── Smooth Scroll for Anchor Links ────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

/* ─── Active Nav Highlight ──────────────────────────────── */
(function highlightActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.main-nav a, .bottom-nav a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && path.includes(href.replace('/dateapp', ''))) {
            link.classList.add('active');
        }
    });
})();

/* ─── Parallax-lite on Hero Photos ──────────────────────── */
(function heroParallax() {
    const hero = document.querySelector('.landing-hero');
    if (!hero) return;
    const photos = hero.querySelectorAll('.hero-photo');
    if (!photos.length) return;
    window.addEventListener('scroll', function() {
        const scrollY = window.scrollY;
        if (scrollY > window.innerHeight) return;
        photos.forEach((photo, i) => {
            const speed = 0.03 + (i * 0.015);
            photo.style.transform = 'translateY(' + (scrollY * speed) + 'px)';
        });
    }, { passive: true });
})();

/* ─── Tilt Effect on Swipe Cards (desktop) ──────────────── */
(function cardTilt() {
    if (window.innerWidth < 768) return;
    const stack = document.querySelector('.swipe-stack');
    if (!stack) return;
    stack.addEventListener('mousemove', function(e) {
        const card = stack.querySelector('.swipe-card:last-child');
        if (!card || card.dataset.dragging) return;
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * -5;
        const rotateY = ((x - centerX) / centerX) * 5;
        card.style.transform = 'perspective(800px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) scale(1.02)';
    });
    stack.addEventListener('mouseleave', function() {
        const card = stack.querySelector('.swipe-card:last-child');
        if (card) card.style.transform = '';
    });
})();

    // ═══════════════════════════════════════════════════════
    // REWIND (UNDO LAST SWIPE)
    // ═══════════════════════════════════════════════════════
    function initRewind() {
        const btn = document.querySelector('.swipe-btn-rewind');
        if (!btn) return;

        btn.addEventListener('click', () => {
            if (btn.classList.contains('swipe-btn--locked')) {
                window.location.href = BASE + '/premium';
                return;
            }
            btn.disabled = true;
            ajax('POST', '/rewind', {})
                .then(data => {
                    btn.disabled = false;
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    if (data.success && data.profile) {
                        const stack = document.querySelector('.swipe-stack');
                        if (!stack) return;
                        // Remove empty state if present
                        const empty = stack.querySelector('.empty-state');
                        if (empty) empty.remove();

                        const p = data.profile;
                        const photoHtml = p.photo
                            ? `<img src="${BASE}/public/${p.photo}" alt="${p.name}" class="swipe-card-photo">`
                            : `<div class="swipe-card-photo swipe-card-photo-placeholder">${(p.name || '?')[0].toUpperCase()}</div>`;
                        const card = document.createElement('div');
                        card.className = 'swipe-card';
                        card.dataset.userId = p.id;
                        card.innerHTML =
                            `<div class="swipe-stamp swipe-stamp-like">LIKE</div>` +
                            `<div class="swipe-stamp swipe-stamp-nope">NOPE</div>` +
                            photoHtml +
                            `<div class="swipe-card-info"><h3>${p.name}${p.age ? ', ' + p.age : ''}</h3>` +
                            (p.city ? `<p class="swipe-card-city">📍 ${p.city}</p>` : '') +
                            `</div>`;
                        stack.insertBefore(card, stack.firstChild);
                    }
                })
                .catch(() => { btn.disabled = false; });
        });
    }

    // ═══════════════════════════════════════════════════════
    // PROFILE BOOST
    // ═══════════════════════════════════════════════════════
    function initBoost() {
        // ─── Discover page boost ───────────────────────
        const boostBtn = document.getElementById('boostBtn');
        if (boostBtn) {
            boostBtn.addEventListener('click', () => activateBoost(boostBtn));
        }
        // ─── Dashboard boost button ────────────────────
        const dashBoostBtn = document.getElementById('dashBoostBtn');
        if (dashBoostBtn) {
            dashBoostBtn.addEventListener('click', () => activateBoost(dashBoostBtn));
        }
        // ─── Start countdown if timer already on page ──
        const timer = document.getElementById('boostTimer');
        if (timer) {
            const remaining = parseInt(timer.dataset.remaining, 10);
            if (remaining > 0) startBoostCountdown(timer, remaining);
        }
    }

    function activateBoost(btn) {
        btn.disabled = true;
        ajax('POST', '/boost', {})
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    btn.disabled = false;
                    return;
                }
                if (data.success) {
                    // Replace button with active timer
                    const bar = btn.closest('.boost-bar') || btn.closest('.dash-action-card');
                    if (bar && bar.classList.contains('boost-bar')) {
                        bar.innerHTML =
                            '<span class="boost-icon">🚀</span>' +
                            '<span class="boost-text boost-active">Boost active! <span class="boost-timer" id="boostTimer"></span> remaining</span>';
                        const timer = document.getElementById('boostTimer');
                        startBoostCountdown(timer, data.remaining);
                    } else {
                        alert('Boost activated for 30 minutes!');
                    }
                }
            })
            .catch(() => { btn.disabled = false; });
    }

    function startBoostCountdown(el, seconds) {
        let remaining = seconds;
        function tick() {
            if (remaining <= 0) {
                el.textContent = '0:00';
                location.reload();
                return;
            }
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            remaining--;
            setTimeout(tick, 1000);
        }
        tick();
    }

})();

/* ═══════════════════════════════════════════════════════════
   🎙️  Voice Notes — Recording & Playback
   ═══════════════════════════════════════════════════════════ */

(function voiceNotes() {
    const recordBtn = document.getElementById('voiceRecordBtn');
    if (!recordBtn) return;

    const recordingBar = document.getElementById('voiceRecordingBar');
    const cancelBtn    = document.getElementById('voiceCancelBtn');
    const sendBtn      = document.getElementById('voiceSendBtn');
    const timerEl      = document.getElementById('voiceTimer');
    const chatForm     = document.getElementById('chatForm');
    const chatMessages = document.getElementById('chatMessages');
    const matchId      = chatMessages?.dataset.matchId;
    const BASE         = document.querySelector('meta[name="base-url"]')?.content || '/dateapp';

    let mediaRecorder = null;
    let audioChunks   = [];
    let timerInterval  = null;
    let seconds        = 0;

    function csrfToken() {
        return document.querySelector('input[name="csrf_token"]')?.value || '';
    }

    function updateTimer() {
        seconds++;
        const m = Math.floor(seconds / 60);
        const s = String(seconds % 60).padStart(2, '0');
        timerEl.textContent = m + ':' + s;
    }

    recordBtn.addEventListener('click', async function() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioChunks = [];
            seconds = 0;
            timerEl.textContent = '0:00';

            mediaRecorder = new MediaRecorder(stream, { mimeType: getSupportedMime() });

            mediaRecorder.addEventListener('dataavailable', function(e) {
                if (e.data.size > 0) audioChunks.push(e.data);
            });

            mediaRecorder.addEventListener('stop', function() {
                stream.getTracks().forEach(t => t.stop());
            });

            mediaRecorder.start();
            timerInterval = setInterval(updateTimer, 1000);

            chatForm.style.display = 'none';
            recordingBar.style.display = 'flex';
        } catch (err) {
            alert('Microphone access is required. Please allow it and try again.');
        }
    });

    cancelBtn.addEventListener('click', function() {
        stopRecording(false);
    });

    sendBtn.addEventListener('click', function() {
        stopRecording(true);
    });

    function stopRecording(shouldSend) {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') return;
        clearInterval(timerInterval);

        if (shouldSend) {
            mediaRecorder.addEventListener('stop', function onStop() {
                mediaRecorder.removeEventListener('stop', onStop);
                const blob = new Blob(audioChunks, { type: mediaRecorder.mimeType });
                if (blob.size > 0 && blob.size <= 2 * 1024 * 1024) {
                    sendVoiceNote(blob, seconds);
                }
            });
        }

        mediaRecorder.stop();
        recordingBar.style.display = 'none';
        chatForm.style.display = '';
    }

    function sendVoiceNote(blob, duration) {
        const fd = new FormData();
        const ext = blob.type.includes('webm') ? 'webm' : (blob.type.includes('ogg') ? 'ogg' : 'mp4');
        fd.append('voice', blob, 'voice.' + ext);
        fd.append('match_id', matchId);
        fd.append('duration', duration);
        fd.append('csrf_token', csrfToken());

        fetch(BASE + '/chat/send-voice', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.message) {
                // appendMessage already handles voice type; trigger it through DOM
                const evt = new CustomEvent('voice-sent', { detail: data.message });
                chatMessages.dispatchEvent(evt);
            }
        })
        .catch(() => {});
    }

    // Listen for voice-sent event so initChat's appendMessage can handle it
    chatMessages.addEventListener('voice-sent', function(e) {
        // The initChat closure will handle this through polling if needed,
        // but for instant display, we build the bubble directly
        const msg = e.detail;
        if (chatMessages.querySelector('[data-msg-id="' + msg.id + '"]')) return;
        const userId = chatMessages.dataset.userId || '0';
        const isMine = String(msg.sender_id) === String(userId);
        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (isMine ? 'chat-bubble-mine' : 'chat-bubble-theirs');
        div.dataset.msgId = msg.id;
        const dur = parseInt(msg.voice_duration || 0, 10);
        const mins = Math.floor(dur / 60);
        const secs = String(dur % 60).padStart(2, '0');
        let bars = '';
        for (let b = 0; b < 20; b++) bars += '<span class="voice-bar" style="height:' + (20 + Math.random() * 80) + '%"></span>';
        div.innerHTML = '<div class="voice-msg">' +
            '<button class="voice-play-btn" onclick="playVoice(this)" data-src="' + BASE + '/public/' + msg.voice_path + '">' +
            '<svg class="voice-icon-play" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>' +
            '<svg class="voice-icon-pause" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>' +
            '</button>' +
            '<div class="voice-waveform"><div class="voice-progress" style="width:0%"></div>' + bars + '</div>' +
            '<span class="voice-duration">' + mins + ':' + secs + '</span>' +
            '</div>' +
            '<span class="chat-time">Just now</span>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        // Hide icebreakers
        const ice = document.getElementById('icebreakers');
        if (ice) ice.remove();
        const empty = chatMessages.querySelector('.chat-empty');
        if (empty) empty.remove();
    });

    function getSupportedMime() {
        const types = ['audio/webm', 'audio/ogg', 'audio/mp4', 'video/webm'];
        for (const t of types) {
            if (MediaRecorder.isTypeSupported(t)) return t;
        }
        return '';
    }
})();

/* ═══════════════════════════════════════════════════════════
   🎙️  Voice Playback
   ═══════════════════════════════════════════════════════════ */

(function() {
    let currentAudio = null;
    let currentBtn   = null;

    window.playVoice = function(btn) {
        const src = btn.dataset.src;
        if (!src) return;

        const playIcon  = btn.querySelector('.voice-icon-play');
        const pauseIcon = btn.querySelector('.voice-icon-pause');
        const waveform  = btn.closest('.voice-msg').querySelector('.voice-waveform');
        const bars      = waveform.querySelectorAll('.voice-bar');
        const durLabel  = btn.closest('.voice-msg').querySelector('.voice-duration');

        // If clicking the currently playing button, toggle pause
        if (currentBtn === btn && currentAudio && !currentAudio.paused) {
            currentAudio.pause();
            playIcon.style.display = '';
            pauseIcon.style.display = 'none';
            return;
        }

        // Stop any other playing audio
        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
            if (currentBtn) {
                currentBtn.querySelector('.voice-icon-play').style.display = '';
                currentBtn.querySelector('.voice-icon-pause').style.display = 'none';
                resetBars(currentBtn);
            }
        }

        const audio = new Audio(src);
        currentAudio = audio;
        currentBtn = btn;

        playIcon.style.display = 'none';
        pauseIcon.style.display = '';

        audio.addEventListener('timeupdate', function() {
            if (!audio.duration) return;
            const pct = audio.currentTime / audio.duration;
            const playedCount = Math.floor(pct * bars.length);
            bars.forEach((bar, i) => {
                bar.classList.toggle('played', i < playedCount);
            });
            const rem = Math.ceil(audio.duration - audio.currentTime);
            const m = Math.floor(rem / 60);
            const s = String(rem % 60).padStart(2, '0');
            durLabel.textContent = m + ':' + s;
        });

        audio.addEventListener('ended', function() {
            playIcon.style.display = '';
            pauseIcon.style.display = 'none';
            bars.forEach(b => b.classList.remove('played'));
            currentAudio = null;
            currentBtn = null;
        });

        audio.play().catch(() => {});
    };

    function resetBars(btn) {
        const bars = btn.closest('.voice-msg')?.querySelectorAll('.voice-bar');
        if (bars) bars.forEach(b => b.classList.remove('played'));
    }
})();

/* ═══════════════════════════════════════════════════════════
   🧊  Icebreaker Interaction
   ═══════════════════════════════════════════════════════════ */

window.useIcebreaker = function(btn) {
    const input = document.getElementById('chatInput');
    if (!input) return;
    // Get text without emoji
    const spans = btn.querySelectorAll('span');
    const text = spans.length > 1 ? spans[1].textContent : btn.textContent;
    input.value = text.trim();
    input.focus();
    // Animate selected pill
    btn.style.background = 'var(--primary)';
    btn.style.color = '#fff';
    btn.style.borderColor = 'var(--primary)';
};
