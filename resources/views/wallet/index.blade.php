@extends('layouts.adapp')

@section('content')
<div class="container" style="max-width: 640px;">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <div class="pc-card mb-3 text-center">
        <div class="text-muted small">{{ __('Wallet balance') }}</div>
        <div class="fs-2 fw-bold">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="pc-card mb-3">
        <p class="fw-bold mb-2">{{ __('Top up') }}</p>

        <div class="d-flex gap-2">
            <input type="number" id="walletTopupAmount" class="form-control" min="100" step="1" placeholder="{{ __('Amount (NGN)') }}" required>
            <button type="button" id="walletTopupBtn" class="btn btn-primary">{{ __('Top up with Paystack') }}</button>
        </div>

        <div id="walletTopupStatus" class="mt-2 small"></div>
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

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
(function () {
    const btn = document.getElementById('walletTopupBtn');
    const amountInput = document.getElementById('walletTopupAmount');
    const statusEl = document.getElementById('walletTopupStatus');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    btn.addEventListener('click', function () {
        const amount = parseFloat(amountInput.value);

        if (!amount || amount < 100) {
            statusEl.textContent = '{{ __('Enter an amount of at least 100.') }}';
            statusEl.className = 'mt-2 small text-danger';
            return;
        }

        btn.disabled = true;
        statusEl.textContent = '{{ __('Opening secure payment window…') }}';
        statusEl.className = 'mt-2 small text-muted';

        const handler = PaystackPop.setup({
            key: '{{ config('services.paystack.public_key') }}',
            email: '{{ auth()->user()->email }}',
            amount: Math.round(amount * 100), // Paystack expects kobo
            currency: 'NGN',
            metadata: {
                user_id: {{ auth()->id() }},
                purpose: 'wallet_topup',
            },
            callback: function (response) {
                statusEl.textContent = '{{ __('Payment received, confirming…') }}';

                fetch('{{ route('wallet.verify') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ reference: response.reference }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        statusEl.textContent = data.message;
                        statusEl.className = data.credited
                            ? 'mt-2 small text-success'
                            : 'mt-2 small text-danger';

                        if (data.credited) {
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            btn.disabled = false;
                        }
                    })
                    .catch(() => {
                        statusEl.textContent = '{{ __('Could not confirm payment. Refresh to check your balance.') }}';
                        statusEl.className = 'mt-2 small text-danger';
                        btn.disabled = false;
                    });
            },
            onClose: function () {
                statusEl.textContent = '{{ __('Payment window closed.') }}';
                statusEl.className = 'mt-2 small text-muted';
                btn.disabled = false;
            },
        });

        handler.openIframe();
    });
})();
</script>
@endsection