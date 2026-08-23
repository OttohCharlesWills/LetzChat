<div class="cp-overlay" id="createPostOverlay">
    <div class="cp-modal">
        <div class="cp-header">
            <div class="cp-title" id="cpModalTitle">{{ __('Create post') }}</div>
            <button type="button" class="cp-close" id="createPostClose" aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <form method="POST" action="{{ route('posts.store') }}" id="createPostForm" enctype="multipart/form-data">
            @csrf

            <div class="cp-body">
                <div class="cp-user-row">
                    @if (auth()->user()->profile_photo)
                        <img src="{{ auth()->user()->profile_photo }}" class="cp-avatar" alt="{{ auth()->user()->first_name }}">
                    @else
                        <div class="cp-avatar-fallback">
                            {{ strtoupper(mb_substr(auth()->user()->first_name, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <div class="cp-username">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                        <select name="visibility" id="cpVisibility" class="cp-visibility-select">
                            <option value="public">🌍 {{ __('Public') }}</option>
                            <option value="friends">👥 {{ __('Friends') }}</option>
                            <option value="custom">🚫 {{ __('Custom') }}</option>
                            <option value="private">🔒 {{ __('Only me') }}</option>
                        </select>
                    </div>
                </div>

                {{-- ================= POST-TO SELECTOR (home only) ================= --}}
                <div class="cp-postto-row" id="cpPostToRow">
                    <button type="button" class="cp-postto-btn active" data-target="timeline" id="cpPostToTimelineBtn">
                        {{ __('Your Timeline') }}
                    </button>
                    <button type="button" class="cp-postto-btn" data-target="groups" id="cpPostToGroupsBtn">
                        {{ __('Groups') }}
                    </button>
                </div>

                <div class="cp-group-picker" id="cpGroupPicker" style="display:none;">
                    <div class="cp-group-picker-title">{{ __('Choose one or more groups to post to:') }}</div>
                    <div class="cp-group-list" id="cpGroupList">
                        <div class="cp-group-picker-loading">{{ __('Loading your groups...') }}</div>
                    </div>
                </div>

                <div class="cp-group-locked-banner" id="cpGroupLockedBanner" style="display:none;"></div>

                <textarea name="body" id="cpBody" class="cp-textarea"
                          placeholder="{{ __("What's on your mind, :name?", ['name' => auth()->user()->first_name]) }}"
                          maxlength="5000" autofocus></textarea>

                <div class="cp-error" id="cpBodyError" style="display:none;"></div>

                <div class="cp-image-preview" id="cpImagePreview"></div>

                <div class="cp-video-preview" id="cpVideoPreview"></div>

                <div class="cp-upload-progress" id="cpUploadProgress" style="display:none;">
                    <div class="cp-upload-progress-bar" id="cpUploadProgressBar"></div>
                    <span class="cp-upload-progress-label" id="cpUploadProgressLabel">{{ __('Uploading video...') }} 0%</span>
                </div>

                <div class="cp-exclude-picker" id="cpExcludePicker">
                    <div class="cp-exclude-title">{{ __("Don't show this to:") }}</div>

                    @forelse ($friends ?? [] as $friend)
                        <label class="cp-exclude-item">
                            <input type="checkbox" name="excluded_user_ids[]" value="{{ $friend->id }}">
                            {{ $friend->first_name }} {{ $friend->last_name }}
                        </label>
                    @empty
                        <div class="cp-exclude-title mb-0">{{ __("You don't have any friends to exclude yet.") }}</div>
                    @endforelse
                </div>

                <div class="cp-attach-row">
                    <span>{{ __('Add to your post') }}</span>
                    <div class="cp-attach-icons">
                        <label for="cpImageInput" class="cp-attach-icon" id="cpImageIconLabel" style="cursor:pointer;" title="{{ __('Add photos') }}">🖼️</label>
                        <input type="file" name="images[]" id="cpImageInput" accept="image/png,image/jpeg,image/webp" multiple hidden>

                        <label for="cpVideoInput" class="cp-attach-icon" id="cpVideoIconLabel" style="cursor:pointer;" title="{{ __('Add a video') }}">🎥</label>
                        <input type="file" name="video" id="cpVideoInput" accept="video/mp4,video/quicktime,video/webm" hidden>

                        <span class="cp-attach-icon" title="{{ __('Coming soon') }}">📍</span>
                        <span class="cp-attach-icon" title="{{ __('Coming soon') }}">🙂</span>
                    </div>
                </div>
            </div>

            <div class="cp-footer">
                <button type="submit" class="cp-post-btn" id="cpPostBtn" disabled>{{ __('Post') }}</button>
            </div>
        </form>
    </div>
</div>

<style>
    .cp-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        align-items: flex-start;
        justify-content: center;
        z-index: 1050;
        padding: 40px 16px;
        overflow-y: auto;
    }

    .cp-overlay.active {
        display: flex;
    }

    .cp-modal {
        background: var(--sb-bg);
        color: var(--sb-text);
        width: 100%;
        max-width: 500px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
    }

    .cp-header {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border-bottom: 1px solid var(--sb-border);
        padding: 14px 16px;
    }

    .cp-title {
        font-weight: 700;
        font-size: 1.05rem;
    }

    .cp-close {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--sb-hover);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        font-size: 1.2rem;
        line-height: 1;
        color: var(--sb-text);
    }

    .cp-close:hover {
        background: var(--sb-border);
    }

    .cp-body {
        padding: 16px;
    }

    .cp-user-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .cp-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        background: var(--sb-avatar-fallback-bg);
    }

    .cp-avatar-fallback {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none !important;
    }

    .cp-username {
        font-weight: 600;
        font-size: 0.92rem;
        line-height: 1.2;
    }

    .cp-visibility-select {
        margin-top: 3px;
        background: var(--sb-hover);
        border: 1px solid var(--sb-border);
        border-radius: 5px;
        padding: 3px 8px;
        font-size: 0.78rem;
        color: var(--sb-text);
    }

    /* ---- Post-to selector ---- */
    .cp-postto-row {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .cp-postto-btn {
        flex: 1;
        background: var(--sb-hover);
        border: 1px solid var(--sb-border);
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--sb-text-secondary);
    }

    .cp-postto-btn.active {
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text);
        border-color: var(--sb-avatar-fallback-bg);
    }

    .cp-group-picker {
        border: 1px solid var(--sb-border);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        background: var(--sb-hover);
    }

    .cp-group-picker-title {
        font-size: 0.8rem;
        color: var(--sb-text-secondary);
        margin-bottom: 8px;
    }

    .cp-group-picker-loading,
    .cp-group-picker-empty {
        font-size: 0.85rem;
        color: var(--sb-text-secondary);
    }

    .cp-group-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        font-size: 0.88rem;
        cursor: pointer;
    }

    .cp-group-item img,
    .cp-group-item .cp-group-item-fallback {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .cp-group-locked-banner {
        background: var(--sb-hover);
        border: 1px solid var(--sb-border);
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--sb-text);
    }

    .cp-textarea {
        width: 100%;
        min-height: 120px;
        border: none;
        resize: none;
        font-size: 1.15rem;
        color: var(--sb-text);
        background: transparent;
    }

    .cp-textarea:focus {
        outline: none;
    }

    .cp-error {
        color: #dc3545;
        font-size: 0.82rem;
        margin-top: 4px;
    }

    .cp-image-preview {
        display: none;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 12px;
    }

    .cp-image-preview.active {
        display: grid;
    }

    .cp-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
    }

    .cp-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cp-preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 0.85rem;
        line-height: 1;
        cursor: pointer;
    }

    .cp-video-preview {
        display: none;
        position: relative;
        margin-top: 12px;
        border-radius: 8px;
        overflow: hidden;
        background: #000;
    }

    .cp-video-preview.active {
        display: block;
    }

    .cp-video-preview video {
        width: 100%;
        max-height: 320px;
        display: block;
    }

    .cp-video-preview .cp-preview-remove {
        top: 8px;
        right: 8px;
    }

    .cp-upload-progress {
        margin-top: 10px;
        background: var(--sb-hover);
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        height: 26px;
    }

    .cp-upload-progress-bar {
        height: 100%;
        width: 0%;
        background: var(--sb-avatar-fallback-bg);
        transition: width 0.15s ease;
    }

    .cp-upload-progress-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--sb-text);
        mix-blend-mode: difference;
    }

    .cp-exclude-picker {
        display: none;
        margin-top: 6px;
        padding: 12px;
        border: 1px solid var(--sb-border);
        border-radius: 8px;
        background: var(--sb-hover);
    }

    .cp-exclude-picker.active {
        display: block;
    }

    .cp-exclude-title {
        font-size: 0.8rem;
        color: var(--sb-text-secondary);
        margin-bottom: 6px;
    }

    .cp-exclude-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 3px 0;
        font-size: 0.87rem;
    }

    .cp-attach-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--sb-border);
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 14px;
        font-size: 0.88rem;
        font-weight: 600;
    }

    .cp-attach-icons {
        display: flex;
        gap: 10px;
        font-size: 1.15rem;
    }

    .cp-attach-icons .cp-attach-icon[title="{{ __('Coming soon') }}"] {
        opacity: 0.45;
    }

    .cp-attach-icon.cp-icon-disabled {
        opacity: 0.3;
        pointer-events: none;
    }

    .cp-footer {
        padding: 12px 16px 16px 16px;
    }

    .cp-post-btn {
        width: 100%;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text);
        border: none;
        border-radius: 6px;
        padding: 9px 0;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .cp-post-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>

