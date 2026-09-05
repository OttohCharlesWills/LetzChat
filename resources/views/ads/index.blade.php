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

        @php
            $activeAds = $ads->filter(fn ($ad) => in_array($ad->display_status, ['active', 'paused']));
            $pastAds = $ads->filter(fn ($ad) => $ad->display_status === 'completed');
        @endphp

        @if ($ads->isEmpty())
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
        @else

            {{-- ---- Active / Paused ads ---- --}}
            <div class="section-label">{{ __('Active') }}</div>

            @forelse ($activeAds as $ad)
                @include('ads.partials.ad-row', ['ad' => $ad])
            @empty
                <div class="empty-inline mb-4">
                    <p class="text-muted small mb-0">{{ __("You don't have any active ads right now.") }}</p>
                </div>
            @endforelse

            {{-- ---- Past / completed ads ---- --}}
            @if ($pastAds->isNotEmpty())
                <div class="section-label mt-4">{{ __('Past ads') }}</div>

                @foreach ($pastAds as $ad)
                    @include('ads.partials.ad-row', ['ad' => $ad, 'past' => true])
                @endforeach
            @endif

        @endif

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

    .section-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a8d91;
        margin-bottom: 10px;
        padding-left: 2px;
    }

    .empty-inline {
        background: #fff;
        border: 1px dashed #dfe1e6;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        text-align: center;
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