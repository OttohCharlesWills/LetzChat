@extends('layouts.friend')

@section('content')
<div class="friends-page">

    @include('friends.flash')

    <div class="fp-section">
        <div class="fp-section-header">
            <div class="fp-section-title">{{ __('People you may know') }}</div>
        </div>

        @if ($suggestions->isEmpty())
            <p class="text-muted mb-0">{{ __('No suggestions right now.') }}</p>
        @else
            <div class="pc-grid">
                @foreach ($suggestions as $person)
                    @include('friends.card', ['person' => $person, 'variant' => 'suggestion'])
                @endforeach
            </div>

            @if ($hasMore)
                <div class="text-center mt-3">
                    <a href="{{ route('friends.suggestions', ['page' => $page + 1]) }}" class="btn btn-outline-secondary btn-sm">
                        {{ __('See More') }}
                    </a>
                </div>
            @endif
        @endif
    </div>

</div>

@include('friends.grid-styles')
@endsection