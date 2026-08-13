@extends('layouts.group')

@section('content')
<div class="gr-page">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <div class="gr-layout">

        {{-- ================= LEFT: SIDEBAR ================= --}}
        <div class="gr-sidebar">
            <div class="gr-sidebar-header">
                <span class="gr-sidebar-title">{{ __('Groups') }}</span>
                <a href="#" class="gr-sidebar-settings" title="{{ __('Settings') }}">
                    <i class="bi bi-gear-fill"></i>
                </a>
            </div>

            <div class="gr-search-wrap">
                <i class="bi bi-search gr-search-icon"></i>
                <input type="text" id="grSearchInput" class="gr-search-input" placeholder="{{ __('Search groups') }}">
            </div>

            <nav class="gr-nav">
                <a href="{{ route('groups.index', ['tab' => 'feed']) }}" class="gr-nav-item {{ $tab === 'feed' ? 'active' : '' }}">
                    <i class="bi bi-collection-fill"></i> {{ __('Your feed') }}
                </a>
                <a href="{{ route('groups.index', ['tab' => 'discover']) }}" class="gr-nav-item {{ $tab === 'discover' ? 'active' : '' }}">
                    <i class="bi bi-compass-fill"></i> {{ __('Discover') }}
                </a>
                <a href="{{ route('groups.index', ['tab' => 'mine']) }}" class="gr-nav-item {{ $tab === 'mine' ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> {{ __('Your groups') }}
                </a>
            </nav>

            <a href="{{ route('groups.create') }}" class="btn btn-primary gr-create-btn">
                <i class="bi bi-plus-lg"></i> {{ __('Create New Group') }}
            </a>

            @if ($managedGroups->isNotEmpty())
                <div class="gr-managed-section">
                    <div class="gr-managed-title">{{ __('Groups you manage') }}</div>

                    @foreach ($managedGroups as $group)
                        <a href="{{ route('groups.show', $group->uuid) }}" class="gr-managed-item">
                            @if ($group->cover_photo)
                                <img src="{{ $group->cover_photo }}" class="gr-managed-thumb">
                            @else
                                <span class="gr-managed-thumb gr-managed-thumb-fallback">
                                    {{ strtoupper(substr($group->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="gr-managed-body">
                                <span class="gr-managed-name">{{ $group->name }}</span>
                                <span class="gr-managed-meta">{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ================= RIGHT: CONTENT ================= --}}
        <div class="gr-content">

            @if ($tab === 'discover')
                <div class="gr-card">
                    <div class="gr-card-title">{{ __('Discover groups') }}</div>

                    @if ($discoverGroups->isEmpty())
                        <p class="text-muted mb-0">{{ __('No public groups to discover right now.') }}</p>
                    @else
                        <div class="gr-discover-grid">
                            @foreach ($discoverGroups as $group)
                                <div class="gr-discover-tile">
                                    <div class="gr-discover-cover">
                                        @if ($group->cover_photo)
                                            <img src="{{ $group->cover_photo }}">
                                        @endif
                                    </div>
                                    <div class="gr-discover-body">
                                        <a href="{{ route('groups.show', $group->uuid) }}" class="gr-discover-name">{{ $group->name }}</a>
                                        <div class="gr-discover-meta">{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }} &middot; {{ ucfirst($group->privacy) }}</div>

                                        <form method="POST" action="{{ route('groups.join', $group->uuid) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">{{ __('Join') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">{{ $discoverGroups->links() }}</div>
                    @endif
                </div>
            @elseif ($tab === 'mine')
                <div class="gr-card">
                    <div class="gr-card-title">{{ __('Your groups') }} ({{ $myGroups->count() }})</div>

                    @if ($myGroups->isEmpty())
                        <p class="text-muted mb-0">{{ __("You haven't joined any groups yet.") }}</p>
                    @else
                        <div class="gr-discover-grid">
                            @foreach ($myGroups as $group)
                                <div class="gr-discover-tile">
                                    <div class="gr-discover-cover">
                                        @if ($group->cover_photo)
                                            <img src="{{ $group->cover_photo}}">
                                        @endif
                                    </div>
                                    <div class="gr-discover-body">
                                        <a href="{{ route('groups.show', $group->uuid) }}" class="gr-discover-name">{{ $group->name }}</a>
                                        <div class="gr-discover-meta">{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="gr-card">
                    <div class="gr-card-title">{{ __('Recent activity') }}</div>

                    @if ($posts->isEmpty())
                        <p class="text-muted mb-0">
                            {{ __("No recent activity. Join a group to see posts here, or") }}
                            <a href="{{ route('groups.index', ['tab' => 'discover']) }}">{{ __('discover groups') }}</a>.
                        </p>
                    @else
                        @foreach ($posts as $post)
                            <div class="gr-activity-source">
                                <a href="{{ route('groups.show', $post->group->uuid) }}">{{ $post->group->name }}</a>
                            </div>
                            
                            @include('posts.partials.post-card', ['post' => $post])
                        @endforeach

                        <div class="mt-2">{{ $posts->links() }}</div>
                    @endif
                </div>
            @endif

        </div>

    </div>
</div>

<style>
    :root {
        --gr-bg: #ffffff;
        --gr-text: #050505;
        --gr-text-secondary: #65676b;
        --gr-border: #e4e6eb;
        --gr-hover: #f0f2f5;
        --gr-active-bg: #e7f3ff;
        --gr-active-text: #0d6efd;
        --gr-search-bg: #f0f2f5;
        --gr-avatar-fallback-bg: #0d6efd;
        --gr-avatar-fallback-text: #ffffff;
    }

    [data-theme="dark"] {
        --gr-bg: #242526;
        --gr-text: #e4e6eb;
        --gr-text-secondary: #b0b3b8;
        --gr-border: #3e4042;
        --gr-hover: #3a3b3c;
        --gr-active-bg: #263951;
        --gr-active-text: #4599ff;
        --gr-search-bg: #3a3b3c;
        --gr-avatar-fallback-bg: #4599ff;
        --gr-avatar-fallback-text: #050505;
    }

    .gr-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 16px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .gr-layout {
            grid-template-columns: 1fr;
        }
    }

    .gr-sidebar {
        background: var(--gr-bg);
        border-radius: 10px;
        padding: 16px;
    }

    .gr-sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .gr-sidebar-title {
        font-weight: 800;
        font-size: 1.4rem;
        color: var(--gr-text);
    }

    .gr-sidebar-settings {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--gr-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gr-text-secondary);
    }

    .gr-search-wrap {
        position: relative;
        margin-bottom: 14px;
    }

    .gr-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gr-text-secondary);
        font-size: 0.85rem;
    }

    .gr-search-input {
        width: 100%;
        background: var(--gr-search-bg);
        border: none;
        border-radius: 20px;
        padding: 8px 12px 8px 34px;
        font-size: 0.9rem;
        color: var(--gr-text);
    }

    .gr-search-input:focus {
        outline: none;
    }

    .gr-nav {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-bottom: 14px;
    }

    .gr-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        border-radius: 8px;
        text-decoration: none;
        color: var(--gr-text);
        font-weight: 600;
        font-size: 0.92rem;
    }

    .gr-nav-item:hover {
        background: var(--gr-hover);
        color: var(--gr-text);
    }

    .gr-nav-item.active {
        background: var(--gr-active-bg);
        color: var(--gr-active-text);
    }

    .gr-create-btn {
        width: 100%;
        margin-bottom: 16px;
    }

    .gr-managed-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--gr-text);
        padding: 6px 0 10px 0;
        border-top: 1px solid var(--gr-border);
        margin-top: 4px;
    }

    .gr-managed-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px;
        border-radius: 8px;
        text-decoration: none;
    }

    .gr-managed-item:hover {
        background: var(--gr-hover);
    }

    .gr-managed-thumb {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .gr-managed-thumb-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gr-avatar-fallback-bg);
        color: var(--gr-avatar-fallback-text);
        font-weight: 700;
    }

    .gr-managed-body {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .gr-managed-name {
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--gr-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gr-managed-meta {
        font-size: 0.76rem;
        color: var(--gr-text-secondary);
    }

    .gr-card {
        background: var(--gr-bg);
        border-radius: 10px;
        padding: 16px;
    }

    .gr-card-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--gr-text);
        margin-bottom: 12px;
    }

    .gr-activity-source {
        font-size: 0.8rem;
        color: var(--gr-text-secondary);
        margin-bottom: 6px;
        font-weight: 600;
    }

    .gr-activity-source a {
        color: var(--gr-active-text);
        text-decoration: none;
    }

    .gr-discover-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }

    .gr-discover-tile {
        border: 1px solid var(--gr-border);
        border-radius: 10px;
        overflow: hidden;
    }

    .gr-discover-cover {
        height: 90px;
        background: var(--gr-hover);
    }

    .gr-discover-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gr-discover-body {
        padding: 10px;
    }

    .gr-discover-name {
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--gr-text);
        text-decoration: none;
        display: block;
    }

    .gr-discover-meta {
        font-size: 0.78rem;
        color: var(--gr-text-secondary);
        margin-top: 2px;
    }
</style>

<script>
    (function () {
        const searchInput = document.getElementById('grSearchInput');
        const items = Array.from(document.querySelectorAll('.gr-managed-item, .gr-discover-tile'));

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                items.forEach((item) => {
                    const name = item.querySelector('.gr-managed-name, .gr-discover-name')?.textContent.toLowerCase() ?? '';
                    item.style.display = name.includes(term) ? '' : 'none';
                });
            });
        }
    })();
</script>
@endsection