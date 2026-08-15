@php
    $myReaction = $post->isReactedToBy(auth()->user());
    $reactionMeta = [
        'like'  => ['emoji' => '👍', 'label' => __('Like'),  'color' => '#e85d3f'],
        'love'  => ['emoji' => '❤️', 'label' => __('Love'),  'color' => '#e0245e'],
        'haha'  => ['emoji' => '😆', 'label' => __('Haha'),  'color' => '#f7b125'],
        'wow'   => ['emoji' => '😮', 'label' => __('Wow'),   'color' => '#f7b125'],
        'sad'   => ['emoji' => '😢', 'label' => __('Sad'),   'color' => '#f7b125'],
        'angry' => ['emoji' => '😡', 'label' => __('Angry'), 'color' => '#e0245e'],
    ];
    $activeReaction = $myReaction ? $reactionMeta[$myReaction] : null;
    $reactionSummary = $post->likes_count > 0 ? $post->reactionSummary() : collect();
    $isOwner = $post->user_id === auth()->id();
@endphp

<div class="pc-card" data-post-id="{{ $post->uuid }}">
    <div class="pc-header">
        @if ($post->user->profile_photo)
            <img src="{{ $post->user->profile_photo }}" class="pc-avatar" alt="{{ $post->user->first_name }}">
        @else
            <div class="pc-avatar-fallback">
                {{ strtoupper(mb_substr($post->user->first_name, 0, 1)) }}
            </div>
        @endif

        <div class="pc-header-body">
            <a href="{{ route('profile.show', $post->user->uuid) }}" class="pc-author">
                {{ $post->user->first_name }} {{ $post->user->last_name }}
            </a>

            @if (auth()->check() && $post->user_id !== auth()->id())
                @php $isFollowing = $post->user->isFollowedBy(auth()->user()); @endphp
                <form method="POST"
                      action="{{ $isFollowing ? route('follow.destroy', $post->user->uuid) : route('follow.store', $post->user->uuid) }}"
                      class="pc-follow-form">
                    @csrf
                    @if ($isFollowing) @method('DELETE') @endif
                    <button type="submit" class="pc-follow-btn {{ $isFollowing ? 'following' : '' }}">
                        {{ $isFollowing ? __('Following') : __('Follow') }}
                    </button>
                </form>
            @endif

            @if ($post->is_pinned)
                <span class="pc-pinned">📌 {{ __('Pinned') }}</span>
            @endif
            <div class="pc-meta">
                {{ $post->created_at->diffForHumans() }}
                &middot;
                @switch($post->visibility)
                    @case('public') 🌍 {{ __('Public') }} @break
                    @case('friends') 👥 {{ __('Friends') }} @break
                    @case('custom') 🚫 {{ __('Custom') }} @break
                    @case('private') 🔒 {{ __('Only me') }} @break
                @endswitch
                @if ($post->is_edited)
                    &middot; {{ __('Edited') }}
                @endif
            </div>
        </div>

        {{-- ================= OPTIONS MENU (owner only) ================= --}}
        @if ($isOwner)
            <div class="pc-options-wrap">
                <button type="button" class="pc-options-btn" title="{{ __('Post options') }}">
                    <i class="bi bi-three-dots"></i>
                </button>

                <div class="pc-options-dropdown">
                    <button type="button"
                            class="pc-options-item pc-toggle-comments-btn"
                            data-comments-enabled="{{ $post->comments_enabled ? '1' : '0' }}">
                        <i class="bi bi-chat-square-dots"></i>
                        <span class="pc-toggle-comments-label">
                            {{ $post->comments_enabled ? __('Turn off commenting') : __('Turn on commenting') }}
                        </span>
                    </button>

                    <button type="button" class="pc-options-item pc-options-delete">
                        <i class="bi bi-trash-fill"></i>
                        {{ __('Delete post') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div class="pc-body">{{ $post->body }}</div>

    @if ($post->images->isNotEmpty())
        <div class="pc-images pc-images-count-{{ min($post->images->count(), 5) }}">
            @foreach ($post->images->take(5) as $index => $image)
                <div class="pc-image-item" data-full="{{ $image->url }}">
                    <img src="{{ $image->url }}" alt="" loading="lazy">
                    @if ($index === 4 && $post->images->count() > 5)
                        <div class="pc-image-more-overlay">+{{ $post->images->count() - 5 }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($post->videos->isNotEmpty())
        <div class="pc-videos">
            @foreach ($post->videos as $video)
                @php
                    $ext = pathinfo($video->path, PATHINFO_EXTENSION);
                    $mime = match ($ext) {
                        'webm' => 'video/webm',
                        'mov'  => 'video/quicktime',
                        default => 'video/mp4',
                    };
                @endphp
                <video class="pc-video" controls preload="metadata" playsinline>
                    <source src="{{ $video->url() }}" type="{{ $mime }}">
                    {{ __('Your browser does not support the video tag.') }}
                </video>
            @endforeach
        </div>
    @endif

    <div class="pc-actions">
        <div class="pc-reaction-wrap">
            <button type="button"
                    class="pc-action-btn pc-like-btn {{ $activeReaction ? 'is-active' : '' }}"
                    @if ($activeReaction) style="--pc-reaction-color: {{ $activeReaction['color'] }}" @endif>
                <span class="pc-like-emoji"><i class="bi bi-hand-thumbs-up-fill"></i></span>
                <span class="pc-stats-count">{{ $post->likes_count }}</span>
            </button>

            <div class="pc-reaction-picker">
                <button type="button" class="pc-reaction-opt" data-type="like" title="{{ __('Like') }}">👍</button>
                <button type="button" class="pc-reaction-opt" data-type="love" title="{{ __('Love') }}">❤️</button>
                <button type="button" class="pc-reaction-opt" data-type="haha" title="{{ __('Haha') }}">😆</button>
                <button type="button" class="pc-reaction-opt" data-type="wow" title="{{ __('Wow') }}">😮</button>
                <button type="button" class="pc-reaction-opt" data-type="sad" title="{{ __('Sad') }}">😢</button>
                <button type="button" class="pc-reaction-opt" data-type="angry" title="{{ __('Angry') }}">😡</button>
            </div>
        </div>

        <button type="button" class="pc-action-btn pc-comment-toggle-btn">
            <i class="bi bi-chat-fill"></i> <span class="pc-comment-count">{{ $post->comments_count }}</span>
        </button>

        <button type="button" class="pc-action-btn pc-share-btn">
            <i class="bi bi-reply-fill"></i> <span class="pc-share-count">{{ $post->shares_count }}</span>
        </button>

        <div class="pc-reaction-cluster" data-post-uuid="{{ $post->uuid }}" style="margin-left:auto;">
            @foreach ($reactionSummary->take(3) as $r)
                <span class="pc-reaction-badge" data-type="{{ $r['type'] }}">{{ $reactionMeta[$r['type']]['emoji'] }}</span>
            @endforeach
            <div class="pc-reaction-tooltip"></div>
        </div>
    </div>

    <div class="pc-comments-section d-none">
        <form class="pc-comment-composer-form {{ $post->comments_enabled ? '' : 'd-none' }}">
            @if (auth()->user()->profile_photo)
                <img src="{{ auth()->user()->profile_photo }}" class="pc-comment-composer-avatar">
            @else
                <span class="pc-comment-composer-avatar pc-comment-composer-avatar-fallback">
                    {{ strtoupper(mb_substr(auth()->user()->first_name, 0, 1)) }}
                </span>
            @endif
            <input type="text" class="pc-comment-composer-input" placeholder="{{ __('Write a comment...') }}" maxlength="2000">
            <button type="submit" class="pc-comment-send-btn" title="{{ __('Post comment') }}">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>

        <p class="pc-comments-disabled-note {{ $post->comments_enabled ? 'd-none' : '' }}">
            {{ __('Comments are turned off for this post.') }}
        </p>

        <div class="pc-comment-list"></div>
    </div>

    <div class="pc-share-section d-none">
        <form class="pc-share-form">
            <textarea class="pc-share-input" placeholder="{{ __('Add a caption (optional)...') }}" maxlength="2000" rows="2"></textarea>
            <div class="pc-share-form-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary pc-share-cancel">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('Post') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="pc-lightbox-overlay" id="pcLightboxOverlay">
    <button type="button" class="pc-lightbox-close" id="pcLightboxClose">&times;</button>
    <img src="" alt="" id="pcLightboxImg">
</div>

@once
    <style>
        :root {
            --pc-bg: #ffffff;
            --pc-border: #e4e6eb;
            --pc-text: #050505;
            --pc-text-secondary: #65676b;
            --pc-avatar-fallback-bg: #e85d3f;
            --pc-avatar-fallback-text: #ffffff;
            --pc-hover: #f0f2f5;
        }

        [data-theme="dark"] {
            --pc-bg: #242526;
            --pc-border: #3e4042;
            --pc-text: #e4e6eb;
            --pc-text-secondary: #b0b3b8;
            --pc-avatar-fallback-bg: #e85d3f;
            --pc-avatar-fallback-text: #050505;
            --pc-hover: #3a3b3c;
        }

        .pc-images {
            margin-top: 10px;
            display: grid;
            gap: 4px;
            border-radius: 10px;
            overflow: hidden;
        }

        .pc-image-item {
            position: relative;
            overflow: hidden;
            cursor: pointer;
            background: var(--pc-hover);
        }

        .pc-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* 1 image: full width, natural-ish height */
        .pc-images-count-1 {
            grid-template-columns: 1fr;
        }
        .pc-images-count-1 .pc-image-item {
            max-height: 500px;
        }
        .pc-images-count-1 img {
            max-height: 500px;
            object-fit: contain;
        }

        /* 2 images: side by side */
        .pc-images-count-2 {
            grid-template-columns: 1fr 1fr;
        }
        .pc-images-count-2 .pc-image-item {
            aspect-ratio: 1;
        }

        /* 3 images: one big left, two stacked right */
        .pc-images-count-3 {
            grid-template-columns: 2fr 1fr;
            grid-template-rows: 1fr 1fr;
        }
        .pc-images-count-3 .pc-image-item:nth-child(1) {
            grid-row: 1 / span 2;
        }
        .pc-images-count-3 .pc-image-item {
            aspect-ratio: 1;
        }

        /* 4 images: 2x2 grid */
        .pc-images-count-4 {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
        }
        .pc-images-count-4 .pc-image-item {
            aspect-ratio: 1;
        }

        /* 5 images: 2 on top, 3 on bottom (last shows +N overlay if more) */
        .pc-images-count-5 {
            grid-template-columns: repeat(3, 1fr);
        }
        .pc-images-count-5 .pc-image-item:nth-child(1),
        .pc-images-count-5 .pc-image-item:nth-child(2) {
            grid-column: span 1;
        }
        .pc-images-count-5 .pc-image-item {
            aspect-ratio: 1;
        }

        .pc-image-more-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ---- Videos ---- */
        .pc-videos {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .pc-video {
            width: 100%;
            max-height: 500px;
            border-radius: 10px;
            background: #000;
            display: block;
        }

        /* ---- Lightbox ---- */
        .pc-lightbox-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .pc-lightbox-overlay.active {
            display: flex;
        }

        .pc-lightbox-overlay img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 6px;
        }

        .pc-lightbox-close {
            position: absolute;
            top: 20px;
            right: 24px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.4rem;
            line-height: 1;
        }

        .pc-card {
            background: var(--pc-bg);
            border: 1px solid var(--pc-border);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .pc-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .pc-header-body {
            flex: 1;
            min-width: 0;
        }

        .pc-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--pc-avatar-fallback-bg);
        }

        .pc-avatar-fallback {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--pc-avatar-fallback-bg);
            color: var(--pc-avatar-fallback-text) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none !important;
            flex-shrink: 0;
        }

        .pc-author {
            font-weight: 600;
            color: var(--pc-text);
            text-decoration: none;
        }

        .pc-author:hover {
            text-decoration: underline;
            color: var(--pc-text);
        }

        .pc-follow-form {
            display: inline-block;
            margin-left: 8px;
            vertical-align: middle;
        }

        .pc-follow-btn {
            background: none;
            border: 1px solid #e85d3f;
            color: #e85d3f !important;
            border-radius: 14px;
            padding: 2px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .pc-follow-btn:hover {
            background: #e85d3f;
            color: #fff !important;
        }

        .pc-follow-btn.following {
            border-color: var(--pc-border);
            color: var(--pc-text-secondary) !important;
        }

        .pc-follow-btn.following:hover {
            border-color: #dc3545;
            background: none;
            color: #dc3545 !important;
        }

        .pc-pinned {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--pc-avatar-fallback-bg);
            margin-left: 6px;
        }

        .pc-meta {
            font-size: 0.78rem;
            color: var(--pc-text-secondary);
        }

        .pc-body {
            margin-top: 10px;
            font-size: 0.95rem;
            white-space: pre-line;
            color: var(--pc-text);
        }

        /* ---- Options menu (three dots) ---- */
        .pc-options-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .pc-options-btn {
            background: none;
            border: none;
            color: var(--pc-text-secondary);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .pc-options-btn:hover {
            background: var(--pc-hover);
            color: var(--pc-text);
        }

        .pc-options-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 4px;
            background: var(--pc-bg);
            border: 1px solid var(--pc-border);
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            min-width: 210px;
            overflow: hidden;
            z-index: 30;
        }

        .pc-options-dropdown.show {
            display: block;
        }

        .pc-options-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 10px 14px;
            font-size: 0.88rem;
            color: var(--pc-text);
        }

        .pc-options-item:hover {
            background: var(--pc-hover);
        }

        .pc-options-delete {
            color: #dc3545;
        }

        /* ---- Reaction cluster (badges + tooltip) ---- */
        .pc-reaction-cluster {
            position: relative;
            display: flex;
        }

        .pc-reaction-badge {
            font-size: 1.3rem;
            line-height: 1;
            margin-left: -5px;
            background: var(--pc-bg);
            border-radius: 50%;
            padding: 2px;
            cursor: pointer;
        }

        .pc-reaction-badge:first-child {
            margin-left: 0;
        }

        .pc-reaction-tooltip {
            display: none;
            position: absolute;
            bottom: 100%;
            right: 0;
            margin-bottom: 6px;
            background: #1c1e21;
            color: #fff;
            font-size: 0.78rem;
            padding: 6px 10px;
            border-radius: 6px;
            max-width: 240px;
            white-space: normal;
            z-index: 30;
        }

        .pc-reaction-tooltip.show {
            display: block;
        }

        .pc-actions {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-top: 1px solid var(--pc-border);
            padding-top: 10px;
            position: relative;
        }

        .pc-action-btn {
            background: none;
            border: none;
            color: var(--pc-text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pc-action-btn i {
            font-size: 1rem;
        }

        .pc-action-btn:hover {
            color: var(--pc-text);
        }

        /* ---- Reaction picker ---- */
        .pc-reaction-wrap {
            position: relative;
        }

        .pc-like-btn.is-active .pc-like-emoji {
            color: var(--pc-reaction-color, #e85d3f);
        }

        .pc-reaction-picker {
            display: none;
            position: absolute;
            bottom: 100%;
            left: 0;
            margin-bottom: 8px;
            background: var(--pc-bg);
            border: 1px solid var(--pc-border);
            border-radius: 30px;
            padding: 6px 8px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.18);
            gap: 4px;
            z-index: 20;
        }

        .pc-reaction-picker.show {
            display: flex;
        }

        .pc-reaction-opt {
            background: none;
            border: none;
            font-size: 1.4rem;
            line-height: 1;
            padding: 4px;
            border-radius: 50%;
            transition: transform 0.1s ease;
        }

        .pc-reaction-opt:hover {
            transform: scale(1.3);
            background: var(--pc-hover);
        }

        /* ---- Comments section ---- */
        .pc-comments-section {
            margin-top: 12px;
            border-top: 1px solid var(--pc-border);
            padding-top: 12px;
        }

        .pc-comments-disabled-note {
            font-size: 0.82rem;
            color: var(--pc-text-secondary);
            font-style: italic;
            margin-bottom: 8px;
        }

        .pc-comment-composer-form {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .pc-comment-composer-avatar,
        .pc-comment-composer-avatar-fallback {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .pc-comment-composer-avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--pc-avatar-fallback-bg);
            color: var(--pc-avatar-fallback-text) !important;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .pc-comment-composer-input {
            flex: 1;
            background: var(--pc-hover);
            border: none;
            border-radius: 18px;
            padding: 8px 14px;
            font-size: 0.88rem;
            color: var(--pc-text);
        }

        .pc-comment-composer-input:focus {
            outline: none;
        }

        .pc-comment-send-btn,
        .cm-reply-send-btn {
            background: none;
            border: none;
            color: #e85d3f;
            font-size: 1.05rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            padding: 4px;
        }

        .pc-comment-send-btn:hover,
        .cm-reply-send-btn:hover {
            color: #0a58ca;
        }

        .cm-reply-form {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cm-reply-form .cm-reply-input {
            flex: 1;
        }

        /* ---- Share box ---- */
        .pc-share-section {
            margin-top: 12px;
            border-top: 1px solid var(--pc-border);
            padding-top: 12px;
        }

        .pc-share-input {
            width: 100%;
            background: var(--pc-hover);
            border: none;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.88rem;
            color: var(--pc-text);
            resize: vertical;
        }

        .pc-share-input:focus {
            outline: none;
        }

        .pc-share-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
        }

        /* ---- Comment items (shared with posts.partials.comment) ---- */
        .cm-item {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .cm-avatar,
        .cm-avatar-fallback {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .cm-avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--pc-avatar-fallback-bg);
            color: var(--pc-avatar-fallback-text) !important;
            font-weight: 700;
            font-size: 0.75rem;
            text-decoration: none;
        }

        .cm-body-wrap {
            flex: 1;
            min-width: 0;
        }

        .cm-bubble {
            background: var(--pc-hover);
            border-radius: 14px;
            padding: 6px 12px;
            display: inline-block;
            max-width: 100%;
        }

        .cm-deleted-bubble {
            color: var(--pc-text-secondary);
        }

        .cm-author {
            display: block;
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--pc-text);
            text-decoration: none;
        }

        .cm-text {
            font-size: 0.86rem;
            color: var(--pc-text);
            word-break: break-word;
        }

        .cm-actions {
            display: flex;
            gap: 12px;
            margin-top: 3px;
            padding-left: 12px;
            font-size: 0.75rem;
            color: var(--pc-text-secondary);
        }

        .cm-actions button {
            background: none;
            border: none;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--pc-text-secondary);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .cm-like-btn i {
            font-size: 0.85rem;
        }

        .cm-like-btn.liked {
            color: #e85d3f;
        }

        .cm-delete-btn:hover {
            color: #dc3545;
        }

        .cm-replies {
            margin-top: 8px;
            padding-left: 24px;
        }

        .cm-reply-form {
            margin-top: 6px;
            padding-left: 24px;
        }

        .cm-reply-form.d-none {
            display: none;
        }

        .cm-reply-input {
            width: 100%;
            background: var(--pc-hover);
            border: none;
            border-radius: 14px;
            padding: 6px 12px;
            font-size: 0.82rem;
            color: var(--pc-text);
        }

        .cm-reply-input:focus {
            outline: none;
        }
    </style>

    <script>
        // ---- Image lightbox ----
        const lightbox = document.getElementById('pcLightboxOverlay');
        const lightboxImg = document.getElementById('pcLightboxImg');
        const lightboxClose = document.getElementById('pcLightboxClose');

        document.addEventListener('click', function (e) {
        const item = e.target.closest('.pc-image-item');
        if (item) {
            lightboxImg.src = item.dataset.full;
            lightbox.classList.add('active');
        }
        });

        lightboxClose.addEventListener('click', () => lightbox.classList.remove('active'));
        lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) lightbox.classList.remove('active');
        });
        document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') lightbox.classList.remove('active');
        });

        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const REACTIONS = {
                like:  { emoji: '👍', label: '{{ __('Like') }}',  color: '#e85d3f' },
                love:  { emoji: '❤️', label: '{{ __('Love') }}',  color: '#e0245e' },
                haha:  { emoji: '😆', label: '{{ __('Haha') }}',  color: '#f7b125' },
                wow:   { emoji: '😮', label: '{{ __('Wow') }}',   color: '#f7b125' },
                sad:   { emoji: '😢', label: '{{ __('Sad') }}',   color: '#f7b125' },
                angry: { emoji: '😡', label: '{{ __('Angry') }}', color: '#e0245e' },
            };

            function jsonFetch(url, options = {}) {
                return fetch(url, {
                    ...options,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        ...options.headers,
                    },
                }).then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        console.error('Request failed:', url, res.status, data);
                        throw new Error(data.message || 'Request failed');
                    }
                    return data;
                });
            }

            function applyReactionUI(likeBtn, type) {
                if (type) {
                    const meta = REACTIONS[type] || REACTIONS.like;
                    likeBtn.classList.add('is-active');
                    likeBtn.style.setProperty('--pc-reaction-color', meta.color);
                } else {
                    likeBtn.classList.remove('is-active');
                    likeBtn.style.removeProperty('--pc-reaction-color');
                }
            }

            function sendReaction(card, type) {
                const uuid = card.dataset.postId;
                const likeBtn = card.querySelector('.pc-like-btn');
                const countEl = card.querySelector('.pc-stats-count');

                jsonFetch(`/posts/${uuid}/react`, {
                    method: 'POST',
                    body: JSON.stringify(type ? { type } : {}),
                }).then((data) => {
                    applyReactionUI(likeBtn, data.current_reaction);
                    countEl.textContent = data.likes_count;
                }).catch(() => {});
            }

            function closeAllPickers(except) {
                document.querySelectorAll('.pc-reaction-picker.show').forEach((p) => {
                    if (p !== except) p.classList.remove('show');
                });
            }

            function closeAllOptionsMenus(except) {
                document.querySelectorAll('.pc-options-dropdown.show').forEach((d) => {
                    if (d !== except) d.classList.remove('show');
                });
            }

            document.addEventListener('contextmenu', function (e) {
                const likeBtn = e.target.closest('.pc-like-btn');
                if (!likeBtn) return;

                e.preventDefault();
                const picker = likeBtn.closest('.pc-reaction-wrap').querySelector('.pc-reaction-picker');
                closeAllPickers(picker);
                picker.classList.toggle('show');
            });

            let pressTimer;
            document.addEventListener('touchstart', function (e) {
                const btn = e.target.closest('.pc-like-btn');
                if (!btn) return;
                const picker = btn.closest('.pc-reaction-wrap').querySelector('.pc-reaction-picker');
                pressTimer = setTimeout(() => {
                    closeAllPickers(picker);
                    picker.classList.add('show');
                }, 400);
            });
            document.addEventListener('touchend', function () {
                clearTimeout(pressTimer);
            });

            const reactorsCache = new Map();

            function loadReactors(postUuid) {
                if (reactorsCache.has(postUuid)) {
                    return Promise.resolve(reactorsCache.get(postUuid));
                }
                return jsonFetch(`/posts/${postUuid}/reactors`, { method: 'GET' })
                    .then((data) => {
                        reactorsCache.set(postUuid, data.reactors);
                        return data.reactors;
                    });
            }

            document.addEventListener('mouseenter', function (e) {
                const badge = e.target.closest && e.target.closest('.pc-reaction-badge');
                if (!badge) return;

                const cluster = badge.closest('.pc-reaction-cluster');
                const tooltip = cluster.querySelector('.pc-reaction-tooltip');
                const type = badge.dataset.type;

                tooltip.textContent = '{{ __('Loading...') }}';
                tooltip.classList.add('show');

                loadReactors(cluster.dataset.postUuid).then((reactors) => {
                    const names = reactors.filter((r) => r.type === type).map((r) => r.name);
                    tooltip.textContent = names.length ? names.join(', ') : '';
                }).catch(() => {});
            }, true);

            document.addEventListener('mouseleave', function (e) {
                const badge = e.target.closest && e.target.closest('.pc-reaction-badge');
                if (!badge) return;
                badge.closest('.pc-reaction-cluster').querySelector('.pc-reaction-tooltip').classList.remove('show');
            }, true);

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.pc-reaction-picker') && !e.target.closest('.pc-like-btn')) {
                    closeAllPickers(null);
                }

                if (!e.target.closest('.pc-options-wrap')) {
                    closeAllOptionsMenus(null);
                }

                // ---------- Options menu toggle (three dots) ----------
                const optionsBtn = e.target.closest('.pc-options-btn');
                if (optionsBtn) {
                    const dropdown = optionsBtn.closest('.pc-options-wrap').querySelector('.pc-options-dropdown');
                    closeAllOptionsMenus(dropdown);
                    dropdown.classList.toggle('show');
                    return;
                }

                // ---------- Toggle commenting on/off ----------
                const toggleCommentsBtn = e.target.closest('.pc-toggle-comments-btn');
                if (toggleCommentsBtn) {
                    const card = toggleCommentsBtn.closest('.pc-card');
                    const uuid = card.dataset.postId;
                    const label = toggleCommentsBtn.querySelector('.pc-toggle-comments-label');
                    const composerForm = card.querySelector('.pc-comment-composer-form');
                    const disabledNote = card.querySelector('.pc-comments-disabled-note');

                    jsonFetch(`/posts/${uuid}/toggle-comments`, { method: 'PATCH' }).then((data) => {
                        toggleCommentsBtn.dataset.commentsEnabled = data.comments_enabled ? '1' : '0';
                        label.textContent = data.comments_enabled
                            ? '{{ __('Turn off commenting') }}'
                            : '{{ __('Turn on commenting') }}';
                        composerForm.classList.toggle('d-none', !data.comments_enabled);
                        disabledNote.classList.toggle('d-none', data.comments_enabled);
                    }).catch(() => {});

                    closeAllOptionsMenus(null);
                    return;
                }

                // ---------- Delete post ----------
                const deleteBtn = e.target.closest('.pc-options-delete');
                if (deleteBtn) {
                    if (!confirm('{{ __('Delete this post? This cannot be undone.') }}')) return;
                    const card = deleteBtn.closest('.pc-card');
                    const uuid = card.dataset.postId;

                    jsonFetch(`/posts/${uuid}`, { method: 'DELETE' }).then(() => {
                        card.remove();
                    }).catch(() => {});
                    return;
                }

                const opt = e.target.closest('.pc-reaction-opt');
                if (opt) {
                    const card = opt.closest('.pc-card');
                    sendReaction(card, opt.dataset.type);
                    opt.closest('.pc-reaction-picker').classList.remove('show');
                    return;
                }

                const likeBtn = e.target.closest('.pc-like-btn');
                if (likeBtn) {
                    sendReaction(likeBtn.closest('.pc-card'), null);
                    return;
                }

                const commentToggle = e.target.closest('.pc-comment-toggle-btn');
                if (commentToggle) {
                    const card = commentToggle.closest('.pc-card');
                    const section = card.querySelector('.pc-comments-section');
                    const list = section.querySelector('.pc-comment-list');

                    section.classList.toggle('d-none');

                    if (!section.dataset.loaded && !section.classList.contains('d-none')) {
                        section.dataset.loaded = '1';
                        jsonFetch(`/posts/${card.dataset.postId}/comments`, { method: 'GET' })
                            .then((data) => { list.innerHTML = data.html; })
                            .catch(() => {});
                    }
                    return;
                }

                const cmLikeBtn = e.target.closest('.cm-like-btn');
                if (cmLikeBtn) {
                    const id = cmLikeBtn.dataset.commentId;
                    jsonFetch(`/comments/${id}/react`, { method: 'POST', body: '{}' })
                        .then((data) => {
                            cmLikeBtn.classList.toggle('liked', data.liked);
                            cmLikeBtn.querySelector('.cm-like-count').textContent = data.likes_count;
                        }).catch(() => {});
                    return;
                }

                const cmDeleteBtn = e.target.closest('.cm-delete-btn');
                if (cmDeleteBtn) {
                    if (!confirm('{{ __('Delete this comment?') }}')) return;
                    const id = cmDeleteBtn.dataset.commentId;
                    const item = cmDeleteBtn.closest('.cm-item');
                    const card = cmDeleteBtn.closest('.pc-card');

                    jsonFetch(`/comments/${id}`, { method: 'DELETE' }).then((data) => {
                        const bubble = item.querySelector(':scope > .cm-body-wrap > .cm-bubble');
                        const actions = item.querySelector(':scope > .cm-body-wrap > .cm-actions');
                        const avatarLink = item.querySelector(':scope > .cm-avatar-link');

                        if (bubble) bubble.outerHTML = `<div class="cm-bubble cm-deleted-bubble"><em>{{ __('This comment was deleted.') }}</em></div>`;
                        if (actions) actions.remove();
                        if (avatarLink) avatarLink.outerHTML = `<span class="cm-avatar cm-avatar-fallback">?</span>`;

                        card.querySelector('.pc-comment-count').textContent = data.comments_count;
                    }).catch(() => {});
                    return;
                }

                const replyBtn = e.target.closest('.cm-reply-btn');
                if (replyBtn) {
                    const id = replyBtn.dataset.commentId;
                    const form = document.getElementById(`cmReplyForm${id}`);
                    if (!form) return;
                    form.classList.toggle('d-none');
                    if (!form.classList.contains('d-none')) {
                        const input = form.querySelector('.cm-reply-input');
                        input.placeholder = `{{ __('Reply to') }} ${replyBtn.dataset.authorName}...`;
                        input.focus();
                    }
                    return;
                }

                const shareBtn = e.target.closest('.pc-share-btn');
                if (shareBtn) {
                    const card = shareBtn.closest('.pc-card');
                    const section = card.querySelector('.pc-share-section');
                    section.classList.toggle('d-none');
                    if (!section.classList.contains('d-none')) {
                        section.querySelector('.pc-share-input').focus();
                    }
                    return;
                }

                const shareCancel = e.target.closest('.pc-share-cancel');
                if (shareCancel) {
                    const section = shareCancel.closest('.pc-share-section');
                    section.classList.add('d-none');
                    section.querySelector('.pc-share-input').value = '';
                    return;
                }
            });

            document.addEventListener('submit', function (e) {
                const commentForm = e.target.closest('.pc-comment-composer-form');
                if (commentForm) {
                    e.preventDefault();
                    const input = commentForm.querySelector('.pc-comment-composer-input');
                    const body = input.value.trim();
                    if (!body) return;
                    const card = commentForm.closest('.pc-card');

                    jsonFetch(`/posts/${card.dataset.postId}/comments`, {
                        method: 'POST',
                        body: JSON.stringify({ body }),
                    }).then((data) => {
                        input.value = '';
                        card.querySelector('.pc-comment-list').insertAdjacentHTML('beforeend', data.html);
                        card.querySelector('.pc-comment-count').textContent = data.comments_count;
                    }).catch(() => {});
                    return;
                }

                const replyForm = e.target.closest('.cm-reply-form');
                if (replyForm) {
                    e.preventDefault();
                    const input = replyForm.querySelector('.cm-reply-input');
                    const body = input.value.trim();
                    if (!body) return;

                    const postUuid = replyForm.dataset.postUuid;
                    const parentId = replyForm.dataset.parentId;
                    const card = replyForm.closest('.pc-card');

                    jsonFetch(`/posts/${postUuid}/comments`, {
                        method: 'POST',
                        body: JSON.stringify({ body, parent_id: parentId }),
                    }).then((data) => {
                        input.value = '';
                        replyForm.classList.add('d-none');
                        document.getElementById(`cmReplies${parentId}`)
                            .insertAdjacentHTML('beforeend', data.html);
                        card.querySelector('.pc-comment-count').textContent = data.comments_count;
                    }).catch(() => {});
                    return;
                }

                const shareForm = e.target.closest('.pc-share-form');
                if (shareForm) {
                    e.preventDefault();
                    const textarea = shareForm.querySelector('.pc-share-input');
                    const caption = textarea.value.trim();
                    const card = shareForm.closest('.pc-card');
                    const section = shareForm.closest('.pc-share-section');

                    jsonFetch(`/posts/${card.dataset.postId}/share`, {
                        method: 'POST',
                        body: JSON.stringify({ caption: caption || null }),
                    }).then((data) => {
                        textarea.value = '';
                        section.classList.add('d-none');
                        card.querySelector('.pc-share-count').textContent = data.shares_count;
                        document.dispatchEvent(new CustomEvent('post:created', {
                            detail: { html: data.html },
                        }));
                    }).catch(() => {});
                }
            });
        })();
    </script>
@endonce