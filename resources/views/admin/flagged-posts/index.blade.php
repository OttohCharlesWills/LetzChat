@extends('layouts.adminapp')

@section('content')
<div class="container">

    @if (session('status'))
        @include('friends.flash')
    @endif

    <h4 class="mb-3">{{ __('Flagged Posts') }} <span class="text-muted">({{ $posts->total() }})</span></h4>

    @forelse ($posts as $post)
        <div class="pc-card mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <a href="{{ route('profile.show', $post->user->uuid) }}">
                        {{ $post->user->first_name }} {{ $post->user->last_name }}
                    </a>
                    <span class="text-muted small">&middot; {{ $post->created_at->diffForHumans() }}</span>
                    @if ($post->group)
                        <span class="text-muted small">&middot; {{ __('in') }} {{ $post->group->name }}</span>
                    @endif
                </div>
            </div>

            <p class="mb-2">{{ $post->body }}</p>

            @if ($post->images->isNotEmpty())
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    @foreach ($post->images as $image)
                        <img src="{{ $image->url }}" style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                    @endforeach
                </div>
            @endif

            <div class="mb-2">
                @foreach (($post->flagged_words ?? []) as $word)
                    <span class="badge bg-danger">{{ $word }}</span>
                @endforeach
            </div>

            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.flagged-posts.dismiss', $post->uuid) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Dismiss (looks fine)') }}</button>
                </form>

                <form method="POST" action="{{ route('admin.flagged-posts.remove', $post->uuid) }}"
                      onsubmit="return confirm('{{ __('Delete this post? This cannot be undone.') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete post') }}</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">{{ __('No flagged posts right now.') }}</p>
    @endforelse

    @if ($posts->hasPages())
        {{ $posts->links() }}
    @endif

</div>
@endsection