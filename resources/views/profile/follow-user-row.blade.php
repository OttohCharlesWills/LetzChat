@php
    // Whether the CURRENT VIEWER (not the profile owner) follows this
    // listed person — determines whether the button says Follow or
    // Following. Computed per-row for now; fine at this list size, but
    // worth batching into a single query later if these lists grow large.
    $viewerFollowsThisUser = auth()->check() && $listedUser->id !== auth()->id()
        ? $listedUser->isFollowedBy(auth()->user())
        : null;
@endphp

<div class="fw-row">
    <a href="{{ route('profile.show', $listedUser->username) }}" class="fw-row-link">
        @if ($listedUser->profile_photo)
            <img src="{{ $listedUser->profile_photo }}" class="fw-avatar" alt="{{ $listedUser->first_name }}">
        @else
            <span class="fw-avatar fw-avatar-fallback">
                {{ strtoupper(substr($listedUser->first_name, 0, 1)) }}
            </span>
        @endif

        <span class="fw-row-body">
            <span class="fw-name">{{ $listedUser->first_name }} {{ $listedUser->last_name }}</span>
            <span class="fw-username">{{ '@' . $listedUser->username }}</span>
        </span>
    </a>

    @if ($viewerFollowsThisUser !== null)
        <form method="POST"
              action="{{ $viewerFollowsThisUser ? route('follow.destroy', $listedUser->uuid) : route('follow.store', $listedUser->uuid) }}"
              class="fw-follow-form">
            @csrf
            @if ($viewerFollowsThisUser) @method('DELETE') @endif
            <button type="submit" class="fw-btn {{ $viewerFollowsThisUser ? 'fw-btn-following' : 'fw-btn-follow' }}">
                {{ $viewerFollowsThisUser ? __('Following') : __('Follow') }}
            </button>
        </form>
    @endif
</div>

@once
    <style>
        .fw-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 4px;
            border-bottom: 1px solid var(--sb-border);
        }

        .fw-row:last-child {
            border-bottom: none;
        }

        .fw-row-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex: 1;
            min-width: 0;
        }

        .fw-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--sb-avatar-fallback-bg);
        }

        .fw-avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sb-avatar-fallback-text) !important;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none !important;
        }

        .fw-row-body {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .fw-name {
            font-weight: 600;
            color: var(--sb-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fw-username {
            font-size: 0.82rem;
            color: var(--sb-text-secondary);
        }

        .fw-follow-form {
            flex-shrink: 0;
        }

        .fw-btn {
            border-radius: 6px;
            padding: 7px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .fw-btn-follow {
            background: var(--sb-avatar-fallback-bg);
            color: var(--sb-avatar-fallback-text);
        }

        .fw-btn-follow:hover {
            opacity: 0.9;
        }

        .fw-btn-following {
            background: var(--sb-hover);
            color: var(--sb-text);
            border-color: var(--sb-border);
        }

        .fw-btn-following:hover {
            background: var(--sb-border);
        }
    </style>
@endonce