<script>
    (function () {
        const overlay = document.getElementById('createPostOverlay');
        const closeBtn = document.getElementById('createPostClose');
        const form = document.getElementById('createPostForm');
        const modalTitle = document.getElementById('cpModalTitle');
        const textarea = document.getElementById('cpBody');
        const postBtn = document.getElementById('cpPostBtn');
        const visibilitySelect = document.getElementById('cpVisibility');
        const excludePicker = document.getElementById('cpExcludePicker');
        const bodyError = document.getElementById('cpBodyError');

        const imageInput = document.getElementById('cpImageInput');
        const previewBox = document.getElementById('cpImagePreview');
        const imageIconLabel = document.getElementById('cpImageIconLabel');

        const videoInput = document.getElementById('cpVideoInput');
        const videoPreviewBox = document.getElementById('cpVideoPreview');
        const videoIconLabel = document.getElementById('cpVideoIconLabel');

        const progressWrap = document.getElementById('cpUploadProgress');
        const progressBar = document.getElementById('cpUploadProgressBar');
        const progressLabel = document.getElementById('cpUploadProgressLabel');

        // ---- Post-to elements ----
        const postToRow = document.getElementById('cpPostToRow');
        const postToTimelineBtn = document.getElementById('cpPostToTimelineBtn');
        const postToGroupsBtn = document.getElementById('cpPostToGroupsBtn');
        const groupPicker = document.getElementById('cpGroupPicker');
        const groupList = document.getElementById('cpGroupList');
        const groupLockedBanner = document.getElementById('cpGroupLockedBanner');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const MAX_IMAGES = 10;
        let selectedImages = [];
        let selectedVideo = null;

        // 'timeline' | 'groups' | 'group-locked'
        let postToMode = 'timeline';
        let lockedGroupId = null;
        let lockedGroupName = null;
        let selectedGroupIds = new Set();
        let groupsLoaded = false;

        // options: undefined/null -> home timeline mode.
        // { groupId, groupName } -> locked to that single group (opened from a group page).
        window.openCreatePostModal = function (options) {
            if (options && options.groupId) {
                lockedGroupId = options.groupId;
                lockedGroupName = options.groupName || '';
                postToMode = 'group-locked';
            } else {
                lockedGroupId = null;
                lockedGroupName = null;
                postToMode = 'timeline';
                selectedGroupIds = new Set();
            }

            applyPostToModeUI();

            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => textarea.focus(), 50);
        };

        function applyPostToModeUI() {
            if (postToMode === 'group-locked') {
                postToRow.style.display = 'none';
                groupPicker.style.display = 'none';
                groupLockedBanner.style.display = 'block';
                groupLockedBanner.textContent = '{{ __('Posting in') }} ' + lockedGroupName;
                // Group posts are governed by group membership, not the
                // friends/public visibility system, so hide that entirely.
                visibilitySelect.style.display = 'none';
                excludePicker.classList.remove('active');
            } else {
                postToRow.style.display = 'flex';
                groupLockedBanner.style.display = 'none';
                visibilitySelect.style.display = '';
                setPostToTab('timeline');
            }
        }

        function setPostToTab(tab) {
            postToMode = tab;
            postToTimelineBtn.classList.toggle('active', tab === 'timeline');
            postToGroupsBtn.classList.toggle('active', tab === 'groups');
            groupPicker.style.display = tab === 'groups' ? 'block' : 'none';

            if (tab === 'groups') {
                visibilitySelect.style.display = 'none';
                excludePicker.classList.remove('active');
                if (!groupsLoaded) loadPostableGroups();
            } else {
                visibilitySelect.style.display = '';
            }

            updatePostButtonState();
        }

        postToTimelineBtn.addEventListener('click', () => setPostToTab('timeline'));
        postToGroupsBtn.addEventListener('click', () => setPostToTab('groups'));

        function loadPostableGroups() {
            fetch('{{ route('groups.postable') }}', { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    groupsLoaded = true;
                    const groups = data.groups || [];

                    if (!groups.length) {
                        groupList.innerHTML = `<div class="cp-group-picker-empty">{{ __("You don't belong to any groups you can post in yet.") }}</div>`;
                        return;
                    }

                    groupList.innerHTML = groups.map(g => `
                        <label class="cp-group-item">
                            <input type="checkbox" class="cp-group-checkbox" value="${g.id}">
                            ${g.cover_photo
                                ? `<img src="${g.cover_photo}" alt="">`
                                : `<span class="cp-group-item-fallback">${(g.name || '?').charAt(0).toUpperCase()}</span>`}
                            <span>${g.name}</span>
                        </label>
                    `).join('');

                    groupList.querySelectorAll('.cp-group-checkbox').forEach(cb => {
                        cb.addEventListener('change', function () {
                            if (this.checked) {
                                selectedGroupIds.add(this.value);
                            } else {
                                selectedGroupIds.delete(this.value);
                            }
                            updatePostButtonState();
                        });
                    });
                })
                .catch(() => {
                    groupList.innerHTML = `<div class="cp-group-picker-empty">{{ __('Could not load your groups. Try again.') }}</div>`;
                });
        }

        function closeModal() {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function updatePostButtonState() {
            const hasText = textarea.value.trim().length > 0;
            const hasImages = selectedImages.length > 0;
            const hasVideo = !!selectedVideo;
            const hasContent = hasText || hasImages || hasVideo;

            if (postToMode === 'groups') {
                postBtn.disabled = !(hasContent && selectedGroupIds.size > 0);
            } else {
                postBtn.disabled = !hasContent;
            }
        }

        function syncAttachIconAvailability() {
            imageIconLabel.classList.toggle('cp-icon-disabled', !!selectedVideo);
            videoIconLabel.classList.toggle('cp-icon-disabled', selectedImages.length > 0);
        }

        function renderPreviews() {
            previewBox.innerHTML = '';
            previewBox.classList.toggle('active', selectedImages.length > 0);

            selectedImages.forEach((file, index) => {
                const url = URL.createObjectURL(file);
                const item = document.createElement('div');
                item.className = 'cp-preview-item';
                item.innerHTML = `
                    <img src="${url}" alt="">
                    <button type="button" class="cp-preview-remove" data-index="${index}">&times;</button>
                `;
                previewBox.appendChild(item);
            });

            previewBox.querySelectorAll('.cp-preview-remove').forEach(btn => {
                btn.addEventListener('click', function () {
                    selectedImages.splice(Number(this.dataset.index), 1);
                    renderPreviews();
                    syncAttachIconAvailability();
                    updatePostButtonState();
                });
            });
        }

        function renderVideoPreview() {
            videoPreviewBox.innerHTML = '';

            if (!selectedVideo) {
                videoPreviewBox.classList.remove('active');
                return;
            }

            const url = URL.createObjectURL(selectedVideo);
            videoPreviewBox.classList.add('active');
            videoPreviewBox.innerHTML = `
                <video src="${url}" controls></video>
                <button type="button" class="cp-preview-remove" id="cpVideoRemoveBtn">&times;</button>
            `;

            document.getElementById('cpVideoRemoveBtn').addEventListener('click', function () {
                selectedVideo = null;
                videoInput.value = '';
                renderVideoPreview();
                syncAttachIconAvailability();
                updatePostButtonState();
            });
        }

        function resetForm() {
            form.reset();
            excludePicker.classList.remove('active');
            bodyError.style.display = 'none';
            selectedImages = [];
            selectedVideo = null;
            videoInput.value = '';
            renderPreviews();
            renderVideoPreview();
            syncAttachIconAvailability();
            progressWrap.style.display = 'none';
            progressBar.style.width = '0%';
            selectedGroupIds = new Set();
            groupList.querySelectorAll('.cp-group-checkbox').forEach(cb => cb.checked = false);
            postBtn.disabled = true;
        }

        closeBtn.addEventListener('click', function () {
            closeModal();
        });

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) closeModal();
        });

        textarea.addEventListener('input', function () {
            updatePostButtonState();
            bodyError.style.display = 'none';
        });

        visibilitySelect.addEventListener('change', function () {
            excludePicker.classList.toggle('active', this.value === 'custom');
        });

        imageInput.addEventListener('change', function () {
            const incoming = Array.from(this.files);

            selectedImages = [...selectedImages, ...incoming].slice(0, MAX_IMAGES);
            this.value = '';

            renderPreviews();
            syncAttachIconAvailability();
            updatePostButtonState();
            bodyError.style.display = 'none';
        });

        videoInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                selectedVideo = this.files[0];
            }

            renderVideoPreview();
            syncAttachIconAvailability();
            updatePostButtonState();
            bodyError.style.display = 'none';
        });

        function uploadVideoWithProgress(postId, videoFile) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();

                const videoFormData = new FormData();
                videoFormData.append('video', videoFile);
                videoFormData.append('type', 'video');

                xhr.open('POST', `/posts/${postId}/videos`, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = pct + '%';
                        progressLabel.textContent = `{{ __('Uploading video...') }} ${pct}%`;
                    }
                });

                xhr.onload = function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(JSON.parse(xhr.responseText));
                    } else {
                        reject(new Error('Video upload failed'));
                    }
                };

                xhr.onerror = function () {
                    reject(new Error('Video upload failed'));
                };

                xhr.send(videoFormData);
            });
        }

        // Creates ONE post via the existing endpoint, optionally scoped to a
        // single group_id. Returns the parsed JSON on success.
        function createSinglePost(groupId) {
            const formData = new FormData(form);
            formData.delete('images[]');
            formData.delete('video');
            selectedImages.forEach(file => formData.append('images[]', file));

            let url = form.action;

            if (groupId) {
                formData.delete('group_id');
                formData.delete('visibility');
                formData.delete('excluded_user_ids[]');
                url = `/groups/${groupId}/posts`;
            }

            return fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
            }).then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    const err = new Error(data.message || 'Request failed');
                    err.data = data;
                    err.status = response.status;
                    throw err;
                }
                return data;
            });
        }

        async function attachVideoIfAny(postData) {
            if (!selectedVideo || !postData.id) return postData;

            postBtn.textContent = '{{ __('Uploading video...') }}';
            progressWrap.style.display = 'block';
            await uploadVideoWithProgress(postData.id, selectedVideo);
            return postData;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const hasText = textarea.value.trim().length > 0;
            if (!hasText && selectedImages.length === 0 && !selectedVideo) return;
            if (postToMode === 'groups' && selectedGroupIds.size === 0) return;

            postBtn.disabled = true;
            const originalLabel = postBtn.textContent;
            postBtn.textContent = '{{ __('Posting...') }}';
            bodyError.style.display = 'none';

            let targets;
            if (postToMode === 'group-locked') {
                targets = [lockedGroupId];
            } else if (postToMode === 'groups') {
                targets = Array.from(selectedGroupIds);
            } else {
                targets = [null]; // plain timeline post
            }

            (async () => {
                try {
                    let lastResult = null;

                    for (const groupId of targets) {
                        let result = await createSinglePost(groupId);
                        result = await attachVideoIfAny(result);
                        lastResult = result;

                        if (result.status !== 'pending' && result.html) {
                            document.dispatchEvent(new CustomEvent('post:created', {
                                detail: { html: result.html, message: result.message },
                            }));
                        }
                    }

                    resetForm();
                    closeModal();

                    if (lastResult && lastResult.status === 'pending') {
                        alert(lastResult.message);
                    }
                } catch (err) {
                    if (err.status === 422 && err.data && err.data.errors) {
                        bodyError.textContent = Object.values(err.data.errors)[0][0];
                    } else {
                        bodyError.textContent = err.message || '{{ __('Something went wrong. Please try again.') }}';
                    }
                    bodyError.style.display = 'block';
                } finally {
                    postBtn.disabled = false;
                    postBtn.textContent = originalLabel;
                    progressWrap.style.display = 'none';
                }
            })();
        });
    })();
</script>