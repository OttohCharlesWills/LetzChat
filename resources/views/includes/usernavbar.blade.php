<nav class="navbar navbar-expand-md nb-navbar">
    <div class="container-fluid nb-inner">

        {{-- ================= LEFT: BRAND + SEARCH ================= --}}
        <div class="nb-left">
            <a class="navbar-brand nb-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>

            <button type="button" class="nb-icon-btn nb-search-btn" title="{{ __('Search') }}">
                <i class="bi bi-search"></i>
            </button>
        </div>

        {{-- ================= CENTER: MAIN NAV ICONS ================= --}}
        <div class="nb-center">
            <a href="{{ url('/home') }}" class="nb-center-icon active" title="{{ __('Home') }}">
                <i class="bi bi-house-fill"></i>
            </a>

            <a href="{{ route('friends.index') }}" class="nb-center-icon" title="{{ __('Friends') }}">
                <i class="bi bi-people-fill"></i>
            </a>

            <a href="#" class="nb-center-icon" title="{{ __('Watch') }}">
                <i class="bi bi-collection-play-fill"></i>
            </a>

            <a href="#" class="nb-center-icon" title="{{ __('Groups') }}">
                <i class="bi bi-diagram-3-fill"></i>
            </a>
        </div>

        <div class="nb-icons">

            {{-- ================= MESSENGER ================= --}}
            <div class="nb-icon-btn nb-messenger-toggle" id="nbMessengerToggle" title="{{ __('Messenger') }}">
                <i class="bi bi-messenger"></i>
            </div>

            {{-- Notifications (placeholder for now) --}}
            {{-- ================= NOTIFICATIONS ================= --}}
            <div class="nb-icon-btn nb-notif-toggle" id="nbNotifToggle" title="{{ __('Notifications') }}">
                <i class="bi bi-bell-fill"></i>
                <span class="nb-notif-badge" id="nbNotifBadge" style="display:none;">0</span>
            </div>

            <div class="nb-dropdown nb-notif-dropdown" id="nbNotifDropdown">
                <div class="nb-notif-header">
                    <span>{{ __('Notifications') }}</span>
                    <button type="button" id="nbMarkAllReadBtn" class="nb-notif-markall">{{ __('Mark all as read') }}</button>
                </div>

                <div class="nb-notif-list" id="nbNotifList">
                    <div class="ntf-empty">{{ __('Loading...') }}</div>
                </div>

                <a href="{{ route('notifications.index') }}" class="nb-notif-viewall">{{ __('See all notifications') }}</a>
            </div>

            {{-- ================= PROFILE DROPDOWN ================= --}}
            @auth
                <div class="nb-icon-btn nb-profile-toggle" id="nbProfileToggle">
                    @if (auth()->user()->profile_photo)
                        <img src="{{ auth()->user()->profile_photo }}" class="nb-profile-avatar">
                    @else
                        <span class="nb-profile-avatar nb-profile-avatar-fallback">
                            {{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}
                        </span>
                    @endif
                </div>

                <div class="nb-dropdown" id="nbProfileDropdown">
                    <a href="{{ route('profile.show', Auth::user()->username) }}" class="nb-dropdown-item">
                        {{ __('View Profile') }}
                    </a>
                    <a href="{{ route('logout') }}" class="nb-dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="nb-icon-btn" title="{{ __('Login') }}">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                @endif
            @endauth

        </div>

    </div>

    @include('messenger.panel')
</nav>

<style>
    :root {
        --nb-bg: #ffffff;
        --nb-text: #050505;
        --nb-text-secondary: #65676b;
        --nb-border: #e4e6eb;
        --nb-hover: #f0f2f5;
        --nb-accent: #0d6efd;
        --nb-avatar-fallback-bg: #0d6efd;
        --nb-avatar-fallback-text: #ffffff;
        --nb-search-bg: #f0f2f5;
    }

    [data-theme="dark"] {
        --nb-bg: #242526;
        --nb-text: #e4e6eb;
        --nb-text-secondary: #b0b3b8;
        --nb-border: #3e4042;
        --nb-hover: #3a3b3c;
        --nb-accent: #4599ff;
        --nb-avatar-fallback-bg: #4599ff;
        --nb-avatar-fallback-text: #050505;
        --nb-search-bg: #3a3b3c;
    }

    .nb-navbar {
        background: var(--nb-bg);
        border-bottom: 1px solid var(--nb-border);
        padding: 8px 0;
        position: relative;
    }

    .nb-inner {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 12px;
    }

    .nb-left {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-self: start;
        min-width: 0;
    }

    .nb-search-btn {
        flex-shrink: 0;
    }

    .nb-center {
        display: flex;
        align-items: center;
        gap: 4px;
        justify-self: center;
    }

    .nb-center-icon {
        width: 90px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--nb-text-secondary);
        font-size: 1.35rem;
        text-decoration: none;
        position: relative;
    }

    .nb-center-icon:hover {
        background: var(--nb-hover);
        color: var(--nb-text);
    }

    .nb-center-icon.active {
        color: var(--nb-accent);
    }

    .nb-center-icon.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 10px;
        right: 10px;
        height: 3px;
        background: var(--nb-accent);
        border-radius: 3px 3px 0 0;
    }

    @media (max-width: 767.98px) {
        .nb-brand {
            font-size: 1.1rem;
        }
        .nb-center {
            display: none;
        }
    }

    .nb-brand {
        font-weight: 800;
        color: var(--nb-text);
        font-size: 1.3rem;
        text-decoration: none;
    }

    .nb-icons {
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        justify-self: end;
    }

    .nb-icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--nb-hover);
        color: var(--nb-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        cursor: pointer;
        text-decoration: none;
        border: none;
    }

    .nb-icon-btn:hover {
        filter: brightness(0.95);
        color: var(--nb-text);
    }

    .nb-profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .nb-profile-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--nb-avatar-fallback-bg);
        color: var(--nb-avatar-fallback-text);
        font-weight: 700;
    }

    /* ---------- Generic small dropdown (profile menu) ---------- */
    .nb-dropdown {
        display: none;
        position: absolute;
        top: 48px;
        right: 0;
        background: var(--nb-bg);
        border: 1px solid var(--nb-border);
        border-radius: 10px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        min-width: 180px;
        overflow: hidden;
        z-index: 1050;
    }

    .nb-dropdown.show {
        display: block;
    }

    .nb-dropdown-item {
        display: block;
        padding: 10px 16px;
        color: var(--nb-text);
        text-decoration: none;
        font-size: 0.9rem;
    }

    .nb-dropdown-item:hover {
        background: var(--nb-hover);
        color: var(--nb-text);
    }

    .nb-notif-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #dc3545;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 16px;
    height: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 3px;
}

    .nb-notif-toggle {
        position: relative;
    }

    .nb-notif-dropdown {
        width: 340px;
        max-width: 90vw;
        max-height: 480px;
        display: none;
        flex-direction: column;
    }

    .nb-notif-dropdown.show {
        display: flex;
    }

    .nb-notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border-bottom: 1px solid var(--nb-border);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .nb-notif-markall {
        background: none;
        border: none;
        color: var(--nb-accent);
        font-size: 0.78rem;
        font-weight: 600;
    }

    .nb-notif-list {
        overflow-y: auto;
        flex: 1;
    }

    .nb-notif-viewall {
        display: block;
        text-align: center;
        padding: 10px;
        border-top: 1px solid var(--nb-border);
        color: var(--nb-accent);
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }

    .ntf-item {
        display: flex;
        gap: 10px;
        padding: 10px 14px;
        position: relative;
        text-decoration: none;
        color: var(--nb-text);
        cursor: pointer;
    }

    .ntf-item:hover {
        background: var(--nb-hover);
    }

    .ntf-unread {
        background: rgba(13, 110, 253, 0.06);
    }

    .ntf-avatar-link {
        position: relative;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
    }

    .ntf-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        background: var(--nb-avatar-fallback-bg);
    }

    .ntf-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--nb-avatar-fallback-text);
        font-weight: 700;
    }

    .ntf-icon-badge {
        position: absolute;
        bottom: -3px;
        right: -3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--nb-accent);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        border: 2px solid var(--nb-bg);
    }

    .ntf-body {
        flex: 1;
        min-width: 0;
    }

    .ntf-message {
        font-size: 0.85rem;
        margin: 0;
        color: var(--nb-text);
        line-height: 1.35;
    }

    .ntf-excerpt {
        font-size: 0.78rem;
        color: var(--nb-text-secondary);
        margin: 2px 0 0;
        font-style: italic;
    }

    .ntf-time {
        font-size: 0.75rem;
        color: var(--nb-text-secondary);
    }

    .ntf-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--nb-accent);
        align-self: center;
        flex-shrink: 0;
    }

    .ntf-empty {
        text-align: center;
        padding: 30px 14px;
        color: var(--nb-text-secondary);
        font-size: 0.85rem;
    }
