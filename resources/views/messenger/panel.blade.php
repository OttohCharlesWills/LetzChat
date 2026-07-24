{{-- ================= MESSENGER FLYOUT PANEL ================= --}}
@auth
    <div class="nb-messenger-panel" id="nbMessengerPanel">

        {{-- ---- View 1: chat list ---- --}}
        <div class="nb-mp-list-view" id="nbMpListView">
            <div class="nb-mp-header">
                <span class="nb-mp-title">{{ __('Chats') }}</span>
            </div>

            <div class="nb-mp-search-wrap">
                <i class="bi bi-search nb-mp-search-icon"></i>
                <input type="text" id="nbMpSearchInput" class="nb-mp-search-input" placeholder="{{ __('Search Messenger') }}">
            </div>

            <div class="nb-mp-list" id="nbMpList">
                @php $friends = Auth::user()->friends(); @endphp

                @forelse ($friends as $friend)
                    <div class="nb-mp-item"
                         data-uuid="{{ $friend->uuid }}"
                         data-name="{{ strtolower($friend->first_name . ' ' . $friend->last_name) }}"
                         data-fullname="{{ $friend->first_name }} {{ $friend->last_name }}"
                         data-username="{{ $friend->username }}"
                         data-avatar="{{ $friend->avatar ? asset('storage/' . $friend->avatar) : '' }}"
                         data-initial="{{ strtoupper(substr($friend->first_name, 0, 1)) }}">

                        @if ($friend->avatar)
                            <img src="{{ asset('storage/' . $friend->avatar) }}" class="nb-mp-avatar">
                        @else
                            <span class="nb-mp-avatar nb-mp-avatar-fallback">
                                {{ strtoupper(substr($friend->first_name, 0, 1)) }}
                            </span>
                        @endif

                        <span class="nb-mp-item-body">
                            <span class="nb-mp-item-name">{{ $friend->first_name }} {{ $friend->last_name }}</span>
                            <span class="nb-mp-item-preview">{{ __('Start a conversation') }}</span>
                        </span>
                    </div>
                @empty
                    <p class="text-muted px-3 small">
                        {{ __("You haven't added any friends yet — add some to start chatting.") }}
                    </p>
                @endforelse
            </div>
        </div>

        {{-- ---- View 2: conversation (hidden until a chat is opened) ---- --}}
        <div class="nb-mp-chat-view d-none" id="nbMpChatView">
            <div class="nb-mp-header nb-mp-chat-header">
                <button type="button" class="nb-mp-back" id="nbMpBack">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <span class="nb-mp-chat-avatar" id="nbMpChatAvatarWrap"></span>
                <span class="nb-mp-chat-name" id="nbMpChatName"></span>
            </div>

            <div class="nb-mp-thread" id="nbMpThread">
                <p class="nb-mp-thread-placeholder">
                    {{ __('This is the beginning of your conversation.') }}
                </p>
            </div>

            <form class="nb-mp-send-form" id="nbMpSendForm">
                <input type="text" class="nb-mp-send-input" id="nbMpSendInput" placeholder="{{ __('Aa') }}" autocomplete="off">
                <button type="button" class="nb-mp-mic-btn" id="nbMpMicBtn" title="{{ __('Record voice note') }}">
                    <i class="bi bi-mic-fill"></i>
                </button>
                <button type="submit" class="nb-mp-send-btn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>

            <div class="nb-mp-recording-bar d-none" id="nbMpRecordingBar">
                <button type="button" id="nbMpCancelRecording" class="nb-mp-rec-btn nb-mp-rec-cancel" title="{{ __('Cancel') }}">
                    <i class="bi bi-trash-fill"></i>
                </button>
                <span class="nb-mp-rec-dot"></span>
                <span class="nb-mp-rec-timer" id="nbMpRecordingTimer">0:00</span>
                <button type="button" id="nbMpStopRecording" class="nb-mp-rec-btn nb-mp-rec-send" title="{{ __('Send') }}">
                    <i class="bi bi-check-circle-fill"></i>
                </button>
            </div>
        </div>

    </div>
