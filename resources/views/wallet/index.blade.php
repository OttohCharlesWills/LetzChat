@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <div class="pc-card mb-3 text-center">
        <div class="text-muted small">{{ __('Wallet balance') }}</div>
        <div class="fs-2 fw-bold">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</div>
    </div>

    <div class="pc-card mb-3">
        <p class="fw-bold mb-2">{{ __('Top up') }}</p>
        <p class="text-muted small">{{ __('Payment gateway integration coming soon — this credits your wallet directly for now.') }}</p>

        <form method="POST" action="{{ route('wallet.topup') }}" class="d-flex gap-2">
            @csrf
            <input type="number" name="amount" class="form-control" min="100" step="1" placeholder="{{ __('Amount') }}" required>
            <button type="submit" class="btn btn-primary">{{ __('Top up') }}</button>
        </form>
    </div>

    <div class="pc-card">
        <p class="fw-bold mb-2">{{ __('Transaction history') }}</p>

        @forelse ($transactions as $tx)
            <div class="d-flex justify-content-between border-bottom py-2">
                <div>
                    <div>{{ $tx->description }}</div>
                    <div class="text-muted small">{{ $tx->created_at->diffForHumans() }}</div>
                </div>
                <div class="{{ $tx->type === 'topup' || $tx->type === 'refund' ? 'text-success' : 'text-danger' }}">
                    {{ $tx->type === 'topup' || $tx->type === 'refund' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">{{ __('No transactions yet.') }}</p>
        @endforelse
    </div>

    @if ($transactions->hasPages())
        <div class="mt-3">{{ $transactions->links() }}</div>
    @endif

</div>
@endsections