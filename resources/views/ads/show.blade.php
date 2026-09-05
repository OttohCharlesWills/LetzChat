@extends('layouts.adapp')

@section('content')
<div class="ad-show-page py-4">
    <div class="ad-show-container">

        @if (session('status'))
            @include('friends.flash')
        @endif

        <a href="{{ route('ads.index') }}" class="back-link mb-3 d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> {{ __('Back to Ads') }}
        </a>

        @if ($ad->display_status === 'completed')
            <div class="ended-banner mb-3">
                <i class="bi bi-flag-fill"></i>
                {{ __('This ad has ended. Any unused budget was refunded to your wallet.') }}
            </div>
        @endif

        <div class="ad-card mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                @php
                    $statusMap = [
                        'active'    => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'dot' => 'dot-success'],
                        'paused'    => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'dot' => 'dot-secondary'],
                        'completed' => ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'dot' => 'dot-dark'],
                    ];
                    $status = $statusMap[$ad->display_status] ?? ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'dot' => 'dot-dark'];
                @endphp
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
            <p class="mb-0 ad-post-body">{{ $ad->post->body }}</p>
        </div>

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
                <div class="stat-block-value">{{ number_format($ad->spent, 2) }}</div>
                <div class="stat-block-label"><i class="bi bi-cash-coin"></i> {{ __('Spent') }} / {{ number_format($ad->budget, 2) }}</div>
            </div>
        </div>

        <div class="ad-card mb-3">
            <p class="fw-bold mb-3">{{ __('Post engagement') }}</p>
            <div class="stat-row-inline">
                <div class="stat-block">
                    <div class="stat-block-value">{{ number_format($ad->post->views_count) }}</div>
                    <div class="stat-block-label"><i class="bi bi-eye-fill"></i> {{ __('Views') }}</div>
                </div>
                <div class="stat-block">
                    <div class="stat-block-value">{{ number_format($ad->post->likes_count) }}</div>
                    <div class="stat-block-label"><i class="bi bi-hand-thumbs-up-fill"></i> {{ __('Reactions') }}</div>
                </div>
                <div class="stat-block">
                    <div class="stat-block-value">{{ number_format($ad->post->comments_count) }}</div>
                    <div class="stat-block-label"><i class="bi bi-chat-fill"></i> {{ __('Comments') }}</div>
                </div>
            </div>
        </div>

        <div class="ad-card">
            <p class="fw-bold mb-2">{{ __('Audience') }}</p>
            <div class="audience-row">
                <span class="audience-label">{{ __('Age') }}</span>
                <span>
                    {{ $ad->target_min_age || $ad->target_max_age ? ($ad->target_min_age ?? '13') . '–' . ($ad->target_max_age ?? '100') : __('Any') }}
                </span>
            </div>
            <div class="audience-row">
                <span class="audience-label">{{ __('Gender') }}</span>
                <span>{{ ucfirst($ad->target_gender) }}</span>
            </div>
            <div class="audience-row">
                <span class="audience-label">{{ __('Locations') }}</span>
                <span>{{ $ad->target_locations ? implode(', ', $ad->target_locations) : __('Any') }}</span>
            </div>
        </div>

        @if ($ad->display_status !== 'completed')
            <div class="mt-3 d-flex gap-2">
                @if ($ad->display_status === 'active')
                    <form method="POST" action="{{ route('ads.pause', $ad->uuid) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-pause-fill"></i> {{ __('Pause ad') }}
                        </button>
                    </form>
                @elseif ($ad->display_status === 'paused')
                    <form method="POST" action="{{ route('ads.resume', $ad->uuid) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="bi bi-play-fill"></i> {{ __('Resume ad') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif

    </div>
</div>

<style>
    .ad-show-page {
        background: #f0f2f5;
        min-height: calc(100vh - 60px);
    }

    .ad-show-container {
        max-width: 640px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .back-link {
        color: #65676b;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
    }
    .back-link:hover { color: #1c1e21; }

    .ended-banner {
        background: #fff7e6;
        border: 1px solid #ffe4a3;
        color: #8a6100;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.86rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ad-card {
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .ad-post-body {
        color: #1c1e21;
        font-size: 0.95rem;
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

    .stat-row {
        display: flex;
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 14px;
        padding: 14px 0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .stat-row-inline {
        display: flex;
    }
    .stat-block {
        flex: 1;
        text-align: center;
        border-right: 1px solid #f0f2f5;
    }
    .stat-block:last-child { border-right: none; }
    .stat-block-value { font-weight: 700; font-size: 1.05rem; color: #1c1e21; }
    .stat-block-label {
        font-size: 0.74rem;
        color: #8a8d91;
        margin-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .audience-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f2f5;
        font-size: 0.88rem;
        color: #1c1e21;
    }
    .audience-row:last-child { border-bottom: none; }
    .audience-label { color: #8a8d91; }
</style>
@endsection