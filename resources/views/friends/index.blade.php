@extends('layouts.friend')

@section('content')
<div class="friends-page">

    @include('friends.flash')

    {{-- ================= FRIEND REQUESTS PREVIEW ================= --}}
    <div class="fp-section">
        <div class="fp-section-header">
            <div class="fp-section-title">{{ __('Friend requests') }}</div>
            @if ($totalIncoming > count($incomingRequests))
                <a href="{{ route('friends.requests') }}" class="fp-see-all">{{ __('See all') }}</a>
            @endif
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

            @if ($totalIncoming > count($incomingRequests))
                <div class="text-center mt-3">
                    <a href="{{ route('friends.requests') }}" class="btn btn-outline-secondary btn-sm">
                        {{ __('See More') }}
                    </a>
                </div>
            @endif
        @endif
    </div>

    {{-- ================= SUGGESTIONS PREVIEW ================= --}}
    <div class="fp-section">
        <div class="fp-section-header">
            <div class="fp-section-title">{{ __('People you may know') }}</div>
            <a href="{{ route('friends.suggestions') }}" class="fp-see-all">{{ __('See all') }}</a>
        </div>

        @if ($suggestions->isEmpty())
            <p class="text-muted mb-0">{{ __('No suggestions right now.') }}</p>
        @else
            <div class="pc-grid">
                @foreach ($suggestions as $person)
                    @include('friends.card', ['person' => $person, 'variant' => 'suggestion'])
                @endforeach
            </div>
        @endif
    </div>

</div>

@include('friends.grid-styles')
@endsection