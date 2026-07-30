@extends('layouts.profile')

@section('content')
<div class="pf-page container">

    @if (session('status'))
        @include('friends.flash')
    @endif

    {{-- ================= COVER + AVATAR ================= --}}
    <div class="pf-cover-card">
        <div class="pf-cover" id="pfCover">
            @if ($user->cover_photo)
                <img src="{{ $user->cover_photo }}" class="pf-cover-img" id="pfCoverImg">
            @else
                <img src="" class="pf-cover-img d-none" id="pfCoverImg">
            @endif

            @if ($isOwnProfile)
                <button type="button" class="btn btn-light btn-sm pf-cover-btn" id="pfCoverBtn">
                    <i class="bi bi-camera-fill"></i> {{ __('Add Cover Photo') }}
                </button>
                <input type="file" id="pfCoverFileInput" accept="image/*" class="d-none">
            @endif
        </div>

        <div class="pf-identity">
            <div class="pf-avatar-wrap">
                <button type="button" class="pf-avatar-btn" id="pfAvatarBtn">
                    @if ($user->profile_photo)
                        <img src="{{ $user->profile_photo }}" class="pf-avatar" id="pfAvatarImg">
                    @else
                        <span class="pf-avatar pf-avatar-fallback" id="pfAvatarImg">
                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                        </span>
                    @endif

                    @if ($isOwnProfile)
                        <span class="pf-avatar-cam"><i class="bi bi-camera-fill"></i></span>
                    @endif
                </button>

                @if ($isOwnProfile)
                    <div class="pf-avatar-dropdown" id="pfAvatarDropdown">
                        <button type="button" class="pf-ad-item" disabled>
                            {{ __('View Story') }}
                            <span class="pf-ad-soon">{{ __('coming soon') }}</span>
                        </button>
                        <button type="button" class="pf-ad-item" id="pfSeePictureBtn">
                            {{ __('See Profile Picture') }}
                        </button>
                        <button type="button" class="pf-ad-item" id="pfChoosePictureBtn">
                            {{ __('Choose Profile Picture') }}
                        </button>
                    </div>
                    <input type="file" id="pfAvatarFileInput" accept="image/*" class="d-none">
                @endif
            </div>

            <div class="pf-identity-text">
                <div class="pf-name">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="pf-username">{{ '@' . $user->username }}</div>

                <div class="pf-stats">
                    <a href="{{ route('profile.followers', $user->username) }}" class="pf-stat-link">
                        <strong>{{ number_format($user->followers_count) }}</strong> {{ __('Followers') }}
                    </a>
                    <span class="pf-stat-dot">&middot;</span>
                    <a href="{{ route('profile.following', $user->username) }}" class="pf-stat-link">
                        <strong>{{ number_format($user->following_count) }}</strong> {{ __('Following') }}
                    </a>
                </div>

                @if ($user->bio)
                    <div class="pf-bio">{{ $user->bio }}</div>
                @endif
            </div>

            <div class="pf-identity-actions">
                @if ($isOwnProfile)
                    <a href="{{ route('profile.edit') }}" class="btn btn-light">
                        <i class="bi bi-pencil-fill"></i> {{ __('Edit Profile') }}
                    </a>
                @else
                    {{-- <form method="POST" action="{{ route('friends.request', $user->uuid) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus-fill"></i> {{ __('Add Friend') }}
                        </button>
                    </form> --}}

                    <form method="POST"
                          action="{{ $isFollowing ? route('follow.destroy', $user->uuid) : route('follow.store', $user->uuid) }}"
                          class="d-inline">
                        @csrf
                        @if ($isFollowing) @method('DELETE') @endif
                        <button type="submit" class="btn {{ $isFollowing ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                            {{ $isFollowing ? __('Following') : __('Follow') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ================= TABS ================= --}}
        <div class="pf-tabs">
            <span class="pf-tab active">{{ __('About') }}</span>
            <span class="pf-tab">{{ __('Friends') }}</span>
            <span class="pf-tab">{{ __('Photos') }}</span>
        </div>
    </div>

    {{-- ================= PICTURE LIGHTBOX ================= --}}
    <div class="pf-lightbox" id="pfLightbox">
        <button type="button" class="pf-lightbox-close" id="pfLightboxClose">
            <i class="bi bi-x-lg"></i>
        </button>
        <img src="" id="pfLightboxImg" class="pf-lightbox-img">
    </div>

    {{-- ================= BODY: LEFT (details) + RIGHT (posts placeholder) ================= --}}
    <div class="pf-body">

        <div class="pf-col-left">

            <div class="pf-card">
                <div class="pf-card-header">
                    <div class="pf-card-title">{{ __('Personal Details') }}</div>
                    @if ($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" class="pf-edit-icon" title="{{ __('Edit details') }}">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                    @endif
                </div>

                @if ($user->location)
                    <div class="pf-detail-row">
                        <i class="bi bi-geo-alt-fill pf-detail-icon"></i>
                        <span>{{ __('Lives in') }} {{ $user->location }}</span>
                    </div>
                @endif

                @if ($user->education)
                    <div class="pf-detail-row">
                        <i class="bi bi-mortarboard-fill pf-detail-icon"></i>
                        <span>{{ $user->education }}</span>
                    </div>
                @endif

                <div class="pf-detail-row">
                    <i class="bi bi-at pf-detail-icon"></i>
                    <span>{{ $user->username }}</span>
                </div>

                @if ($user->gender)
                    <div class="pf-detail-row">
                        <i class="bi bi-person-fill pf-detail-icon"></i>
                        <span>{{ ucfirst($user->gender) }}</span>
                    </div>
                @endif

                @if ($user->date_of_birth)
                    <div class="pf-detail-row">
                        <i class="bi bi-cake2-fill pf-detail-icon"></i>
                        <span>{{ $user->date_of_birth->format('F j, Y') }}</span>
                    </div>
                @endif

                @if ($isOwnProfile && $user->phone)
                    <div class="pf-detail-row">
                        <i class="bi bi-telephone-fill pf-detail-icon"></i>
                        <span>{{ $user->phone }}</span>
                    </div>
                @endif

                @if ($isOwnProfile)
                    <div class="pf-detail-row">
                        <i class="bi bi-envelope-fill pf-detail-icon"></i>
                        <span>{{ $user->email }}</span>
                    </div>
                @endif

                <div class="pf-detail-row">
                    <i class="bi bi-calendar-event-fill pf-detail-icon"></i>
                    <span>{{ __('Joined') }} {{ $user->created_at->format('F Y') }}</span>
                </div>
            </div>

            <div class="pf-card">
                <div class="pf-card-header">
                    <div class="pf-card-title">{{ __('Friends') }} ({{ $friendsCount }})</div>
                    <a href="{{ route('friends.all') }}" class="pf-see-all">{{ __('See all friends') }}</a>
                </div>

                @if ($friendsPreview->isEmpty())
                    <p class="text-muted small mb-0">{{ __('No friends to show yet.') }}</p>
                @else
                    <div class="pf-friends-grid">
                        @foreach ($friendsPreview as $friend)
                            <a href="{{ route('profile.show', $friend->uuid) }}" class="pf-friend-tile">
                                @if ($friend->profile_photo)
                                    <img src="{{ $friend->profile_photo }}" class="pf-friend-avatar">
                                @else
                                    <span class="pf-friend-avatar pf-avatar-fallback">
                                        {{ strtoupper(substr($friend->first_name, 0, 1)) }}
                                    </span>
                                @endif
                                <span class="pf-friend-name">{{ $friend->first_name }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <div class="pf-col-right">
            @forelse ($posts as $post)
                @include('posts.partials.post-card', ['post' => $post])
            @empty
                <div class="pf-card pf-empty-state">
                    <i class="bi bi-journal-text pf-empty-icon"></i>
                    <p class="text-muted mb-0">{{ __('No posts yet.') }}</p>
                </div>
            @endforelse

            @if ($posts instanceof \Illuminate\Contracts\Pagination\Paginator || $posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-2">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>

    </div>

</div>

<style>
    :root {
        --pf-bg: #ffffff;
        --pf-text: #050505;
        --pf-text-secondary: #65676b;
        --pf-border: #e4e6eb;
        --pf-hover: #f0f2f5;
        --pf-avatar-fallback-bg: #0d6efd;
        --pf-avatar-fallback-text: #ffffff;
        --pf-cover-bg: #dfe3e8;
    }

    [data-theme="dark"] {
        --pf-bg: #242526;
        --pf-text: #e4e6eb;
        --pf-text-secondary: #b0b3b8;
        --pf-border: #3e4042;
        --pf-hover: #3a3b3c;
        --pf-avatar-fallback-bg: #4599ff;
        --pf-avatar-fallback-text: #050505;
        --pf-cover-bg: #18191a;
    }

    .pf-cover-card {
        background: var(--pf-bg);
        border-radius: 10px;
        /* overflow: hidden; */
        margin-bottom: 16px;
    }

    .pf-cover {
        position: relative;
        height: 280px;
        background: var(--pf-cover-bg);
    }

    .pf-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pf-cover-btn {
        position: absolute;
        bottom: 14px;
        right: 14px;
    }

    .pf-identity {
        display: flex;
        align-items: flex-end;
        gap: 16px;
        padding: 0 20px 16px 20px;
        position: relative;
    }

    .pf-avatar-wrap {
        margin-top: -70px;
        position: relative;
    }

    .pf-avatar-btn {
        border: none;
        background: none;
        padding: 0;
        display: block;
        position: relative;
        cursor: pointer;
    }

    .pf-avatar-cam {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--pf-hover);
        border: 2px solid var(--pf-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pf-text);
        font-size: 1rem;
    }

    .pf-avatar-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        background: var(--pf-bg);
        border: 1px solid var(--pf-border);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        min-width: 220px;
        overflow: hidden;
        z-index: 100;
    }

    .pf-avatar-dropdown.show {
        display: block;
    }

    .pf-ad-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 12px 16px;
        font-size: 0.9rem;
        color: var(--pf-text);
        cursor: pointer;
    }

    .pf-ad-item:hover:not(:disabled) {
        background: var(--pf-hover);
    }

    .pf-ad-item:disabled {
        color: var(--pf-text-secondary);
        cursor: not-allowed;
    }

    .pf-ad-soon {
        font-size: 0.7rem;
        color: var(--pf-text-secondary);
    }

    .pf-edit-icon {
        color: var(--pf-text-secondary);
        font-size: 0.9rem;
    }

    .pf-edit-icon:hover {
        color: var(--pf-text);
    }

    /* ---------- Picture lightbox ---------- */
    .pf-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .pf-lightbox.show {
        display: flex;
    }

    .pf-lightbox-img {
        max-width: 90vw;
        max-height: 90vh;
        border-radius: 8px;
    }

    .pf-lightbox-close {
        position: absolute;
        top: 20px;
        right: 24px;
        background: none;
        border: none;
        color: #fff;
        font-size: 1.6rem;
    }

    .pf-avatar {
        width: 168px;
        height: 168px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--pf-bg);
        display: block;
    }

    .pf-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--pf-avatar-fallback-bg);
        color: var(--pf-avatar-fallback-text);
        font-weight: 700;
        font-size: 3.5rem;
    }

    .pf-identity-text {
        flex: 1;
        padding-bottom: 8px;
        min-width: 0;
    }

    .pf-name {
        font-size: 1.7rem;
        font-weight: 800;
        color: var(--pf-text);
    }

    .pf-username {
        color: var(--pf-text-secondary);
        font-size: 0.95rem;
    }

    .pf-stats {
        margin-top: 4px;
        font-size: 0.9rem;
        color: var(--pf-text-secondary);
    }

    .pf-stat-link {
        color: var(--pf-text-secondary);
        text-decoration: none;
    }

    .pf-stat-link:hover {
        text-decoration: underline;
    }

    .pf-stat-link strong {
        color: var(--pf-text);
    }

    .pf-stat-dot {
        margin: 0 6px;
    }

    .pf-bio {
        color: var(--pf-text);
        margin-top: 6px;
        font-size: 0.92rem;
    }

    .pf-identity-actions {
        padding-bottom: 12px;
        flex-shrink: 0;
    }

    .pf-tabs {
        display: flex;
        gap: 4px;
        border-top: 1px solid var(--pf-border);
        padding: 0 20px;
    }

    .pf-tab {
        padding: 14px 12px;
        font-weight: 600;
        color: var(--pf-text-secondary);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        font-size: 0.95rem;
    }

    .pf-tab.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
    }

    .pf-body {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 16px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .pf-body {
            grid-template-columns: 1fr;
        }
        .pf-identity {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    .pf-card {
        background: var(--pf-bg);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .pf-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .pf-card-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--pf-text);
        margin-bottom: 10px;
    }

    .pf-card-header .pf-card-title {
        margin-bottom: 0;
    }

    .pf-see-all {
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }

    .pf-detail-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        color: var(--pf-text);
        font-size: 0.9rem;
    }

    .pf-detail-icon {
        color: var(--pf-text-secondary);
        width: 20px;
        text-align: center;
    }

    .pf-friends-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .pf-friend-tile {
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .pf-friend-avatar {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 8px;
        object-fit: cover;
        font-size: 1.6rem;
    }

    .pf-friend-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--pf-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .pf-empty-state {
        text-align: center;
        padding: 40px 16px;
    }

    .pf-empty-icon {
        font-size: 2.5rem;
        color: var(--pf-text-secondary);
        opacity: 0.5;
        display: block;
        margin-bottom: 10px;
    }

    /* ---------- Upload feedback ---------- */
    .pf-uploading {
        opacity: 0.55;
        pointer-events: none;
    }

    .pf-toast {
        display: none;
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--pf-text);
        color: var(--pf-bg);
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.88rem;
        z-index: 2100;
    }

    .pf-toast.show {
        display: block;
    }

    .pf-toast-error {
        background: #dc3545;
        color: #fff;
    }
