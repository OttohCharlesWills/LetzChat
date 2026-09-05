@extends('layouts.adapp')

@section('content')
<div class="ads-page py-4">
    <div class="ads-container">

        @if (session('status'))
            @include('friends.flash')
        @endif

        {{-- Header --}}
        <div class="ads-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">{{ __('Your Ads') }}</h4>
                <p class="text-muted small mb-0">{{ __('Boost your posts and track how they perform.') }}</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('wallet.index') }}" class="wallet-chip">
                    <i class="bi bi-wallet2"></i>
                    <span>{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</span>
                </a>
                <a href="{{ route('ads.create') }}" class="btn-boost">
                    <i class="bi bi-lightning-charge-fill"></i> {{ __('Boost a post') }}
                </a>
            </div>
        </div>

        @forelse ($ads as $ad)
            @php
                $progress = $ad->budget > 0 ? min(100, ($ad->spent / $ad->budget) * 100) : 0;
                $ctr = $ad->impressions_count > 0
                    ? round(($ad->clicks_count / $ad->impressions_count) * 100, 2)
                    : 0;
                $statusMap = [
                    'active'    => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'dot' => 'dot-success'],
                    'paused'    => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'dot' => 'dot-secondary'],
                    'completed' => ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'dot' => 'dot-dark'],
                ];
                $status = $statusMap[$ad->display_status] ?? ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'dot' => 'dot-dark'];
            @endphp

            <div class="ad-card mb-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                    <div class="ad-main">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="status-pill {{ $status['bg'] }} {{ $status['text'] }}">
                                <span class="status-dot {{ $status['dot'] }}"></span>
                                {{ ucfirst($ad->display_status) }}
                            </span>
                            <span class="text-muted small">
                                @if ($ad->start_at && $ad->end_at)
                                    {{ $ad->start_at->format('M j, Y') }} – {{ $ad->end_at->format('M j, Y') }}
                                @else
                                    {{ __('No schedule set') }}
                                @endif
                            </span>
                        </div>

                        <p class="ad-body mb-0">
                            {{ $ad->post->body ?? __('(Image post)') }}
                        </p>
                    </div>
                </div>

                {{-- Ad performance row --}}
                <div class="stat-row mb-3">
                    <div class="stat-block">
                        <div class="stat-block-value">{{ number_format($ad->impressions_count) }}</div>
                        <div class="stat-block-label"><i class="bi bi-eye-fill"></i> {{ __('Impressions') }}</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-block-value">{{ number_format($ad->clicks_count) }}</div>
                        <div class="stat-block-label"><i class="bi bi-cursor-fill"></i> {{ __('Clicks') }}</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-block-value">{{ $ctr }}%</div>
                        <div class="stat-block-label"><i class="bi bi-graph-up-arrow"></i> {{ __('CTR') }}</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-block-value">{{ number_format($ad->post->views_count) }}</div>
                        <div class="stat-block-label"><i class="bi bi-file-earmark-text-fill"></i> {{ __('Post views') }}</div>
                    </div>
                    <div class="stat-block">
                        <div class="stat-block-value">{{ number_format($ad->post->likes_count) }}</div>
                        <div class="stat-block-label"><i class="bi bi-hand-thumbs-up-fill"></i> {{ __('Reactions') }}</div>
                    </div>
                </div>

                <div class="budget-bar mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>{{ __('Spent') }}: {{ number_format($ad->spent, 2) }}</span>
                        <span>{{ __('Budget') }}: {{ number_format($ad->budget, 2) }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('ads.show', $ad->uuid) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        {{ __('View details') }}
                    </a>

                    @if ($ad->display_status === 'active')
                        <form method="POST" action="{{ route('ads.pause', $ad->uuid) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="bi bi-pause-fill"></i> {{ __('Pause') }}
                            </button>
                        </form>
                    @elseif ($ad->display_status === 'paused')
                        <form method="POST" action="{{ route('ads.resume', $ad->uuid) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                <i class="bi bi-play-fill"></i> {{ __('Resume') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state text-center">
                <div class="empty-icon mb-3">
                    <i class="bi bi-megaphone"></i>
                </div>
                <h5 class="fw-semibold mb-1">{{ __("No ads yet") }}</h5>
                <p class="text-muted mb-4">{{ __("Boost a post to reach more people and grow your audience.") }}</p>
                <a href="{{ route('ads.create') }}" class="btn-boost">
                    <i class="bi bi-lightning-charge-fill"></i> {{ __('Boost your first post') }}
                </a>
            </div>
        @endforelse

        @if ($ads->hasPages())
            <div class="mt-4">
                {{ $ads->links() }}
            </div>
        @endif

    </div>
</div>

<style>
    .ads-page {
        background: #f0f2f5;
        min-height: calc(100vh - 60px);
    }

    .ads-container {
        max-width: 760px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .wallet-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        background: #fff;
        color: #333;
        font-weight: 500;
        font-size: 0.9rem;
        text-decoration: none;
        border: 1px solid #e5e6ea;
        transition: background .15s ease;
    }
    .wallet-chip:hover { background: #f4f5f7; color: #333; }

    .btn-boost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: linear-gradient(135deg, #4f7cff, #3b5fe0);
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 9px 20px;
        font-weight: 500;
        font-size: 0.9rem;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(59, 95, 224, 0.25);
        transition: opacity .15s ease, transform .15s ease;
    }
    .btn-boost:hover {
        color: #fff;
        opacity: .92;
        transform: translateY(-1px);
    }

    .ad-card {
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .ad-body {
        color: #1c1e21;
        font-size: 0.95rem;
        max-width: 480px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .dot-success { background: #28a745; }
    .dot-secondary { background: #6c757d; }
    .dot-dark { background: #212529; }

    /* Stats row */
    .stat-row {
        display: flex;
        border-top: 1px solid #f0f2f5;
        border-bottom: 1px solid #f0f2f5;
        padding: 12px 0;
    }
    .stat-block {
        flex: 1;
        text-align: center;
        border-right: 1px solid #f0f2f5;
    }
    .stat-block:last-child { border-right: none; }
    .stat-block-value { font-weight: 700; font-size: 1rem; color: #1c1e21; }
    .stat-block-label {
        font-size: 0.72rem;
        color: #8a8d91;
        margin-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .budget-bar .progress-bar { background: #4f7cff; }

    .empty-state {
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 16px;
        padding: 56px 24px;
    }
    .empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        border-radius: 50%;
        background: #eef2ff;
        color: #4f7cff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }
</style>
@endsection