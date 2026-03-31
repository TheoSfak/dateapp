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