@endauth

<style>
    /* ---------- Messenger flyout panel ---------- */
    .nb-messenger-panel {
        display: none;
        position: absolute;
        top: 56px;
        right: 16px;
        width: 340px;
        max-height: 70vh;
        background: var(--nb-bg);
        border: 1px solid var(--nb-border);
        border-radius: 12px;
        box-shadow: 0 8px 28px rgba(0,0,0,0.2);
        z-index: 1050;
        overflow: hidden;
        flex-direction: column;
    }

    .nb-messenger-panel.show {
        display: flex;
    }

    .nb-mp-list-view,
    .nb-mp-chat-view {
        display: flex;
        flex-direction: column;
        height: 70vh;
        max-height: 560px;
    }

    .nb-mp-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px 10px 16px;
        border-bottom: 1px solid var(--nb-border);
    }

    .nb-mp-title {
        font-weight: 700;
        font-size: 1.15rem;
        color: var(--nb-text);
    }

    .nb-mp-search-wrap {
        position: relative;
        margin: 10px 14px;
    }

    .nb-mp-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--nb-text-secondary);
        font-size: 0.85rem;
    }

    .nb-mp-search-input {
        width: 100%;
        background: var(--nb-search-bg);
        border: none;
        border-radius: 20px;
        padding: 8px 12px 8px 32px;
        font-size: 0.88rem;
        color: var(--nb-text);
    }

    .nb-mp-search-input:focus {
        outline: none;
    }

    .nb-mp-list {
        flex: 1;
        overflow-y: auto;
        padding: 0 8px 10px 8px;
    }

    .nb-mp-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
    }

    .nb-mp-item:hover {
        background: var(--nb-hover);
    }

    .nb-mp-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .nb-mp-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--nb-avatar-fallback-bg);
        color: var(--nb-avatar-fallback-text);
        font-weight: 700;
    }

    .nb-mp-item-body {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .nb-mp-item-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--nb-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nb-mp-item-preview {
        font-size: 0.78rem;
        color: var(--nb-text-secondary);
    }

    /* ---- Conversation view ---- */
    .nb-mp-chat-header {
        gap: 8px;
    }

    .nb-mp-back {
        background: none;
        border: none;
        font-size: 1.1rem;
        color: var(--nb-text);
        padding: 4px;
        display: flex;
    }

    .nb-mp-chat-avatar img,
    .nb-mp-chat-avatar .nb-mp-avatar-fallback {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .nb-mp-chat-name {
        font-weight: 700;
        color: var(--nb-text);
        font-size: 0.95rem;
    }

    .nb-mp-thread {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nb-mp-thread-placeholder {
        color: var(--nb-text-secondary);
        font-size: 0.85rem;
        text-align: center;
        margin: 0;
    }

    .nb-mp-thread.has-messages {
        align-items: stretch;
        justify-content: flex-start;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .nb-mp-bubble-row {
        display: flex;
    }

    .nb-mp-bubble-row.mine {
        justify-content: flex-end;
    }

    .nb-mp-bubble-row.theirs {
        justify-content: flex-start;
    }

    .nb-mp-bubble {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 16px;
        font-size: 0.88rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .nb-mp-bubble-row.mine .nb-mp-bubble {
        background: var(--nb-accent);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .nb-mp-bubble-row.theirs .nb-mp-bubble {
        background: var(--nb-hover);
        color: var(--nb-text);
        border-bottom-left-radius: 4px;
    }

    .nb-mp-bubble.deleted {
        font-style: italic;
        opacity: 0.7;
    }

    .nb-mp-send-form {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-top: 1px solid var(--nb-border);
    }

    .nb-mp-send-input {
        flex: 1;
        background: var(--nb-search-bg);
        border: none;
        border-radius: 20px;
        padding: 8px 14px;
        font-size: 0.9rem;
        color: var(--nb-text);
    }

    .nb-mp-send-btn {
        background: none;
        border: none;
        color: var(--nb-accent);
        font-size: 1.2rem;
        display: flex;
    }

    .nb-mp-mic-btn {
        background: none;
        border: none;
        color: var(--nb-text-secondary);
        font-size: 1.15rem;
        display: flex;
        flex-shrink: 0;
    }

    .nb-mp-mic-btn:hover {
        color: var(--nb-accent);
    }

    .nb-mp-recording-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-top: 1px solid var(--nb-border);
    }

    .nb-mp-rec-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #dc3545;
        animation: nb-mp-pulse 1.2s infinite ease-in-out;
        flex-shrink: 0;
    }

    @keyframes nb-mp-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .nb-mp-rec-timer {
        flex: 1;
        font-size: 0.88rem;
        color: var(--nb-text);
        font-variant-numeric: tabular-nums;
    }

    .nb-mp-rec-btn {
        background: none;
        border: none;
        font-size: 1.3rem;
        display: flex;
    }

    .nb-mp-rec-cancel {
        color: var(--nb-text-secondary);
    }

    .nb-mp-rec-cancel:hover {
        color: #dc3545;
    }

    .nb-mp-rec-send {
        color: var(--nb-accent);
    }

    /* ---- Custom voice message player (replaces native <audio controls>) ---- */
    .nb-mp-voice-bubble {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 18px;
        min-width: 190px;
    }

    .nb-mp-bubble-row.theirs .nb-mp-voice-bubble {
        background: var(--nb-hover);
        border-radius: 16px;
    }

    .nb-mp-bubble-row.mine .nb-mp-voice-bubble {
        background: var(--nb-accent);
        border-radius: 16px;
    }

    .nb-mp-voice-play {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .nb-mp-bubble-row.mine .nb-mp-voice-play {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .nb-mp-bubble-row.theirs .nb-mp-voice-play {
        background: var(--nb-bg);
        color: var(--nb-accent);
    }

    .nb-mp-voice-track {
        flex: 1;
        height: 4px;
        border-radius: 2px;
        position: relative;
        cursor: pointer;
    }

    .nb-mp-bubble-row.mine .nb-mp-voice-track {
        background: rgba(255, 255, 255, 0.35);
    }

    .nb-mp-bubble-row.theirs .nb-mp-voice-track {
        background: var(--nb-border);
    }

    .nb-mp-voice-progress {
        height: 100%;
        border-radius: 2px;
        width: 0%;
        pointer-events: none;
    }

    .nb-mp-bubble-row.mine .nb-mp-voice-progress {
        background: #fff;
    }

    .nb-mp-bubble-row.theirs .nb-mp-voice-progress {
        background: var(--nb-accent);
    }

    .nb-mp-voice-duration {
        font-size: 0.72rem;
        font-variant-numeric: tabular-nums;
        flex-shrink: 0;
    }

    .nb-mp-bubble-row.mine .nb-mp-voice-duration {
        color: rgba(255, 255, 255, 0.9);
    }

    .nb-mp-bubble-row.theirs .nb-mp-voice-duration {
        color: var(--nb-text-secondary);
    }

    @media (max-width: 480px) {
        .nb-messenger-panel {
            right: 8px;
            left: 8px;
            width: auto;
        }
    }
</style>

<script>
    (function () {
        // Self-contained: looks up every element it needs by ID directly,
        // rather than relying on variables from the navbar's own script —
        // this file is a separate closure, so nothing is shared between them.

        const messengerToggle = document.getElementById('nbMessengerToggle');
        const messengerPanel = document.getElementById('nbMessengerPanel');
        const listView = document.getElementById('nbMpListView');
        const chatView = document.getElementById('nbMpChatView');
        const backBtn = document.getElementById('nbMpBack');
        const chatAvatarWrap = document.getElementById('nbMpChatAvatarWrap');
        const chatName = document.getElementById('nbMpChatName');
        const thread = document.getElementById('nbMpThread');

        if (messengerToggle) {
            messengerToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                document.getElementById('nbProfileDropdown')?.classList.remove('show');
                messengerPanel.classList.toggle('show');
            });
        }

        // Close this panel on any outside click
        document.addEventListener('click', function () {
            messengerPanel && messengerPanel.classList.remove('show');
        });

        // Don't let clicks inside the panel bubble up and close it
        if (messengerPanel) {
            messengerPanel.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // ---------- Search filter ----------
        const searchInput = document.getElementById('nbMpSearchInput');
        const items = Array.from(document.querySelectorAll('.nb-mp-item'));

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                items.forEach(item => {
                    item.style.display = item.dataset.name.includes(term) ? 'flex' : 'none';
                });
            });
        }

        // ---------- Chat: open / render / send / receive ----------
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const currentUserId = {{ Auth::id() ?? 'null' }};

        const sendForm = document.getElementById('nbMpSendForm');
        const sendInput = document.getElementById('nbMpSendInput');

        let activeConversationId = null;   // numeric id, used for the Echo channel name
        let activeConversationUuid = null; // uuid, used for the send-message route
        let activeChannelName = null;

        function formatTimer(totalSeconds) {
            const m = Math.floor(totalSeconds / 60);
            const s = totalSeconds % 60;
            return `${m}:${String(s).padStart(2, '0')}`;
        }

        // ---------- Custom voice message player ----------
        let currentlyPlayingAudio = null;

        function initVoiceBubbles(container) {
            container.querySelectorAll('.nb-mp-voice-bubble:not([data-voice-init])').forEach((bubble) => {
                bubble.dataset.voiceInit = '1';

                const audio = bubble.querySelector('.nb-mp-voice-audio');
                const playBtn = bubble.querySelector('.nb-mp-voice-play');
                const playIcon = playBtn.querySelector('i');
                const track = bubble.querySelector('.nb-mp-voice-track');
                const progress = bubble.querySelector('.nb-mp-voice-progress');
                const durationEl = bubble.querySelector('.nb-mp-voice-duration');

                audio.addEventListener('loadedmetadata', function () {
                    if (isFinite(audio.duration)) {
                        durationEl.textContent = formatTimer(Math.floor(audio.duration));
                    }
                });

                audio.addEventListener('timeupdate', function () {
                    if (!audio.duration) return;
                    progress.style.width = ((audio.currentTime / audio.duration) * 100) + '%';
                    durationEl.textContent = formatTimer(Math.floor(audio.currentTime));
                });

                audio.addEventListener('ended', function () {
                    playIcon.className = 'bi bi-play-fill';
                    progress.style.width = '0%';
                    if (isFinite(audio.duration)) {
                        durationEl.textContent = formatTimer(Math.floor(audio.duration));
                    }
                    if (currentlyPlayingAudio === audio) currentlyPlayingAudio = null;
                });

                playBtn.addEventListener('click', function () {
                    if (currentlyPlayingAudio && currentlyPlayingAudio !== audio) {
                        currentlyPlayingAudio.pause();
                        const otherBubble = currentlyPlayingAudio.closest('.nb-mp-voice-bubble');
                        if (otherBubble) otherBubble.querySelector('.nb-mp-voice-play i').className = 'bi bi-play-fill';
                    }

                    if (audio.paused) {
                        audio.play();
                        playIcon.className = 'bi bi-pause-fill';
                        currentlyPlayingAudio = audio;
                    } else {
                        audio.pause();
                        playIcon.className = 'bi bi-play-fill';
                        currentlyPlayingAudio = null;
                    }
                });

                track.addEventListener('click', function (e) {
                    if (!audio.duration) return;
                    const rect = track.getBoundingClientRect();
                    const ratio = (e.clientX - rect.left) / rect.width;
                    audio.currentTime = ratio * audio.duration;
                });
            });
        }

        function renderThread(messages, otherFullname) {
            if (!messages.length) {
                thread.classList.remove('has-messages');
                thread.innerHTML = `<p class="nb-mp-thread-placeholder">This is the beginning of your conversation with ${otherFullname}.</p>`;
                return;
            }

            thread.classList.add('has-messages');
            thread.innerHTML = messages.map(renderBubbleHtml).join('');
            thread.scrollTop = thread.scrollHeight;
            initVoiceBubbles(thread);
        }

        function renderBubbleHtml(message) {
            const mine = message.sender_id === currentUserId;

            let bubbleHtml;
            if (message.is_deleted) {
                bubbleHtml = `<div class="nb-mp-bubble deleted">This message was unsent.</div>`;
            } else if (message.type === 'voice') {
                const initialDuration = message.duration_seconds
                    ? formatTimer(message.duration_seconds)
                    : '0:00';

                bubbleHtml = `
                    <div class="nb-mp-bubble nb-mp-voice-bubble">
                        <button type="button" class="nb-mp-voice-play">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <div class="nb-mp-voice-track">
                            <div class="nb-mp-voice-progress"></div>
                        </div>
                        <span class="nb-mp-voice-duration">${initialDuration}</span>
                        <audio class="nb-mp-voice-audio d-none" src="${message.attachment_url}" preload="metadata"></audio>
                    </div>
                `;
            } else {
                bubbleHtml = `<div class="nb-mp-bubble">${escapeHtml(message.body)}</div>`;
            }

            return `<div class="nb-mp-bubble-row ${mine ? 'mine' : 'theirs'}">${bubbleHtml}</div>`;
        }

        function appendMessage(message) {
            thread.classList.add('has-messages');
            if (thread.querySelector('.nb-mp-thread-placeholder') && !thread.classList.contains('has-messages-rendered')) {
                thread.innerHTML = '';
            }
            thread.insertAdjacentHTML('beforeend', renderBubbleHtml(message));
            thread.classList.add('has-messages-rendered');
            thread.scrollTop = thread.scrollHeight;
            initVoiceBubbles(thread);
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function leaveActiveChannel() {
            if (activeChannelName && window.Echo) {
                window.Echo.leave(activeChannelName);
            }
            activeChannelName = null;
        }

        function listenOnConversation(conversationId) {
            leaveActiveChannel();

            if (!window.Echo) {
                return;
            }

            activeChannelName = 'conversation.' + conversationId;

            window.Echo.private(activeChannelName).listen('.message.sent', (payload) => {
                if (payload.sender_id !== currentUserId) {
                    appendMessage(payload);
                }
            });
        }

        async function openConversation(userUuid, fullname) {
            const res = await fetch(`{{ url('/chat/start') }}/${userUuid}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            if (!res.ok) {
                thread.innerHTML = `<p class="nb-mp-thread-placeholder">Couldn't open this conversation. Try again.</p>`;
                return;
            }

            const data = await res.json();

            activeConversationId = data.conversation.id;
            activeConversationUuid = data.conversation.uuid;

            thread.classList.remove('has-messages-rendered');
            renderThread(data.messages, fullname);
            listenOnConversation(activeConversationId);
        }

        // ---------- Open a conversation (click a friend in the list) ----------
        items.forEach(item => {
            item.addEventListener('click', function () {
                const { avatar, initial, fullname, uuid } = this.dataset;

                chatAvatarWrap.innerHTML = avatar
                    ? `<img src="${avatar}" alt="${fullname}">`
                    : `<span class="nb-mp-avatar-fallback">${initial}</span>`;
                chatName.textContent = fullname;

                thread.innerHTML = `<p class="nb-mp-thread-placeholder">Loading…</p>`;

                listView.classList.add('d-none');
                chatView.classList.remove('d-none');

                openConversation(uuid, fullname);
            });
        });

        // ---------- Send a message ----------
        if (sendForm) {
            sendForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const body = sendInput.value.trim();
                if (!body || !activeConversationUuid) return;

                sendInput.value = '';

                const res = await fetch(`{{ url('/chat') }}/${activeConversationUuid}/messages`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ body }),
                });

                if (!res.ok) return;

                const message = await res.json();
                appendMessage(message);
            });
        }

        if (backBtn) {
            backBtn.addEventListener('click', function () {
                leaveActiveChannel();
                activeConversationId = null;
                activeConversationUuid = null;
                chatView.classList.add('d-none');
                listView.classList.remove('d-none');
            });
        }

        // ---------- Voice notes (recording) ----------
        const micBtn = document.getElementById('nbMpMicBtn');
        const recordingBar = document.getElementById('nbMpRecordingBar');
        const recordingTimerEl = document.getElementById('nbMpRecordingTimer');
        const cancelRecordingBtn = document.getElementById('nbMpCancelRecording');
        const stopRecordingBtn = document.getElementById('nbMpStopRecording');

        let mediaRecorder = null;
        let audioChunks = [];
        let recordingStream = null;
        let recordingStartedAt = null;
        let recordingTimerInterval = null;

        function showRecordingUI() {
            sendForm.classList.add('d-none');
            recordingBar.classList.remove('d-none');
        }

        function hideRecordingUI() {
            clearInterval(recordingTimerInterval);
            sendForm.classList.remove('d-none');
            recordingBar.classList.add('d-none');
            recordingTimerEl.textContent = '0:00';
        }

        async function startRecording() {
            if (!navigator.mediaDevices || !window.MediaRecorder) {
                alert('Voice notes are not supported in this browser.');
                return;
            }

            try {
                recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (err) {
                alert('Microphone access is needed to record a voice note.');
                return;
            }

            audioChunks = [];
            mediaRecorder = new MediaRecorder(recordingStream);

            mediaRecorder.addEventListener('dataavailable', function (e) {
                if (e.data.size > 0) audioChunks.push(e.data);
            });

            mediaRecorder.addEventListener('stop', function () {
                recordingStream.getTracks().forEach(track => track.stop());
            });

            mediaRecorder.start();
            recordingStartedAt = Date.now();

            recordingTimerEl.textContent = '0:00';
            recordingTimerInterval = setInterval(function () {
                const elapsed = Math.floor((Date.now() - recordingStartedAt) / 1000);
                recordingTimerEl.textContent = formatTimer(elapsed);
            }, 500);

            showRecordingUI();
        }

        function cancelRecording() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            audioChunks = [];
            hideRecordingUI();
        }

        async function finishRecordingAndSend() {
            if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

            const durationSeconds = Math.floor((Date.now() - recordingStartedAt) / 1000);

            const stopped = new Promise(function (resolve) {
                mediaRecorder.addEventListener('stop', resolve, { once: true });
            });

            mediaRecorder.stop();
            await stopped;

            hideRecordingUI();

            if (!audioChunks.length || !activeConversationUuid) return;

            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            const formData = new FormData();
            formData.append('audio', blob, 'voice-note.webm');
            formData.append('duration', durationSeconds);

            const res = await fetch(`{{ url('/chat') }}/${activeConversationUuid}/voice`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!res.ok) return;

            const message = await res.json();
            appendMessage(message);
        }

        if (micBtn) {
            micBtn.addEventListener('click', function () {
                if (!activeConversationUuid) return;
                startRecording();
            });
        }

        if (cancelRecordingBtn) {
            cancelRecordingBtn.addEventListener('click', cancelRecording);
        }

        if (stopRecordingBtn) {
            stopRecordingBtn.addEventListener('click', finishRecordingAndSend);
        }

        // ---------- External "Message" buttons ----------
        // Any element anywhere on the page with data-open-messenger + the
        // data-friend-* attributes (friend cards, profile pages, etc.) opens
        // this panel directly into that friend's conversation.
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-open-messenger]');
            if (!trigger) return;

            e.preventDefault();
            e.stopPropagation();

            const { friendUuid, friendName, friendAvatar, friendInitial } = trigger.dataset;

            chatAvatarWrap.innerHTML = friendAvatar
                ? `<img src="${friendAvatar}" alt="${friendName}">`
                : `<span class="nb-mp-avatar-fallback">${friendInitial}</span>`;
            chatName.textContent = friendName;

            thread.innerHTML = `<p class="nb-mp-thread-placeholder">Loading…</p>`;

            listView.classList.add('d-none');
            chatView.classList.remove('d-none');
            messengerPanel.classList.add('show');

            openConversation(friendUuid, friendName);
        });
    })();
</script>