</style>

<script>
    (function () {
        const isOwnProfile = @json($isOwnProfile);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ---------- Toast helper ----------
        function showToast(message, isError = false) {
            let toast = document.getElementById('pfToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'pfToast';
                toast.className = 'pf-toast';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.classList.toggle('pf-toast-error', isError);
            toast.classList.add('show');
            clearTimeout(toast._hideTimer);
            toast._hideTimer = setTimeout(() => toast.classList.remove('show'), 2500);
        }

        // ---------- Generic AJAX image upload ----------
        function uploadPhoto({ file, fieldName, url, onSuccess }) {
            const formData = new FormData();
            formData.append(fieldName, file);

            return fetch(url, {
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
                        const msg = data.errors
                            ? Object.values(data.errors)[0][0]
                            : (data.message || '{{ __('Upload failed.') }}');
                        throw new Error(msg);
                    }

                    onSuccess(data.url);
                    showToast(data.message);
                })
                .catch((err) => {
                    showToast(err.message || '{{ __('Upload failed. Please try again.') }}', true);
                });
        }

        // ---------- Avatar / profile picture ----------
        const avatarBtn = document.getElementById('pfAvatarBtn');
        const avatarDropdown = document.getElementById('pfAvatarDropdown');
        let avatarImgEl = document.getElementById('pfAvatarImg');
        const avatarFileInput = document.getElementById('pfAvatarFileInput');
        const seePictureBtn = document.getElementById('pfSeePictureBtn');
        const choosePictureBtn = document.getElementById('pfChoosePictureBtn');

        const lightbox = document.getElementById('pfLightbox');
        const lightboxImg = document.getElementById('pfLightboxImg');
        const lightboxClose = document.getElementById('pfLightboxClose');

        function currentAvatarSrc() {
            return avatarImgEl.tagName === 'IMG' ? avatarImgEl.src : null;
        }

        function openLightbox(src) {
            if (!src) return; // no photo to show (initials fallback only)
            lightboxImg.src = src;
            lightbox.classList.add('show');
        }

        function closeLightbox() {
            lightbox.classList.remove('show');
        }

        lightboxClose.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) closeLightbox();
        });

        if (avatarBtn) {
            avatarBtn.addEventListener('click', function (e) {
                e.stopPropagation();

                if (isOwnProfile && avatarDropdown) {
                    avatarDropdown.classList.toggle('show');
                } else {
                    // Viewing someone else's profile: just open the picture directly
                    openLightbox(currentAvatarSrc());
                }
            });
        }

        document.addEventListener('click', function () {
            if (avatarDropdown) avatarDropdown.classList.remove('show');
        });

        if (avatarDropdown) {
            avatarDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        if (seePictureBtn) {
            seePictureBtn.addEventListener('click', function () {
                avatarDropdown.classList.remove('show');
                openLightbox(currentAvatarSrc());
            });
        }

        if (choosePictureBtn) {
            choosePictureBtn.addEventListener('click', function () {
                avatarDropdown.classList.remove('show');
                avatarFileInput.click();
            });
        }

        // Instant local preview, then upload to Cloudinary via profile_photo column.
        if (avatarFileInput) {
            avatarFileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (avatarImgEl.tagName === 'IMG') {
                        avatarImgEl.src = e.target.result;
                    } else {
                        // Was showing the initials fallback — swap it for a real <img>
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'pf-avatar';
                        img.id = 'pfAvatarImg';
                        avatarImgEl.replaceWith(img);
                        avatarImgEl = img;
                    }

                    avatarImgEl.classList.add('pf-uploading');
                };
                reader.readAsDataURL(file);

                uploadPhoto({
                    file,
                    fieldName: 'profile_photo',
                    url: '{{ route('profile.photo.update') }}',
                    onSuccess: (url) => {
                        if (avatarImgEl.tagName === 'IMG') {
                            avatarImgEl.src = url;
                        }
                    },
                }).finally(() => {
                    avatarImgEl.classList.remove('pf-uploading');
                });
            });
        }

        // ---------- Cover photo ----------
        const coverBtn = document.getElementById('pfCoverBtn');
        const coverFileInput = document.getElementById('pfCoverFileInput');
        const coverImg = document.getElementById('pfCoverImg');

        if (coverBtn) {
            coverBtn.addEventListener('click', function () {
                coverFileInput.click();
            });
        }

        if (coverFileInput) {
            coverFileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    coverImg.src = e.target.result;
                    coverImg.classList.remove('d-none');
                    coverImg.classList.add('pf-uploading');
                };
                reader.readAsDataURL(file);

                uploadPhoto({
                    file,
                    fieldName: 'cover_photo',
                    url: '{{ route('profile.cover.update') }}',
                    onSuccess: (url) => {
                        coverImg.src = url;
                    },
                }).finally(() => {
                    coverImg.classList.remove('pf-uploading');
                });
            });
        }
    })();
</script>
@endsection