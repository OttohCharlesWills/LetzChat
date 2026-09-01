@extends('layouts.app')

@section('content')
<div class="container">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Your Ads') }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-wallet2"></i> {{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}
            </a>
            <a href="{{ route('ads.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> {{ __('Boost a post') }}
            </a>
        </div>
    </div>

    @forelse ($ads as $ad)
        <div class="pc-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-truncate mb-1" style="max-width: 400px;">
                        {{ $ad->post->body ?? __('(Image post)') }}
                    </div>
                    <span class="badge {{ $ad->status === 'active' ? 'bg-success' : ($ad->status === 'paused' ? 'bg-secondary' : 'bg-dark') }}">
                        {{ ucfirst($ad->status) }}
                    </span>
                    <span class="text-muted small ms-2">
                        {{ $ad->start_date->format('M j') }} – {{ $ad->end_date->format('M j') }}
                    </span>
                </div>

                <div class="d-flex gap-3 text-muted small">
                    <span><i class="bi bi-eye-fill"></i> {{ number_format($ad->impressions_count) }}</span>
                    <span><i class="bi bi-cursor-fill"></i> {{ number_format($ad->clicks_count) }}</span>
                    <span>{{ number_format($ad->spent, 2) }} / {{ number_format($ad->budget, 2) }}</span>
                </div>
            </div>

            <div class="mt-2 d-flex gap-2">
                <a href="{{ route('ads.show', $ad->uuid) }}" class="btn btn-sm btn-outline-primary">{{ __('View details') }}</a>

                @if ($ad->status === 'active')
                    <form method="POST" action="{{ route('ads.pause', $ad->uuid) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Pause') }}</button>
                    </form>
                @elseif ($ad->status === 'paused')
                    <form method="POST" action="{{ route('ads.resume', $ad->uuid) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Resume') }}</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="pc-card text-center py-5">
            <p class="text-muted mb-2">{{ __("You haven't boosted any posts yet.") }}</p>
            <a href="{{ route('ads.create') }}" class="btn btn-primary btn-sm">{{ __('Boost your first post') }}</a>
        </div>
    @endforelse

    @if ($ads->hasPages())
        {{ $ads->links() }}
    @endif

</div>
@endsection