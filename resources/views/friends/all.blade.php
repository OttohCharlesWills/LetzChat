@extends('layouts.friendlist')

@section('content')

@include('friends.flash')

<div class="fl-wrap">
    {{-- ================= RIGHT: PREVIEW PANE ================= --}}
    <div class="fl-preview-col" id="flPreviewCol">
        <div class="fl-preview-empty" id="flPreviewEmpty">
            <i class="bi bi-people-fill fl-preview-icon"></i>
            <div class="fl-preview-text">{{ __("Select people's names to preview their profile.") }}</div>
        </div>

        <div class="fl-preview-filled d-none" id="flPreviewFilled">
            <span class="fl-preview-avatar" id="flPreviewAvatarWrap"></span>
            <div class="fl-preview-name" id="flPreviewName"></div>
            <div class="fl-preview-mutual" id="flPreviewMutual"></div>

            <div class="fl-preview-actions">
                <a href="#" id="flPreviewProfileLink" class="btn btn-primary btn-sm">{{ __('View Profile') }}</a>
                <form method="POST" id="flPreviewRemoveForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Remove Friend') }}</button>
                </form>
            </div>
        </div>
    </div>

</div>

<style>
    :root {
        --fl-bg: #ffffff;
        --fl-text: #050505;
        --fl-text-secondary: #65676b;
        --fl-border: #e4e6eb;
        --fl-hover: #f0f2f5;
        --fl-active-bg: #e7f3ff;
        --fl-search-bg: #f0f2f5;
        --fl-avatar-fallback-bg: #0d6efd;
        --fl-avatar-fallback-text: #ffffff;
    }

    [data-theme="dark"] {
        --fl-bg: #3a3b3c;
        --fl-text: #e4e6eb;
        --fl-text-secondary: #b0b3b8;
        --fl-border: #4b4c4d;
        --fl-hover: #4e4f50;
        --fl-active-bg: #263951;
        --fl-search-bg: #4e4f50;
        --fl-avatar-fallback-bg: #4599ff;
        --fl-avatar-fallback-text: #050505;
    }

    .fl-wrap {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        height: calc(100vh - 4rem);
    }

    /* ---------- Left list column ---------- */
    .fl-list-col {
        background: var(--fl-bg);
        border-radius: 10px;
        width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .fl-list-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px 8px 16px;
    }

    .fl-back {
        color: var(--fl-text-secondary);
        font-size: 1.2rem;
        text-decoration: none;
    }

    .fl-eyebrow {
        font-size: 0.8rem;
        color: var(--fl-text-secondary);
    }

    .fl-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--fl-text);
    }

    .fl-search-wrap {
        position: relative;
        margin: 8px 16px;
    }

    .fl-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--fl-text-secondary);
        font-size: 0.9rem;
    }

    .fl-search-input {
        width: 100%;
        background: var(--fl-search-bg);
        border: none;
        border-radius: 20px;
        padding: 8px 12px 8px 34px;
        color: var(--fl-text);
        font-size: 0.9rem;
    }

    .fl-search-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px var(--fl-active-bg);
    }

    .fl-count {
        font-weight: 700;
        color: var(--fl-text);
        padding: 8px 16px 4px 16px;
        font-size: 0.95rem;
    }

    .fl-list {
        flex: 1;
        overflow-y: auto;
        padding: 4px 8px 16px 8px;
    }

    .fl-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px;
        border: none;
        background: none;
        border-radius: 8px;
        text-align: left;
        cursor: pointer;
        position: relative;
    }

    .fl-item:hover {
        background: var(--fl-hover);
    }

    .fl-item.active {
        background: var(--fl-active-bg);
    }

    .fl-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .fl-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--fl-avatar-fallback-bg);
        color: var(--fl-avatar-fallback-text);
        font-weight: 700;
    }

    .fl-item-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .fl-item-name {
        font-weight: 600;
        color: var(--fl-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.92rem;
    }

    .fl-item-mutual {
        font-size: 0.78rem;
        color: var(--fl-text-secondary);
    }

    .fl-item-menu {
        position: relative;
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: var(--fl-text-secondary);
    }

    .fl-item-menu:hover {
        background: var(--fl-border);
    }

    .fl-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 30px;
        background: var(--fl-bg);
        border: 1px solid var(--fl-border);
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
        z-index: 50;
        min-width: 160px;
        overflow: hidden;
    }

    .fl-dropdown.show {
        display: block;
    }

    .fl-dropdown-item {
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 10px 14px;
        font-size: 0.88rem;
        color: var(--fl-text);
        cursor: pointer;
    }

    .fl-dropdown-item:hover {
        background: var(--fl-hover);
    }

    .fl-dropdown-danger {
        color: #dc3545;
    }

    /* ---------- Right preview column ---------- */
    .fl-preview-col {
        flex: 1;
        background: var(--fl-bg);
        border-radius: 10px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .fl-preview-icon {
        font-size: 3rem;
        color: var(--fl-text-secondary);
        opacity: 0.5;
        margin-bottom: 14px;
        display: block;
    }

    .fl-preview-text {
        color: var(--fl-text-secondary);
        font-weight: 600;
        font-size: 1.05rem;
    }

    .fl-preview-filled {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .fl-preview-avatar img,
    .fl-preview-avatar .fl-avatar-fallback {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        font-size: 2.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fl-preview-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--fl-text);
        margin-top: 8px;
    }

    .fl-preview-mutual {
        color: var(--fl-text-secondary);
        font-size: 0.9rem;
    }

    .fl-preview-actions {
        display: flex;
        gap: 10px;
        margin-top: 14px;
    }

    @media (max-width: 767.98px) {
        .fl-wrap {
            flex-direction: column;
            height: auto;
        }
        .fl-list-col {
            width: 100%;
        }
        .fl-preview-col {
            display: none; /* keep it simple on mobile, list only */
        }
    }
</style>

<script>
    // ---- Live client-side search filter ----
    const flSearchInput = document.getElementById('flSearchInput');
    const flList = document.getElementById('flList');
    const flCount = document.getElementById('flCount');
    const flItems = Array.from(flList.querySelectorAll('.fl-item'));

    flSearchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        let visibleCount = 0;

        flItems.forEach(item => {
            const matches = item.dataset.name.includes(term);
            item.style.display = matches ? 'flex' : 'none';
            if (matches) visibleCount++;
        });

        flCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'friend' : 'friends'} found`;
    });

    // ---- Click a friend to preview them, no reload ----
    const flPreviewEmpty = document.getElementById('flPreviewEmpty');
    const flPreviewFilled = document.getElementById('flPreviewFilled');
    const flPreviewAvatarWrap = document.getElementById('flPreviewAvatarWrap');
    const flPreviewName = document.getElementById('flPreviewName');
    const flPreviewMutual = document.getElementById('flPreviewMutual');
    const flPreviewProfileLink = document.getElementById('flPreviewProfileLink');
    const flPreviewRemoveForm = document.getElementById('flPreviewRemoveForm');

    flItems.forEach(item => {
        item.addEventListener('click', function () {
            flItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            const { avatar, initial, fullname, mutual, username, friendshipId } = this.dataset;

            flPreviewAvatarWrap.innerHTML = avatar
                ? `<img src="${avatar}" alt="${fullname}">`
                : `<span class="fl-avatar-fallback">${initial}</span>`;

            flPreviewName.textContent = fullname;
            flPreviewMutual.textContent = Number(mutual) > 0
                ? `${mutual} mutual friend${mutual == 1 ? '' : 's'}`
                : '';

            flPreviewProfileLink.href = "{{ url('/') }}/profile/{{ user()->uuid }}";
            flPreviewRemoveForm.action = `{{ url('/friends') }}/${friendshipId}`;

            flPreviewEmpty.classList.add('d-none');
            flPreviewFilled.classList.remove('d-none');
        });
    });

    // ---- Per-item "..." dropdown menu ----
    function toggleFlMenu(el) {
        const dropdown = el.querySelector('.fl-dropdown');
        document.querySelectorAll('.fl-dropdown.show').forEach(d => {
            if (d !== dropdown) d.classList.remove('show');
        });
        dropdown.classList.toggle('show');
    }

    document.addEventListener('click', function () {
        document.querySelectorAll('.fl-dropdown.show').forEach(d => d.classList.remove('show'));
    });
</script>

@endsection