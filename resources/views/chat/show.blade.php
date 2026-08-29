@extends('layouts.grouplist')

@section('content')
@php
    $isGroup = $conversation->type === 'group';
    $headerName = $isGroup ? $conversation->name : ($otherUser->first_name . ' ' . $otherUser->last_name);
    $headerAvatar = $isGroup ? $conversation->avatar : $otherUser->profile_photo;
@endphp

<div class="nb-chat-page">
    <div class="nb-chat-page-header">
        @if ($isGroup)
            <a href="{{ route('groups.show', $group->uuid ?? $conversation->group->uuid) }}" class="nb-chat-back">
                <i class="bi bi-arrow-left"></i>
            </a>
        @else
            <a href="{{ route('chat.index') }}" class="nb-chat-back">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif

        @if ($headerAvatar)
            <img src="{{ $headerAvatar }}" class="nb-chat-header-avatar">
        @else
            <span class="nb-chat-header-avatar nb-chat-avatar-fallback">
                {{ strtoupper(substr($headerName, 0, 1)) }}
            </span>
        @endif

        <div class="nb-chat-header-body">
            <span class="nb-chat-header-name">{{ $headerName }}</span>
            @if ($isGroup)
                <span class="nb-chat-header-sub">{{ __('Group chat') }}</span>
            @endif
        </div>
    </div>

    <div class="nb-chat-thread" id="nbChatThread">
        @forelse ($messages as $message)
            <div class="nb-chat-row {{ $message->sender_id === Auth::id() ? 'mine' : 'theirs' }}"
                 data-message-id="{{ $message->id }}">
                @if ($isGroup && $message->sender_id !== Auth::id())
                    <img src="{{ $message->sender->avatar ? asset('storage/' . $message->sender->avatar) : '' }}"
                         class="nb-chat-sender-avatar"
                         onerror="this.style.display='none'">
                @endif

                <div class="nb-chat-bubble-wrap">
                    @if ($isGroup && $message->sender_id !== Auth::id())
                        <span class="nb-chat-sender-name">{{ $message->sender->first_name }}</span>
                    @endif

                    <div class="nb-chat-bubble-slot" data-render="message"
                         data-payload='@json($message->toChatArray())'></div>
                </div>
            </div>
        @empty
            <p class="nb-chat-thread-placeholder">
                {{ $isGroup ? __('No messages yet — say hi to the group!') : __('This is the beginning of your conversation.') }}
            </p>
        @endforelse
    </div>

    <div class="nb-chat-sticker-picker d-none" id="nbChatStickerPicker">
        <div class="nb-chat-sticker-grid" id="nbChatStickerGrid">
            <p class="text-muted small px-2">{{ __('Loading stickers…') }}</p>
        </div>
    </div>

    <form class="nb-chat-send-form" id="nbChatSendForm" enctype="multipart/form-data">
        <button type="button" class="nb-chat-icon-btn" id="nbChatImageBtn" title="{{ __('Send image') }}">
            <i class="bi bi-image"></i>
        </button>
        <input type="file" id="nbChatImageInput" accept="image/*" class="d-none">

        <button type="button" class="nb-chat-icon-btn" id="nbChatStickerBtn" title="{{ __('Send sticker') }}">
            <i class="bi bi-emoji-smile"></i>
        </button>

        <input type="text" class="nb-chat-send-input" id="nbChatSendInput" placeholder="{{ __('Aa') }}" autocomplete="off">

        <button type="button" class="nb-chat-icon-btn" id="nbChatMicBtn" title="{{ __('Record voice note') }}">
            <i class="bi bi-mic-fill"></i>
        </button>

        <button type="submit" class="nb-chat-send-btn">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>

    <div class="nb-chat-recording-bar d-none" id="nbChatRecordingBar">
        <button type="button" id="nbChatCancelRecording" class="nb-chat-rec-btn nb-chat-rec-cancel">
            <i class="bi bi-trash-fill"></i>
        </button>
        <span class="nb-chat-rec-dot"></span>
        <span class="nb-chat-rec-timer" id="nbChatRecordingTimer">0:00</span>
        <button type="button" id="nbChatStopRecording" class="nb-chat-rec-btn nb-chat-rec-send">
            <i class="bi bi-check-circle-fill"></i>
        </button>
    </div>
