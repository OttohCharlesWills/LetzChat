@extends('layouts.grouplist')

@section('content')
<div class="gp-page container">

    @if (session('status'))
        @include('friends.flash')
    @endif

    {{-- ================= COVER + IDENTITY ================= --}}
    <div class="gp-cover-card">
        <div class="gp-cover" id="gpCover" style="{{ $group->cover_photo ? 'background-image:url(' . $group->cover_photo . ');' : '' }}">
            @if ($isAdmin)
                <button type="button" class="gp-cover-edit-btn" id="gpCoverEditBtn">
                    <i class="bi bi-camera-fill"></i> {{ __('Edit cover photo') }}
                </button>
                <input type="file" id="gpCoverInput" accept="image/png, image/jpeg, image/webp" class="d-none">
            @endif
        </div>

        <div class="gp-identity">
            <div class="gp-identity-text">
                <div class="gp-name">{{ $group->name }}</div>
                <div class="gp-meta">
                    <i class="bi bi-{{ $group->privacy === 'private' ? 'lock-fill' : 'globe' }}"></i>
                    {{ ucfirst($group->privacy) }} {{ __('group') }}
                    &middot;
                    {{ number_format($group->members_count) }} {{ Str::plural(__('member'), $group->members_count) }}
                </div>
            </div>
        </div>

        <div class="gp-toolbar-row">
            <div class="gp-member-stack">
                @foreach ($memberPreview as $member)
                    @if ($member->profile_photo)
                        <img src="{{ $member->profile_photo }}" class="gp-stack-avatar" title="{{ $member->first_name }} {{ $member->last_name }}">
                    @else
                        <span class="gp-stack-avatar gp-avatar-fallback" title="{{ $member->first_name }} {{ $member->last_name }}">
                            {{ strtoupper(substr($member->first_name, 0, 1)) }}
                        </span>
                    @endif
                @endforeach
            </div>

            <div class="gp-toolbar-actions">

                @if ($isMember)
                    <a href="{{ route('groups.chat', $group->uuid) }}" class="btn btn-primary btn-sm gp-chat-btn">
                        <i class="bi bi-chat-dots-fill"></i> {{ __('Group Chat') }}
                    </a>
                @endif
                
                <button type="button" class="btn btn-primary btn-sm gp-invite-btn" title="{{ __('Coming soon') }}" disabled>
                    <i class="bi bi-person-plus-fill"></i> {{ __('Invite') }}
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm gp-share-btn">
                    <i class="bi bi-share-fill"></i> {{ __('Share') }}
                </button>

                <button type="button" class="gp-icon-btn" title="{{ __('Search in group') }}" disabled>
                    <i class="bi bi-search"></i>
                </button>

                <div class="gp-options-wrap">
                    <button type="button" class="gp-icon-btn" id="gpOptionsBtn" title="{{ __('More') }}">
                        <i class="bi bi-three-dots"></i>
                    </button>

                    <div class="gp-options-dropdown" id="gpOptionsDropdown">
                        <button type="button" class="gp-options-item" title="{{ __('Coming soon') }}" disabled>
                            <i class="bi bi-people-fill"></i> {{ __('View member requests') }}
                        </button>
                        <button type="button" class="gp-options-item" title="{{ __('Coming soon') }}" disabled>
                            <i class="bi bi-person-plus-fill"></i> {{ __('Invite people') }}
                        </button>
                        <button type="button" class="gp-options-item" id="gpDropdownShareBtn">
                            <i class="bi bi-share-fill"></i> {{ __('Share') }}
                        </button>
                        <button type="button" class="gp-options-item" title="{{ __('Coming soon') }}" disabled>
                            <i class="bi bi-bell-fill"></i> {{ __('Manage notifications') }}
                        </button>
                        <button type="button" class="gp-options-item" title="{{ __('Coming soon') }}" disabled>
                            <i class="bi bi-eye-slash-fill"></i> {{ __('Unfollow group') }}
                        </button>

                        @if ($isMember)
                            <form method="POST" action="{{ route('groups.leave', $group->uuid) }}">
                                @csrf
                                <button type="submit" class="gp-options-item gp-options-danger">
                                    <i class="bi bi-box-arrow-right"></i> {{ __('Leave group') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @unless ($isMember)
            <div class="gp-join-row">
                <form method="POST" action="{{ route('groups.join', $group->uuid) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('Join Group') }}
                    </button>
                </form>
            </div>
        @endunless
    </div>

    {{-- ================= BODY: LEFT (about) + RIGHT (post feed) ================= --}}
    <div class="gp-body">

        <div class="gp-col-left">
            <div class="gp-card">
                <div class="gp-card-title">{{ __('About') }}</div>

                @if ($group->description)
                    <p class="gp-description">{{ $group->description }}</p>
                @else
                    <p class="gp-description gp-muted">{{ __('No description yet.') }}</p>
                @endif

                <div class="gp-detail-row">
                    <i class="bi bi-{{ $group->privacy === 'private' ? 'lock-fill' : 'globe' }} gp-detail-icon"></i>
                    <span>{{ ucfirst($group->privacy) }} {{ __('group') }}</span>
                </div>

                @if ($group->created_at)
                    <div class="gp-detail-row">
                        <i class="bi bi-calendar-event-fill gp-detail-icon"></i>
                        <span>{{ __('Created') }} {{ $group->created_at->format('F Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="gp-col-right">

            @if (!$isMember)
                <div class="gp-card gp-join-prompt">
                    <i class="bi bi-people-fill"></i>
                    {{ __('Join this group to post and comment.') }}
                </div>
            @endif
            
            @include('groups.composer-trigger')
            
            @include('posts.partials.create-post-modal')

            @forelse ($posts as $post)
                @include('posts.partials.post-card', ['post' => $post])
            @empty
                <div class="gp-card gp-empty-state">
                    <i class="bi bi-journal-text gp-empty-icon"></i>
                    <p class="gp-muted mb-0">{{ __('No posts in this group yet.') }}</p>
                </div>
            @endforelse

            @if (method_exists($posts, 'hasPages') && $posts->hasPages())
                <div class="mt-2">
                    {{ $posts->links() }}
                </div>
            @endif

        </div>

    </div>

</div>

<div class="gp-toast" id="gpToast"></div>

<style>
    :root {
        --gp-bg: #ffffff;
        --gp-text: #050505;
        --gp-text-secondary: #65676b;
        --gp-border: #e4e6eb;
        --gp-hover: #f0f2f5;
        --gp-avatar-fallback-bg: #e85d3f;
        --gp-avatar-fallback-text: #ffffff;
        --gp-cover-bg: #dfe3e8;
    }

    [data-theme="dark"] {
        --gp-bg: #242526;
        --gp-text: #e4e6eb;
        --gp-text-secondary: #b0b3b8;
        --gp-border: #3e4042;
        --gp-hover: #3a3b3c;
        --gp-avatar-fallback-bg: #4599ff;
        --gp-avatar-fallback-text: #050505;
        --gp-cover-bg: #18191a;
    }

    .gp-cover-card {
        background: var(--gp-bg);
        border-radius: 10px;
        /* overflow: hidden; */
        margin-bottom: 16px;
    }

    .gp-cover {
        position: relative;
        height: 180px;
        background: var(--gp-cover-bg);
        background-size: cover;
        background-position: center;
    }

    .gp-cover-edit-btn {
        position: absolute;
        right: 14px;
        bottom: 14px;
        background: var(--gp-bg);
        border: 1px solid var(--gp-border);
        color: var(--gp-text);
        border-radius: 6px;
        padding: 7px 14px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .gp-cover-edit-btn:hover {
        background: var(--gp-hover);
    }

    .gp-cover.gp-uploading {
        opacity: 0.6;
    }

    .gp-identity {
        padding: 16px 20px 8px 20px;
    }

    .gp-name {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--gp-text);
    }

    .gp-meta {
        color: var(--gp-text-secondary);
        font-size: 0.9rem;
        margin-top: 2px;
    }

    .gp-toolbar-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 20px 16px 20px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .gp-member-stack {
        display: flex;
    }

    .gp-stack-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--gp-bg);
        margin-left: -10px;
        background: var(--gp-avatar-fallback-bg);
    }

    .gp-stack-avatar:first-child {
        margin-left: 0;
    }

    .gp-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gp-avatar-fallback-text) !important;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .gp-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gp-icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid var(--gp-border);
        background: var(--gp-bg);
        color: var(--gp-text);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gp-icon-btn:hover:not(:disabled) {
        background: var(--gp-hover);
    }

    .gp-icon-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .gp-options-wrap {
        position: relative;
    }

    .gp-options-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 6px;
        background: var(--gp-bg);
        border: 1px solid var(--gp-border);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        min-width: 230px;
        overflow: hidden;
        z-index: 100;
    }

    .gp-options-dropdown.show {
        display: block;
    }

    .gp-options-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 11px 16px;
        font-size: 0.88rem;
        color: var(--gp-text);
    }

    .gp-options-item:hover:not(:disabled) {
        background: var(--gp-hover);
    }

    .gp-options-item:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .gp-options-danger {
        color: #dc3545;
    }

    .gp-join-row {
        padding: 0 20px 16px 20px;
    }

    .gp-body {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 16px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .gp-body {
            grid-template-columns: 1fr;
        }
    }

    .gp-card {
        background: var(--gp-bg);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .gp-card-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--gp-text);
        margin-bottom: 10px;
    }

    .gp-description {
        color: var(--gp-text);
        font-size: 0.92rem;
        white-space: pre-line;
    }

    .gp-muted {
        color: var(--gp-text-secondary);
    }

    .gp-detail-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        color: var(--gp-text);
        font-size: 0.9rem;
    }

    .gp-detail-icon {
        color: var(--gp-text-secondary);
        width: 20px;
        text-align: center;
    }

    .gp-join-prompt {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--gp-text-secondary);
        font-size: 0.9rem;
    }

    .gp-empty-state {
        text-align: center;
        padding: 40px 16px;
    }

    .gp-empty-icon {
        font-size: 2.5rem;
        color: var(--gp-text-secondary);
        opacity: 0.5;
        display: block;
        margin-bottom: 10px;
    }

    .gp-toast {
        display: none;
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--gp-text);
        color: var(--gp-bg);
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.88rem;
        z-index: 2000;
    }

    .gp-toast.show {
        display: block;
    }
