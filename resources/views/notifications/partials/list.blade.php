@forelse ($notifications as $notification)
    @php
        $d = $notification->data;
        $icon = match($d['type'] ?? null) {
            'friend_request_received' => 'bi-person-plus-fill',
            'friend_request_accepted' => 'bi-person-check-fill',
            'post_reacted' => 'bi-hand-thumbs-up-fill',
            'post_commented', 'comment_replied' => 'bi-chat-fill',
            default => 'bi-bell-fill',
        };
    @endphp

    <div class="ntf-item {{ $notification->read_at ? '' : 'ntf-unread' }}" data-notification-id="{{ $notification->id }}">
        <a href="{{ isset($d['actor_uuid']) ? route('profile.show', $d['actor_uuid']) : '#' }}" class="ntf-avatar-link">
            @if (!empty($d['actor_photo']))
                <img src="{{ $d['actor_photo'] }}" class="ntf-avatar">
            @else
                <span class="ntf-avatar ntf-avatar-fallback">
                    {{ strtoupper(substr($d['actor_name'] ?? '?', 0, 1)) }}
                </span>
            @endif
            <span class="ntf-icon-badge"><i class="bi {{ $icon }}"></i></span>
        </a>

        <div class="ntf-body">
            <p class="ntf-message">{{ $d['message'] ?? __('New notification') }}</p>
            @if (!empty($d['excerpt']))
                <p class="ntf-excerpt">"{{ $d['excerpt'] }}"</p>
            @endif
            <span class="ntf-time">{{ $notification->created_at->diffForHumans() }}</span>
        </div>

        @unless ($notification->read_at)
            <span class="ntf-dot"></span>
        @endunless
    </div>
@empty
    <div class="ntf-empty">{{ __("You're all caught up.") }}</div>
@endforelse