</div>

<style>
    .nb-chat-page {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 56px);
        max-width: 720px;
        margin: 0 auto;
        background: var(--nb-bg);
        border-left: 1px solid var(--nb-border);
        border-right: 1px solid var(--nb-border);
    }

    .nb-chat-page-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--nb-border);
    }

    .nb-chat-back {
        color: var(--nb-text);
        font-size: 1.1rem;
        display: flex;
    }

    .nb-chat-header-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
    }

    .nb-chat-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--nb-avatar-fallback-bg);
        color: var(--nb-avatar-fallback-text);
        font-weight: 700;
    }

    .nb-chat-header-body {
        display: flex;
        flex-direction: column;
    }

    .nb-chat-header-name {
        font-weight: 700;
        color: var(--nb-text);
    }

    .nb-chat-header-sub {
        font-size: 0.75rem;
        color: var(--nb-text-secondary);
    }

    .nb-chat-thread {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .nb-chat-thread-placeholder {
        margin: auto;
        color: var(--nb-text-secondary);
        font-size: 0.9rem;
    }

    .nb-chat-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .nb-chat-row.mine {
        justify-content: flex-end;
    }

    .nb-chat-row.theirs {
        justify-content: flex-start;
    }

    .nb-chat-sender-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .nb-chat-bubble-wrap {
        display: flex;
        flex-direction: column;
        max-width: 75%;
    }

    .nb-chat-sender-name {
        font-size: 0.72rem;
        color: var(--nb-text-secondary);
        margin: 0 0 2px 4px;
    }

    .nb-chat-bubble {
        padding: 8px 12px;
        border-radius: 16px;
        font-size: 0.9rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .mine .nb-chat-bubble {
        background: var(--nb-accent);
        color: #fff;
        border-bottom-right-radius: 4px;
        margin-left: auto;
    }

    .theirs .nb-chat-bubble {
        background: var(--nb-hover);
        color: var(--nb-text);
        border-bottom-left-radius: 4px;
    }

    .nb-chat-bubble.deleted {
        font-style: italic;
        opacity: 0.7;
    }

    .nb-chat-image-bubble img {
        max-width: 260px;
        max-height: 320px;
        border-radius: 12px;
        display: block;
        object-fit: cover;
    }

    .nb-chat-image-caption {
        margin-top: 4px;
    }

    .nb-chat-sticker-bubble img {
        width: 120px;
        height: 120px;
        object-fit: contain;
    }

    .nb-chat-send-form {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-top: 1px solid var(--nb-border);
    }

    .nb-chat-icon-btn {
        background: none;
        border: none;
        color: var(--nb-text-secondary);
        font-size: 1.2rem;
        display: flex;
        flex-shrink: 0;
    }

    .nb-chat-icon-btn:hover {
        color: var(--nb-accent);
    }

    .nb-chat-send-input {
        flex: 1;
        background: var(--nb-search-bg);
        border: none;
        border-radius: 20px;
        padding: 9px 14px;
        font-size: 0.9rem;
        color: var(--nb-text);
    }

    .nb-chat-send-btn {
        background: none;
        border: none;
        color: var(--nb-accent);
        font-size: 1.3rem;
        display: flex;
        flex-shrink: 0;
    }

    .nb-chat-sticker-picker {
        border-top: 1px solid var(--nb-border);
        max-height: 220px;
        overflow-y: auto;
        padding: 10px;
    }

    .nb-chat-sticker-pack-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        color: var(--nb-text-secondary);
        margin: 6px 4px;
    }

    .nb-chat-sticker-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
    }

    .nb-chat-sticker-thumb {
        width: 100%;
        aspect-ratio: 1;
        object-fit: contain;
        cursor: pointer;
        border-radius: 8px;
        padding: 4px;
    }

    .nb-chat-sticker-thumb:hover {
        background: var(--nb-hover);
    }

    .nb-chat-recording-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-top: 1px solid var(--nb-border);
    }

    .nb-chat-rec-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #dc3545;
        animation: nb-chat-pulse 1.2s infinite ease-in-out;
    }

    @keyframes nb-chat-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .nb-chat-rec-timer {
        flex: 1;
        font-size: 0.88rem;
        color: var(--nb-text);
        font-variant-numeric: tabular-nums;
    }

    .nb-chat-rec-btn {
        background: none;
        border: none;
        font-size: 1.3rem;
        display: flex;
    }

    .nb-chat-rec-cancel {
        color: var(--nb-text-secondary);
    }

    .nb-chat-rec-send {
        color: var(--nb-accent);
    }

    .nb-chat-voice-bubble {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 18px;
        min-width: 190px;
    }

    .mine .nb-chat-voice-bubble {
        background: var(--nb-accent);
    }

    .theirs .nb-chat-voice-bubble {
        background: var(--nb-hover);
    }

    .nb-chat-voice-play {
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

    .mine .nb-chat-voice-play {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }

    .theirs .nb-chat-voice-play {
        background: var(--nb-bg);
        color: var(--nb-accent);
    }

    .nb-chat-voice-track {
        flex: 1;
        height: 4px;
        border-radius: 2px;
        position: relative;
        cursor: pointer;
    }

    .mine .nb-chat-voice-track { background: rgba(255,255,255,0.35); }
    .theirs .nb-chat-voice-track { background: var(--nb-border); }

    .nb-chat-voice-progress {
        height: 100%;
        border-radius: 2px;
        width: 0%;
        pointer-events: none;
    }

    .mine .nb-chat-voice-progress { background: #fff; }
    .theirs .nb-chat-voice-progress { background: var(--nb-accent); }

    .nb-chat-voice-duration {
        font-size: 0.72rem;
        font-variant-numeric: tabular-nums;
        flex-shrink: 0;
    }

    .mine .nb-chat-voice-duration { color: rgba(255,255,255,0.9); }
    .theirs .nb-chat-voice-duration { color: var(--nb-text-secondary); }
</style>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const currentUserId = {{ Auth::id() }};
    const isGroup = @json($isGroup);
    const conversationId = {{ $conversation->id }};
    const conversationUuid = "{{ $conversation->uuid }}";

    const thread = document.getElementById('nbChatThread');
    const sendForm = document.getElementById('nbChatSendForm');
    const sendInput = document.getElementById('nbChatSendInput');

    function formatTimer(totalSeconds) {
        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        return `${m}:${String(s).padStart(2, '0')}`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // ---------- Voice bubble player ----------
    let currentlyPlayingAudio = null;

    function initVoiceBubbles(container) {
        container.querySelectorAll('.nb-chat-voice-bubble:not([data-voice-init])').forEach((bubble) => {
            bubble.dataset.voiceInit = '1';

            const audio = bubble.querySelector('.nb-chat-voice-audio');
            const playBtn = bubble.querySelector('.nb-chat-voice-play');
            const playIcon = playBtn.querySelector('i');
            const track = bubble.querySelector('.nb-chat-voice-track');
            const progress = bubble.querySelector('.nb-chat-voice-progress');
            const durationEl = bubble.querySelector('.nb-chat-voice-duration');

            audio.addEventListener('loadedmetadata', function () {
                if (isFinite(audio.duration)) durationEl.textContent = formatTimer(Math.floor(audio.duration));
            });

            audio.addEventListener('timeupdate', function () {
                if (!audio.duration) return;
                progress.style.width = ((audio.currentTime / audio.duration) * 100) + '%';
                durationEl.textContent = formatTimer(Math.floor(audio.currentTime));
            });

            audio.addEventListener('ended', function () {
                playIcon.className = 'bi bi-play-fill';
                progress.style.width = '0%';
                if (isFinite(audio.duration)) durationEl.textContent = formatTimer(Math.floor(audio.duration));
                if (currentlyPlayingAudio === audio) currentlyPlayingAudio = null;
            });

            playBtn.addEventListener('click', function () {
                if (currentlyPlayingAudio && currentlyPlayingAudio !== audio) {
                    currentlyPlayingAudio.pause();
                    const otherBubble = currentlyPlayingAudio.closest('.nb-chat-voice-bubble');
                    if (otherBubble) otherBubble.querySelector('.nb-chat-voice-play i').className = 'bi bi-play-fill';
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
                audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
            });
        });
    }

    // ---------- Bubble rendering (shared by initial page render + live appends) ----------
    function bubbleInnerHtml(message) {
        if (message.is_deleted) {
            return `<div class="nb-chat-bubble deleted">${__('This message was unsent.')}</div>`;
        }

        switch (message.type) {
            case 'voice': {
                const initialDuration = message.duration_seconds ? formatTimer(message.duration_seconds) : '0:00';
                return `
                    <div class="nb-chat-bubble nb-chat-voice-bubble">
                        <button type="button" class="nb-chat-voice-play"><i class="bi bi-play-fill"></i></button>
                        <div class="nb-chat-voice-track"><div class="nb-chat-voice-progress"></div></div>
                        <span class="nb-chat-voice-duration">${initialDuration}</span>
                        <audio class="nb-chat-voice-audio d-none" src="${message.attachment_url}" preload="metadata"></audio>
                    </div>`;
            }
            case 'image': {
                const caption = message.body ? `<div class="nb-chat-bubble nb-chat-image-caption">${escapeHtml(message.body)}</div>` : '';
                return `
                    <div class="nb-chat-bubble nb-chat-image-bubble">
                        <img src="${message.attachment_url}" alt="">
                    </div>
                    ${caption}`;
            }
            case 'sticker':
                return `
                    <div class="nb-chat-sticker-bubble">
                        <img src="${message.attachment_url}" alt="${escapeHtml(message.attachment_name)}">
                    </div>`;
            case 'system':
                return `<div class="nb-chat-bubble deleted">${escapeHtml(message.body)}</div>`;
            default:
                return `<div class="nb-chat-bubble">${escapeHtml(message.body)}</div>`;
        }
    }

    function __(s) { return s; } // i18n placeholder — swap for your JS translation helper if you have one

    function renderRowHtml(message) {
        const mine = message.sender_id === currentUserId;
        const showSender = isGroup && !mine;

        const avatarHtml = showSender
            ? `<img src="${message.sender.avatar ?? ''}" class="nb-chat-sender-avatar" onerror="this.style.display='none'">`
            : '';

        const nameHtml = showSender
            ? `<span class="nb-chat-sender-name">${escapeHtml(message.sender.first_name)}</span>`
            : '';

        return `
            <div class="nb-chat-row ${mine ? 'mine' : 'theirs'}" data-message-id="${message.id}">
                ${avatarHtml}
                <div class="nb-chat-bubble-wrap">
                    ${nameHtml}
                    ${bubbleInnerHtml(message)}
                </div>
            </div>`;
    }

    function appendMessage(message) {
        thread.querySelector('.nb-chat-thread-placeholder')?.remove();
        thread.insertAdjacentHTML('beforeend', renderRowHtml(message));
        thread.scrollTop = thread.scrollHeight;
        initVoiceBubbles(thread);
    }

    // ---------- Hydrate server-rendered bubbles on load (they arrive as raw JSON payloads) ----------
    document.querySelectorAll('[data-render="message"]').forEach((slot) => {
        const message = JSON.parse(slot.dataset.payload);
        slot.outerHTML = bubbleInnerHtml(message);
    });
    initVoiceBubbles(thread);
    thread.scrollTop = thread.scrollHeight;

    // ---------- Real-time: listen for messages from other participants ----------
    if (window.Echo) {
        window.Echo.private('conversation.' + conversationId).listen('.message.sent', (payload) => {
            if (payload.sender_id !== currentUserId) {
                appendMessage(payload);
            }
        });
    }

    // ---------- Send text ----------
    sendForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const body = sendInput.value.trim();
        if (!body) return;

        sendInput.value = '';

        const res = await fetch(`{{ url('/chat') }}/${conversationUuid}/messages`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ body }),
        });

        if (!res.ok) return;
        appendMessage(await res.json());
    });

    // ---------- Send image ----------
    const imageBtn = document.getElementById('nbChatImageBtn');
    const imageInput = document.getElementById('nbChatImageInput');

    imageBtn.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', async function () {
        const file = this.files[0];
        this.value = '';
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        const caption = sendInput.value.trim();
        if (caption) formData.append('caption', caption);
        sendInput.value = '';

        const res = await fetch(`{{ url('/chat') }}/${conversationUuid}/image`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        });

        if (!res.ok) return;
        appendMessage(await res.json());
    });

    // ---------- Sticker picker ----------
    const stickerBtn = document.getElementById('nbChatStickerBtn');
    const stickerPicker = document.getElementById('nbChatStickerPicker');
    const stickerGrid = document.getElementById('nbChatStickerGrid');
    let stickersLoaded = false;

    async function loadStickers() {
        if (stickersLoaded) return;
        stickersLoaded = true;

        const res = await fetch(`{{ route('stickers.index') }}`, {
            headers: { 'Accept': 'application/json' },
        });

        if (!res.ok) {
            stickerGrid.innerHTML = `<p class="text-muted small px-2">${__('Could not load stickers.')}</p>`;
            return;
        }

        const data = await res.json();
        const packs = data.stickers;

        if (!Object.keys(packs).length) {
            stickerGrid.innerHTML = `<p class="text-muted small px-2">${__('No stickers available yet.')}</p>`;
            return;
        }

        stickerGrid.innerHTML = Object.entries(packs).map(([pack, stickers]) => `
            <div class="nb-chat-sticker-pack-label">${escapeHtml(pack)}</div>
            <div class="nb-chat-sticker-grid">
                ${stickers.map(s => `
                    <img src="${s.image_path}" class="nb-chat-sticker-thumb" data-uuid="${s.uuid}" title="${escapeHtml(s.name)}">
                `).join('')}
            </div>
        `).join('');
    }

    stickerBtn.addEventListener('click', function () {
        stickerPicker.classList.toggle('d-none');
        if (!stickerPicker.classList.contains('d-none')) loadStickers();
    });

    stickerGrid.addEventListener('click', async function (e) {
        const thumb = e.target.closest('.nb-chat-sticker-thumb');
        if (!thumb) return;

        stickerPicker.classList.add('d-none');

        const res = await fetch(`{{ url('/chat') }}/${conversationUuid}/sticker`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ sticker_uuid: thumb.dataset.uuid }),
        });

        if (!res.ok) return;
        appendMessage(await res.json());
    });

    // ---------- Voice notes ----------
    const micBtn = document.getElementById('nbChatMicBtn');
    const recordingBar = document.getElementById('nbChatRecordingBar');
    const recordingTimerEl = document.getElementById('nbChatRecordingTimer');
    const cancelRecordingBtn = document.getElementById('nbChatCancelRecording');
    const stopRecordingBtn = document.getElementById('nbChatStopRecording');

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

        mediaRecorder.addEventListener('dataavailable', (e) => {
            if (e.data.size > 0) audioChunks.push(e.data);
        });

        mediaRecorder.addEventListener('stop', () => {
            recordingStream.getTracks().forEach(track => track.stop());
        });

        mediaRecorder.start();
        recordingStartedAt = Date.now();
        recordingTimerEl.textContent = '0:00';

        recordingTimerInterval = setInterval(() => {
            recordingTimerEl.textContent = formatTimer(Math.floor((Date.now() - recordingStartedAt) / 1000));
        }, 500);

        showRecordingUI();
    }

    function cancelRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
        audioChunks = [];
        hideRecordingUI();
    }

    async function finishRecordingAndSend() {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

        const durationSeconds = Math.floor((Date.now() - recordingStartedAt) / 1000);
        const stopped = new Promise((resolve) => mediaRecorder.addEventListener('stop', resolve, { once: true }));

        mediaRecorder.stop();
        await stopped;
        hideRecordingUI();

        if (!audioChunks.length) return;

        const blob = new Blob(audioChunks, { type: 'audio/webm' });
        const formData = new FormData();
        formData.append('audio', blob, 'voice-note.webm');
        formData.append('duration', durationSeconds);

        const res = await fetch(`{{ url('/chat') }}/${conversationUuid}/voice`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        });

        if (!res.ok) return;
        appendMessage(await res.json());
    }

    micBtn.addEventListener('click', startRecording);
    cancelRecordingBtn.addEventListener('click', cancelRecording);
    stopRecordingBtn.addEventListener('click', finishRecordingAndSend);
})();
</script>
@endsection