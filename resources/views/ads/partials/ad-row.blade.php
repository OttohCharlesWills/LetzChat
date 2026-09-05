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
    $past = $past ?? false;
@endphp

<div class="ad-card mb-3 {{ $past ? 'ad-card-past' : '' }}">
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

        @unless ($past)
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
        @endunless
    </div>
</div>

<style>
    .ad-card {
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .ad-card-past {
        opacity: 0.75;
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
</style>