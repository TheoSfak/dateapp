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
    });

    // ═══════════════════════════════════════════════════════
    // SWIPE ENGINE
    // ═══════════════════════════════════════════════════════
    function initSwipe() {
        const stack = document.querySelector('.swipe-stack');
        if (!stack) return;

        let startX = 0, startY = 0, currentX = 0, isDragging = false;
        const THRESHOLD = 100;

        function getTopCard() { return stack.querySelector('.swipe-card:first-child'); }

        function bindCard(card) {
            if (!card) return;
            card.addEventListener('pointerdown', onStart);
            card.addEventListener('pointermove', onMove);
            card.addEventListener('pointerup', onEnd);
            card.addEventListener('pointercancel', onEnd);
        }

        function onStart(e) {
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
                    sendSwipe(card.dataset.userId, direction);
                    const next = getTopCard();
                    if (next) {
                        next.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                        next.style.transform = '';
                        next.style.opacity = '1';
                    }
                    bindCard(getTopCard());
                    updateCounter(-1);
                    if (!getTopCard()) showEmpty();
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

        function triggerSwipe(direction) {
            const card = getTopCard();
            if (!card) return;
            const flyX = direction === 'pass' ? -1000 : 1000;
            card.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
            card.style.transform = `translateX(${flyX}px) rotate(${flyX * 0.04}deg)`;
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                sendSwipe(card.dataset.userId, direction);
                bindCard(getTopCard());
                updateCounter(-1);
                if (!getTopCard()) showEmpty();
            }, 350);
        }

        function sendSwipe(userId, type) {
            ajax('POST', '/swipe', { target_id: userId, type: type })
                .then(data => {
                    if (data.match) showMatchModal(data);
                })
                .catch(() => {});
        }

        function updateCounter(delta) {
            const el = document.querySelector('.swipe-counter');
            if (!el) return;
            const m = el.textContent.match(/(\d+)/);
            if (m) {
                const n = Math.max(0, parseInt(m[1]) + delta);
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
            const userId = document.getElementById('chatMessages')?.dataset.userId || '0';
            const isMine = String(msg.sender_id) === String(userId);
            const div = document.createElement('div');
            div.className = 'chat-bubble ' + (isMine ? 'chat-bubble-mine' : 'chat-bubble-theirs');
            div.dataset.msgId = msg.id;
            const text = msg.body || msg.message_text || '';
            div.innerHTML = '<p>' + escapeHtml(text) + '</p><span class="chat-time">' + formatTime(msg.created_at || msg.sent_at) + '</span>';
            messages.appendChild(div);
        }
    }

    window.sendMessage = function(e) {
        e.preventDefault();
        const form = document.getElementById('chatForm');
        const input = document.getElementById('chatInput');
        const messages = document.getElementById('chatMessages');
        const matchId = messages?.dataset.matchId;
        if (!matchId || !input.value.trim()) return false;

        const body = input.value.trim();
        input.value = '';

        ajax('POST', '/chat/send', { match_id: matchId, body: body })
            .then(data => {
                if (data.success && data.message) {
                    const div = document.createElement('div');
                    div.className = 'chat-bubble chat-bubble-mine';
                    div.dataset.msgId = data.message.id;
                    const text = data.message.body || data.message.message_text || '';
                    div.innerHTML = '<p>' + escapeHtml(text) + '</p><span class="chat-time">' + formatTime(data.message.created_at || data.message.sent_at) + '</span>';
                    messages.appendChild(div);
                    messages.scrollTop = messages.scrollHeight;
                }
            })
            .catch(() => {});
        return false;
    };

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
})();
