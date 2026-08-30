@extends('layouts.app')

@section('content')
<div class="container">

    <div class="pc-card mb-3">
        <h4 class="mb-0">#{{ $tag->name }}</h4>
        <p class="text-muted mb-0">{{ number_format($tag->usage_count) }} {{ Str::plural(__('post'), $tag->usage_count) }}</p>
    </div>

    <div id="feedPostsList">
        @forelse ($posts as $post)
            @include('posts.partials.post-card', ['post' => $post])
        @empty
            <div class="pc-empty-state text-center py-5">
                <p class="text-muted mb-0">{{ __('No posts with this hashtag yet.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($posts->hasPages())
        <div class="mt-3">
            {{ $posts->links() }}
        </div>
    @endif

</div>

@include('posts.partials.feed-scripts')
@endsection