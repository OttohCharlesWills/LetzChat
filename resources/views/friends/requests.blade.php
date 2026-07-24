@extends('layouts.friend')

@section('content')
<div class="friends-page">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <div class="fp-section">
        <div class="fp-section-header">
            <div class="fp-section-title">{{ __('Friend requests') }} ({{ $incomingRequests->total() }})</div>
        </div>

        @if ($incomingRequests->isEmpty())
            <p class="text-muted mb-0">{{ __('No pending friend requests.') }}</p>
        @else
            <div class="pc-grid">
                @foreach ($incomingRequests as $request)
                    @include('friends.card', [
                        'person'       => $request->requester,
                        'variant'      => 'request',
                        'friendshipId' => $request->id,
                    ])
                @endforeach
            </div>

            <div class="mt-3">
                {{ $incomingRequests->links() }}
            </div>
        @endif
    </div>

</div>

@include('friends.grid-styles')
@endsection