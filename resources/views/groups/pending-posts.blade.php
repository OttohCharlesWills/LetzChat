@extends('layouts.grouplist')

@section('content')
<div class="">
    <div class="pp-header">
        <a href="{{ route('groups.show', $group) }}" class="pp-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="pp-eyebrow">{{ $group->name }}</div>
            <div class="pp-title">{{ __('Pending Posts') }}</div>
        </div>
    </div>

    <div class="pp-list" id="ppList">
        @forelse ($pendingPosts as $post)
            <div class="pp-card" data-post-uuid="{{ $post->uuid }}">
                <div class="pp-card-header">
                    @if ($post->user->profile_photo)
                        <img src="{{ $post->user->profile_photo }}" class="pp-avatar" alt="{{ $post->user->first_name }}">
                    @else
                        <div class="pp-avatar-fallback">
                            {{ strtoupper(mb_substr($post->user->first_name, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <div class="pp-author">{{ $post->user->first_name }} {{ $post->user->last_name }}</div>
                        <div class="pp-meta">{{ $post->created_at->diffForHumans() }} &middot; {{ __('Awaiting review') }}</div>
                    </div>
                </div>

                @if ($post->body)
                    <div class="pp-body">{{ $post->body }}</div>
                @endif

                @if ($post->images->isNotEmpty())
                    <div class="pp-images pp-images-count-{{ min($post->images->count(), 5) }}">
                        @foreach ($post->images->take(5) as $index => $image)
                            <div class="pp-image-item">
                                <img src="{{ $image->url }}" alt="" loading="lazy">
                                @if ($index === 4 && $post->images->count() > 5)
                                    <div class="pp-image-more-overlay">+{{ $post->images->count() - 5 }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($post->videos->isNotEmpty())
                    <div class="pp-videos">
                        @foreach ($post->videos as $video)
                            <video src="{{ $video->url() }}" controls class="pp-video"></video>
                        @endforeach
                    </div>
                @endif

                <div class="pp-actions">
                    <button type="button" class="pp-btn pp-btn-reject" data-action="reject">
                        <i class="bi bi-x-lg"></i> {{ __('Reject') }}
                    </button>
                    <button type="button" class="pp-btn pp-btn-approve" data-action="approve">
                        <i class="bi bi-check-lg"></i> {{ __('Approve') }}
                    </button>
                </div>
            </div>
        @empty
            <div class="pp-empty">
                <i class="bi bi-inbox"></i>
                <p>{{ __('No posts waiting for review.') }}</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .pp-page {
        max-width: 640px;
        margin: 24px auto;
    }

    .pp-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .pp-back {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--pc-hover, #f0f2f5);
        color: var(--pc-text, #050505);
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        flex-shrink: 0;
    }

    .pp-eyebrow {
        font-size: 0.78rem;
        color: var(--pc-text-secondary, #65676b);
        font-weight: 600;
    }

    .pp-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--pc-text, #050505);
    }

    .pp-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .pp-card {
        background: var(--pc-bg, #fff);
        border: 1px solid var(--pc-border, #e4e6eb);
        border-radius: 10px;
        padding: 16px;
    }

    .pp-card.pp-removed {
        opacity: 0;
        transform: scale(0.97);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .pp-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .pp-avatar,
    .pp-avatar-fallback {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .pp-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--pc-avatar-fallback-bg, #e85d3f);
        color: var(--pc-avatar-fallback-text, #fff) !important;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .pp-author {
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--pc-text, #050505);
    }

    .pp-meta {
        font-size: 0.78rem;
        color: var(--pc-text-secondary, #65676b);
    }

    .pp-body {
        font-size: 0.95rem;
        color: var(--pc-text, #050505);
        white-space: pre-line;
        margin-bottom: 8px;
    }

    .pp-images {
        display: grid;
        gap: 4px;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .pp-image-item {
        position: relative;
        overflow: hidden;
        background: var(--pc-hover, #f0f2f5);
    }

    .pp-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pp-images-count-1 { grid-template-columns: 1fr; }
    .pp-images-count-1 .pp-image-item { max-height: 420px; }
    .pp-images-count-1 img { max-height: 420px; object-fit: contain; }

    .pp-images-count-2 { grid-template-columns: 1fr 1fr; }
    .pp-images-count-2 .pp-image-item { aspect-ratio: 1; }

    .pp-images-count-3 { grid-template-columns: 2fr 1fr; grid-template-rows: 1fr 1fr; }
    .pp-images-count-3 .pp-image-item:nth-child(1) { grid-row: 1 / span 2; }
    .pp-images-count-3 .pp-image-item { aspect-ratio: 1; }

    .pp-images-count-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
    .pp-images-count-4 .pp-image-item { aspect-ratio: 1; }

    .pp-images-count-5 { grid-template-columns: repeat(3, 1fr); }
    .pp-images-count-5 .pp-image-item { aspect-ratio: 1; }

    .pp-image-more-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        font-size: 1.4rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pp-videos {
        margin-bottom: 10px;
    }

    .pp-video {
        width: 100%;
        max-height: 400px;
        border-radius: 8px;
        background: #000;
        display: block;
    }

    .pp-actions {
        display: flex;
        gap: 10px;
        border-top: 1px solid var(--pc-border, #e4e6eb);
        padding-top: 12px;
        margin-top: 4px;
    }

    .pp-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: none;
        border-radius: 8px;
        padding: 9px 0;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
    }

    .pp-btn-reject {
        background: var(--pc-hover, #f0f2f5);
        color: #dc3545;
    }

    .pp-btn-reject:hover {
        background: #f8d7da;
    }

    .pp-btn-approve {
        background: #e85d3f;
        color: #fff;
    }

    .pp-btn-approve:hover {
        background: #e85d3f;
    }

    .pp-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .pp-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--pc-text-secondary, #65676b);
    }

    .pp-empty i {
        font-size: 2rem;
        display: block;
        margin-bottom: 10px;
    }
</style>

<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const list = document.getElementById('ppList');

        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.pp-btn');
            if (!btn) return;

            const card = btn.closest('.pp-card');
            const postUuid = card.dataset.postUuid;
            const action = btn.dataset.action; // 'approve' | 'reject'

            card.querySelectorAll('.pp-btn').forEach(b => b.disabled = true);

            const url = `{{ url('/groups/'.$group->uuid.'/pending-posts') }}/${postUuid}/${action}`;

            fetch(url, {
                method: action === 'approve' ? 'PATCH' : 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
                .then(res => {
                    if (!res.ok) throw new Error('Request failed');
                    return res.json();
                })
                .then(() => {
                    card.classList.add('pp-removed');
                    setTimeout(() => {
                        card.remove();
                        if (!list.querySelector('.pp-card')) {
                            list.innerHTML = `
                                <div class="pp-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>{{ __('No posts waiting for review.') }}</p>
                                </div>
                            `;
                        }
                    }, 200);
                })
                .catch(() => {
                    card.querySelectorAll('.pp-btn').forEach(b => b.disabled = false);
                    alert('{{ __('Something went wrong. Please try again.') }}');
                });
        });
    })();
</script>
@endsection