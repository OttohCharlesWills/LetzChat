@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <a href="{{ route('ads.index') }}" class="text-decoration-none small mb-2 d-inline-block">
        <i class="bi bi-arrow-left"></i> {{ __('Back to Ads') }}
    </a>

    <div class="pc-card mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge {{ $ad->status === 'active' ? 'bg-success' : ($ad->status === 'paused' ? 'bg-secondary' : 'bg-dark') }}">
                {{ ucfirst($ad->status) }}
            </span>
            <span class="text-muted small">{{ $ad->start_date->format('M j, Y') }} – {{ $ad->end_date->format('M j, Y') }}</span>
        </div>
        <p class="mb-0">{{ $ad->post->body }}</p>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-5 fw-bold">{{ number_format($ad->impressions_count) }}</div>
                <div class="text-muted small">{{ __('Impressions') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-5 fw-bold">{{ number_format($ad->clicks_count) }}</div>
                <div class="text-muted small">{{ __('Clicks') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-5 fw-bold">{{ $ctr }}%</div>
                <div class="text-muted small">{{ __('CTR') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pc-card text-center">
                <div class="fs-5 fw-bold">{{ number_format($ad->spent, 2) }}</div>
                <div class="text-muted small">{{ __('Spent') }} / {{ number_format($ad->budget, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="pc-card">
        <p class="fw-bold mb-2">{{ __('Audience') }}</p>
        <p class="mb-1 text-muted small">
            {{ __('Age') }}:
            {{ $ad->target_min_age || $ad->target_max_age ? ($ad->target_min_age ?? '13') . '–' . ($ad->target_max_age ?? '100') : __('Any') }}
        </p>
        <p class="mb-1 text-muted small">{{ __('Gender') }}: {{ ucfirst($ad->target_gender) }}</p>
        <p class="mb-0 text-muted small">
            {{ __('Locations') }}: {{ $ad->target_locations ? implode(', ', $ad->target_locations) : __('Any') }}
        </p>
    </div>

    <div class="mt-3 d-flex gap-2">
        @if ($ad->status === 'active')
            <form method="POST" action="{{ route('ads.pause', $ad->uuid) }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">{{ __('Pause ad') }}</button>
            </form>
        @elseif ($ad->status === 'paused')
            <form method="POST" action="{{ route('ads.resume', $ad->uuid) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm">{{ __('Resume ad') }}</button>
            </form>
        @endif
    </div>

</div>
@endsection