</style>

<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const toast = document.getElementById('gpToast');

        function showToast(message) {
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }

        // ---------- Options dropdown ----------
        const optionsBtn = document.getElementById('gpOptionsBtn');
        const optionsDropdown = document.getElementById('gpOptionsDropdown');

        if (optionsBtn) {
            optionsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                optionsDropdown.classList.toggle('show');
            });
        }

        document.addEventListener('click', function () {
            optionsDropdown && optionsDropdown.classList.remove('show');
        });

        if (optionsDropdown) {
            optionsDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // ---------- Share (copy link) ----------
        function shareGroupLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showToast('{{ __('Group link copied to clipboard.') }}');
            }).catch(() => {
                showToast('{{ __('Could not copy link.') }}');
            });
        }

        document.querySelectorAll('.gp-share-btn, #gpDropdownShareBtn').forEach((btn) => {
            btn.addEventListener('click', function () {
                shareGroupLink();
                optionsDropdown && optionsDropdown.classList.remove('show');
            });
        });

        // ---------- Cover photo upload (admin only) ----------
        const coverEditBtn = document.getElementById('gpCoverEditBtn');
        const coverInput = document.getElementById('gpCoverInput');
        const cover = document.getElementById('gpCover');

        if (coverEditBtn) {
            coverEditBtn.addEventListener('click', function () {
                coverInput.click();
            });
        }

        if (coverInput) {
            coverInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                cover.classList.add('gp-uploading');

                const formData = new FormData();
                formData.append('cover_photo', file);

                fetch('{{ route('groups.cover.update', $group->uuid) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then(async (response) => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || '{{ __('Upload failed.') }}');
                        }
                        cover.style.backgroundImage = `url(${data.url})`;
                        showToast(data.message);
                    })
                    .catch((err) => {
                        showToast(err.message || '{{ __('Upload failed. Please try again.') }}');
                    })
                    .finally(() => {
                        cover.classList.remove('gp-uploading');
                        coverInput.value = '';
                    });
            });
        }
    })();
</script>
@endsection