@extends('layouts.profile')

@section('content')
<div class="fw-page container">

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="fw-shell">

        <div class="fw-header">
            <a href="{{ route('profile.show', $user->username) }}" class="fw-back" title="{{ __('Back to profile') }}">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="fw-eyebrow">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="fw-title">{{ __('Followers') }} ({{ number_format($followers->total()) }})</div>
            </div>
        </div>

        <div class="fw-card">
            @forelse ($followers as $follower)
                @include('profile.follow-user-row', ['listedUser' => $follower])
            @empty
                <div class="fw-empty">
                    <i class="bi bi-people fw-empty-icon"></i>
                    <p class="mb-0">{{ __(':name doesn\'t have any followers yet.', ['name' => $user->first_name]) }}</p>
                </div>
            @endforelse
        </div>

        @if ($followers->hasPages())
            <div class="mt-3">
                {{ $followers->links() }}
            </div>
        @endif

    </div>
</div>

<style>
    .fw-page {
        background: var(--sb-hover);
        min-height: 100vh;
        padding: 24px 0;
        color: var(--sb-text);
    }

    .fw-shell {
        max-width: 640px;
        margin: 0 auto;
    }

    .fw-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .fw-back {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--sb-bg);
        border: 1px solid var(--sb-border);
        color: var(--sb-text);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .fw-back:hover {
        background: var(--sb-border);
        color: var(--sb-text);
    }

    .fw-eyebrow {
        font-size: 0.8rem;
        color: var(--sb-text-secondary);
    }

    .fw-title {
        font-weight: 700;
        font-size: 1.3rem;
        color: var(--sb-text);
    }

    .fw-card {
        background: var(--sb-bg);
        border: 1px solid var(--sb-border);
        border-radius: 10px;
        padding: 8px 16px;
    }

    .fw-empty {
        text-align: center;
        color: var(--sb-text-secondary);
        padding: 48px 16px;
        font-size: 0.9rem;
    }

    .fw-empty-icon {
        font-size: 2.4rem;
        opacity: 0.5;
        display: block;
        margin-bottom: 10px;
    }
</style>
@endsection