</style>

<script>
    (function () {
        // ---------- Profile dropdown ----------
        // Closes the Messenger panel by ID directly (rather than a shared
        // variable) since that panel now lives in its own included file
        // with its own separate script closure.
        const profileToggle = document.getElementById('nbProfileToggle');
        const profileDropdown = document.getElementById('nbProfileDropdown');

        if (profileToggle) {
            profileToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                document.getElementById('nbMessengerPanel')?.classList.remove('show');
                profileDropdown.classList.toggle('show');
            });
        }

        document.addEventListener('click', function () {
            profileDropdown && profileDropdown.classList.remove('show');
        });

        if (profileDropdown) {
            profileDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }


    
    // ---------- Notifications ----------
    const notifToggle = document.getElementById('nbNotifToggle');
    const notifDropdown = document.getElementById('nbNotifDropdown');
    const notifList = document.getElementById('nbNotifList');
    const notifBadge = document.getElementById('nbNotifBadge');
    const markAllBtn = document.getElementById('nbMarkAllReadBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function updateBadge(count) {
        if (count > 0) {
            notifBadge.textContent = count > 99 ? '99+' : count;
            notifBadge.style.display = 'flex';
        } else {
            notifBadge.style.display = 'none';
        }
    }

    function loadNotifDropdown() {
        fetch('{{ route("notifications.dropdown") }}', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                notifList.innerHTML = data.html;
                updateBadge(data.unread_count);
            })
            .catch(() => {});
    }

    if (notifToggle) {
        notifToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('nbMessengerPanel')?.classList.remove('show');
            profileDropdown?.classList.remove('show');
            notifDropdown.classList.toggle('show');
            if (notifDropdown.classList.contains('show')) {
                loadNotifDropdown();
            }
        });

        notifDropdown.addEventListener('click', function (e) {
            e.stopPropagation();

            const item = e.target.closest('.ntf-item');
            if (item && item.classList.contains('ntf-unread')) {
                const id = item.dataset.notificationId;
                item.classList.remove('ntf-unread');
                item.querySelector('.ntf-dot')?.remove();

                fetch(`/notifications/${id}/read`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                }).then(() => {
                    const current = parseInt(notifBadge.textContent) || 0;
                    if (current > 0) updateBadge(current - 1);
                }).catch(() => {});
            }
        });

        markAllBtn.addEventListener('click', function () {
            fetch('{{ route("notifications.read-all") }}', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            }).then(() => {
                document.querySelectorAll('.ntf-unread').forEach(el => {
                    el.classList.remove('ntf-unread');
                    el.querySelector('.ntf-dot')?.remove();
                });
                updateBadge(0);
            }).catch(() => {});
        });

        document.addEventListener('click', function () {
            notifDropdown.classList.remove('show');
        });

        // Initial unread count on page load, then poll every 25s
        loadNotifDropdown();
        setInterval(loadNotifDropdown, 25000);
    }
    })();

</script>