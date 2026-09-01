@extends('layouts.dashboardapp')

@section('content')
<div class="dash-page">

    {{-- ---- Header ---- --}}
    <div class="dash-hero mb-4">
        <div>
            <h4 class="mb-1 fw-bold">{{ __('Dashboard') }}</h4>
            <p class="text-muted small mb-0">{{ __('An overview of how your content is performing.') }}</p>
        </div>
        <a href="{{ route('ads.index') }}" class="btn-ads-link">
            <i class="bi bi-megaphone-fill"></i> {{ __('Ads') }}
        </a>
    </div>

    {{-- ---- Undone tasks ---- --}}
    @php
        $pendingTasks = collect($tasks)->reject(fn ($t) => $t['done']);
        $totalTasks = collect($tasks)->count();
        $doneTasks = $totalTasks - $pendingTasks->count();
        $setupProgress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 100;
    @endphp
    @if ($pendingTasks->isNotEmpty())
        <div class="setup-card mb-4">
            <div class="setup-card-inner">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="setup-ring" style="--p: {{ $setupProgress }}">
                            <span>{{ $setupProgress }}%</span>
                        </div>
                        <div>
                            <div class="fw-bold">{{ __('Finish setting up your account') }}</div>
                            <div class="text-muted small">{{ $doneTasks }} {{ __('of') }} {{ $totalTasks }} {{ __('steps complete') }}</div>
                        </div>
                    </div>
                </div>
                <div class="setup-tasks">
                    @foreach ($pendingTasks as $task)
                        <a href="{{ $task['route'] }}" class="setup-task">
                            <span class="setup-task-dot"><i class="bi bi-circle"></i></span>
                            <span class="flex-grow-1">{{ $task['label'] }}</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ---- Summary stats ---- --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($totalPosts) }}</div>
                    <div class="stat-label">{{ __('Total Posts') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple"><i class="bi bi-eye-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($totalViews) }}</div>
                    <div class="stat-label">{{ __('Total Views') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-orange"><i class="bi bi-hand-thumbs-up-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($totalLikes) }}</div>
                    <div class="stat-label">{{ __('Total Reactions') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-green"><i class="bi bi-chat-fill"></i></div>
                <div>
                    <div class="stat-value">{{ number_format($totalComments) }}</div>
                    <div class="stat-label">{{ __('Total Comments') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ---- Recent posts ---- --}}
        <div class="col-md-8">
            <div class="dash-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-bold">{{ __('Recent Posts') }}</div>
                    @if($recentPosts->isNotEmpty())
                        <span class="text-muted small">{{ __('Last') }} {{ $recentPosts->count() }}</span>
                    @endif
                </div>

                @forelse ($recentPosts as $post)
                    <div class="recent-post-row">
                        <div class="text-truncate post-body">
                            {{ $post->body ?? __('(Image post)') }}
                        </div>
                        <div class="d-flex gap-3 text-muted small flex-shrink-0">
                            <span><i class="bi bi-eye-fill"></i> {{ number_format($post->views_count) }}</span>
                            <span><i class="bi bi-hand-thumbs-up-fill"></i> {{ number_format($post->likes_count) }}</span>
                            <span><i class="bi bi-chat-fill"></i> {{ number_format($post->comments_count) }}</span>
                            <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-inline text-center py-5">
                        <div class="empty-inline-circle mb-2">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <p class="text-muted mb-0">{{ __("You haven't posted anything yet.") }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ---- Top regions ---- --}}
        <div class="col-md-4">
            <div class="dash-card h-100">
                <div class="fw-bold mb-3">{{ __('Where your viewers are') }}</div>

                @forelse ($topRegions as $region)
                    @php $max = $topRegions->max('views') ?: 1; @endphp
                    <div class="region-row">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $region->location }}</span>
                            <span class="text-muted">{{ number_format($region->views) }}</span>
                        </div>
                        <div class="progress region-progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ ($region->views / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-inline text-center py-5">
                        <div class="empty-inline-circle mb-2">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <p class="text-muted mb-0 small">{{ __('Not enough data yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<style>
    .dash-page {
        max-width: 100%;
    }

    .dash-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .dash-card {
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .btn-ads-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #4f7cff, #3b5fe0);
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 9px 18px;
        font-weight: 500;
        font-size: 0.88rem;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(59, 95, 224, 0.25);
        transition: opacity .15s ease, transform .15s ease;
    }
    .btn-ads-link:hover { color: #fff; opacity: .92; transform: translateY(-1px); }

    /* Setup checklist */
    .setup-card {
        border-radius: 16px;
        padding: 1px;
        background: linear-gradient(135deg, #4f7cff, #8b5cf6);
    }
    .setup-card-inner {
        background: #fff;
        border-radius: 15px;
        padding: 18px 20px;
    }
    .setup-ring {
        --p: 0;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        flex-shrink: 0;
        background: conic-gradient(#4f7cff calc(var(--p) * 1%), #eef0f3 0);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .setup-ring span {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: #4f7cff;
    }
    .setup-tasks { display: flex; flex-direction: column; gap: 2px; }
    .setup-task {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 8px;
        border-radius: 8px;
        text-decoration: none;
        color: #1c1e21;
        font-size: 0.9rem;
        transition: background .12s ease;
    }
    .setup-task:hover { background: #f4f6f9; color: #1c1e21; }
    .setup-task-dot { color: #c7cad1; font-size: 0.8rem; display: flex; }
    .setup-task > i:last-child { color: #c7cad1; font-size: 0.8rem; }

    /* Stat cards */
    .stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #fff;
        border: 1px solid #eceef1;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        height: 100%;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .stat-icon-blue   { background: #eaf1ff; color: #4f7cff; }
    .stat-icon-purple { background: #f2ecff; color: #8b5cf6; }
    .stat-icon-orange { background: #fff2e5; color: #f0883e; }
    .stat-icon-green  { background: #e9f9ee; color: #2fae5c; }
    .stat-value { font-size: 1.3rem; font-weight: 700; color: #1c1e21; line-height: 1.2; }
    .stat-label { font-size: 0.78rem; color: #8a8d91; margin-top: 1px; }

    /* Recent posts */
    .recent-post-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #f0f2f5;
    }
    .recent-post-row:last-child { border-bottom: none; }
    .post-body { max-width: 280px; font-size: 0.92rem; color: #1c1e21; }
    .post-time { min-width: 70px; text-align: right; }

    /* Top regions */
    .region-row { margin-bottom: 12px; }
    .region-row:last-child { margin-bottom: 0; }
    .region-progress { background: #f0f2f5; }
    .region-progress .progress-bar { background: #8b5cf6; }

    /* Empty states */
    .empty-inline-circle {
        width: 56px;
        height: 56px;
        margin: 0 auto;
        border-radius: 50%;
        background: #f4f6f9;
        color: #b0b3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
</style>
@endsection