<div class="cp-overlay" id="createPostOverlay">
    <div class="cp-modal">
        <div class="cp-header">
            <div class="cp-title">{{ __('Create post') }}</div>
            <button type="button" class="cp-close" id="createPostClose" aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <form method="POST" action="{{ route('posts.store') }}" id="createPostForm" enctype="multipart/form-data">
            @csrf

            <div class="cp-body">
                <div class="cp-user-row">
                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/'.auth()->user()->avatar) }}" class="cp-avatar" alt="{{ auth()->user()->first_name }}">
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

                <textarea name="body" id="cpBody" class="cp-textarea"
                          placeholder="{{ __("What's on your mind, :name?", ['name' => auth()->user()->first_name]) }}"
                          maxlength="5000" autofocus></textarea>

                <div class="cp-error" id="cpBodyError" style="display:none;"></div>

                <div class="cp-image-preview" id="cpImagePreview"></div>

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
                        <label for="cpImageInput" class="cp-attach-icon" style="cursor:pointer;" title="{{ __('Add photos') }}">🖼️</label>
                        <input type="file" name="images[]" id="cpImageInput" accept="image/png,image/jpeg,image/webp" multiple hidden>
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
        const textarea = document.getElementById('cpBody');
        const postBtn = document.getElementById('cpPostBtn');
        const visibilitySelect = document.getElementById('cpVisibility');
        const excludePicker = document.getElementById('cpExcludePicker');
        const bodyError = document.getElementById('cpBodyError');
        const imageInput = document.getElementById('cpImageInput');
        const previewBox = document.getElementById('cpImagePreview');

        const MAX_IMAGES = 10;
        let selectedImages = [];

        window.openCreatePostModal = function () {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => textarea.focus(), 50);
        };

        function closeModal() {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function updatePostButtonState() {
            const hasText = textarea.value.trim().length > 0;
            const hasImages = selectedImages.length > 0;
            postBtn.disabled = !(hasText || hasImages);
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
                    updatePostButtonState();
                });
            });
        }

        function resetForm() {
            form.reset();
            excludePicker.classList.remove('active');
            bodyError.style.display = 'none';
            selectedImages = [];
            renderPreviews();
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
            this.value = ''; // allow re-selecting the same file later

            renderPreviews();
            updatePostButtonState();
            bodyError.style.display = 'none';
        });

        // ---- AJAX submit: no page reload ----
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!textarea.value.trim() && selectedImages.length === 0) return;

            postBtn.disabled = true;
            const originalLabel = postBtn.textContent;
            postBtn.textContent = '{{ __('Posting...') }}';
            bodyError.style.display = 'none';

            const formData = new FormData(form);
            formData.delete('images[]'); // drop whatever the raw file input holds
            selectedImages.forEach(file => formData.append('images[]', file));

            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
            })
                .then(async (response) => {
                    const data = await response.json();

                    if (response.status === 422) {
                        const msg = (data.errors && (data.errors.body || data.errors.images))
                            ? (data.errors.body ? data.errors.body[0] : data.errors.images[0])
                            : data.message;
                        bodyError.textContent = msg;
                        bodyError.style.display = 'block';
                        postBtn.disabled = false;
                        postBtn.textContent = originalLabel;
                        return;
                    }

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    // Hand the rendered post HTML to whatever's showing the feed.
                    document.dispatchEvent(new CustomEvent('post:created', {
                        detail: { html: data.html, message: data.message },
                    }));

                    resetForm();
                    closeModal();
                })
                .catch(() => {
                    bodyError.textContent = '{{ __('Something went wrong. Please try again.') }}';
                    bodyError.style.display = 'block';
                })
                .finally(() => {
                    postBtn.textContent = originalLabel;
                    postBtn.disabled = textarea.value.trim().length === 0 && selectedImages.length === 0;
                });
        });
    })();
</script>