@extends('layouts.grouplist')

@section('content')
<div class="mr-page">
    <div class="mr-header">
        <a href="{{ route('groups.show', $group) }}" class="mr-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="mr-eyebrow">{{ $group->name }}</div>
            <div class="mr-title">{{ __('Member Requests') }}</div>
        </div>
    </div>

    <div class="mr-list" id="mrList">
        @forelse ($requests as $req)
            <div class="mr-card" data-request-id="{{ $req->id }}">
                <a href="{{ route('profile.show', $req->user->uuid) }}" class="mr-user-link">
                    @if ($req->user->profile_photo)
                        <img src="{{ $req->user->profile_photo }}" class="mr-avatar" alt="{{ $req->user->first_name }}">
                    @else
                        <div class="mr-avatar-fallback">
                            {{ strtoupper(mb_substr($req->user->first_name, 0, 1)) }}
                        </div>
                    @endif
                </a>

                <div class="mr-body">
                    <a href="{{ route('profile.show', $req->user->uuid) }}" class="mr-name">
                        {{ $req->user->first_name }} {{ $req->user->last_name }}
                    </a>
                    <div class="mr-meta">{{ __('Requested') }} {{ $req->created_at->diffForHumans() }}</div>
                </div>

                <div class="mr-actions">
                    <button type="button" class="mr-btn mr-btn-reject" data-action="reject">
                        {{ __('Decline') }}
                    </button>
                    <button type="button" class="mr-btn mr-btn-approve" data-action="approve">
                        {{ __('Approve') }}
                    </button>
                </div>
            </div>
        @empty
            <div class="mr-empty">
                <i class="bi bi-person-check"></i>
                <p>{{ __('No pending join requests.') }}</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .mr-page {
        max-width: 640px;
        margin: 24px auto;
    }

    .mr-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .mr-back {
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

    .mr-eyebrow {
        font-size: 0.78rem;
        color: var(--pc-text-secondary, #65676b);
        font-weight: 600;
    }

    .mr-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--pc-text, #050505);
    }

    .mr-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .mr-card {
        background: var(--pc-bg, #fff);
        border: 1px solid var(--pc-border, #e4e6eb);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mr-card.mr-removed {
        opacity: 0;
        transform: scale(0.97);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .mr-avatar,
    .mr-avatar-fallback {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .mr-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--pc-avatar-fallback-bg, #0d6efd);
        color: var(--pc-avatar-fallback-text, #fff) !important;
        font-weight: 700;
    }

    .mr-body {
        flex: 1;
        min-width: 0;
    }

    .mr-name {
        display: block;
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--pc-text, #050505);
        text-decoration: none;
    }

    .mr-name:hover {
        text-decoration: underline;
        color: var(--pc-text, #050505);
    }

    .mr-meta {
        font-size: 0.78rem;
        color: var(--pc-text-secondary, #65676b);
    }

    .mr-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .mr-btn {
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        font-weight: 700;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .mr-btn-reject {
        background: var(--pc-hover, #f0f2f5);
        color: var(--pc-text-secondary, #65676b);
    }

    .mr-btn-reject:hover {
        background: #f8d7da;
        color: #dc3545;
    }

    .mr-btn-approve {
        background: #0d6efd;
        color: #fff;
    }

    .mr-btn-approve:hover {
        background: #0a58ca;
    }

    .mr-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .mr-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--pc-text-secondary, #65676b);
    }

    .mr-empty i {
        font-size: 2rem;
        display: block;
        margin-bottom: 10px;
    }
</style>

<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const list = document.getElementById('mrList');

        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.mr-btn');
            if (!btn) return;

            const card = btn.closest('.mr-card');
            const requestId = card.dataset.requestId;
            const action = btn.dataset.action; // 'approve' | 'reject'

            card.querySelectorAll('.mr-btn').forEach(b => b.disabled = true);

            const url = `{{ url('/groups/'.$group->uuid.'/join-requests') }}/${requestId}/${action}`;

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
                    card.classList.add('mr-removed');
                    setTimeout(() => {
                        card.remove();
                        if (!list.querySelector('.mr-card')) {
                            list.innerHTML = `
                                <div class="mr-empty">
                                    <i class="bi bi-person-check"></i>
                                    <p>{{ __('No pending join requests.') }}</p>
                                </div>
                            `;
                        }
                    }, 200);
                })
                .catch(() => {
                    card.querySelectorAll('.mr-btn').forEach(b => b.disabled = false);
                    alert('{{ __('Something went wrong. Please try again.') }}');
                });
        });
    })();
</script>
@endsection