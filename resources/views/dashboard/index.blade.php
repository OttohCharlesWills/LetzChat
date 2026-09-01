@extends('layouts.dashboardapp')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Dashboard') }}</h4>
        <a href="{{ route('ads.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-megaphone-fill"></i> {{ __('Ads') }}
        </a>
    </div>

    {{-- ---- Undone tasks ---- --}}
    @php $pendingTasks = collect($tasks)->reject(fn ($t) => $t['done']); @endphp
    @if ($pendingTasks->isNotEmpty())
        <div class="pc-card mb-3">
            <div class="fw-bold mb-2">{{ __('Finish setting up your account') }}</div>
            @foreach ($pendingTasks as $task)
                <a href="{{ $task['route'] }}" class="d-flex align-items-center gap-2 py-1 text-decoration-none">
                    <i class="bi bi-circle text-muted"></i>
                    <span>{{ $task['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- ---- Summary stats ---- --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-4 fw-bold">{{ number_format($totalPosts) }}</div>
                <div class="text-muted small">{{ __('Total Posts') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-4 fw-bold">{{ number_format($totalViews) }}</div>
                <div class="text-muted small">{{ __('Total Views') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-4 fw-bold">{{ number_format($totalLikes) }}</div>
                <div class="text-muted small">{{ __('Total Reactions') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-4 fw-bold">{{ number_format($totalComments) }}</div>
                <div class="text-muted small">{{ __('Total Comments') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ---- Recent posts ---- --}}
        <div class="col-md-8">
            <div class="pc-card">
                <div class="fw-bold mb-2">{{ __('Recent Posts') }}</div>

                @forelse ($recentPosts as $post)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="text-truncate" style="max-width: 260px;">
                            {{ $post->body ?? __('(Image post)') }}
                        </div>
                        <div class="d-flex gap-3 text-muted small flex-shrink-0">
                            <span><i class="bi bi-eye-fill"></i> {{ number_format($post->views_count) }}</span>
                            <span><i class="bi bi-hand-thumbs-up-fill"></i> {{ number_format($post->likes_count) }}</span>
                            <span><i class="bi bi-chat-fill"></i> {{ number_format($post->comments_count) }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __("You haven't posted anything yet.") }}</p>
                @endforelse
            </div>
        </div>

        {{-- ---- Top regions ---- --}}
        <div class="col-md-4">
            <div class="pc-card">
                <div class="fw-bold mb-2">{{ __('Where your viewers are') }}</div>

                @forelse ($topRegions as $region)
                    <div class="d-flex justify-content-between py-1">
                        <span>{{ $region->location }}</span>
                        <span class="text-muted">{{ number_format($region->views) }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0 small">{{ __('Not enough data yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection