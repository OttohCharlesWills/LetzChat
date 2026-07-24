<div class="cm-item" data-comment-id="{{ $comment->id }}">

    @if ($comment->trashed())
        <span class="cm-avatar cm-avatar-fallback">?</span>
    @else
        <a href="{{ route('profile.show', $comment->user->username) }}" class="cm-avatar-link">
            @if ($comment->user->profile_photo)
                <img src="{{ $comment->user->profile_photo }}" class="cm-avatar">
            @else
                <span class="cm-avatar cm-avatar-fallback">
                    {{ strtoupper(substr($comment->user->first_name, 0, 1)) }}
                </span>
            @endif
        </a>
    @endif

    <div class="cm-body-wrap">

        @if ($comment->trashed())
            <div class="cm-bubble cm-deleted-bubble">
                <em>{{ __('This comment was deleted.') }}</em>
            </div>
        @else
            <div class="cm-bubble">
                <a href="{{ route('profile.show', $comment->user->username) }}" class="cm-author">
                    {{ $comment->user->first_name }} {{ $comment->user->last_name }}
                </a>
                <div class="cm-text">{{ $comment->body }}</div>
            </div>

            <div class="cm-actions">
                <span class="cm-time">{{ $comment->created_at->diffForHumans() }}</span>

                <button type="button"
                        class="cm-like-btn {{ $comment->isLikedBy(auth()->user()) ? 'liked' : '' }}"
                        data-comment-id="{{ $comment->id }}">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                    <span class="cm-like-count">{{ $comment->likes_count }}</span>
                </button>

                @if (!$comment->parent_id)
                    <button type="button" class="cm-reply-btn"
                            data-comment-id="{{ $comment->id }}"
                            data-author-name="{{ $comment->user->first_name }}">
                        {{ __('Reply') }}
                    </button>
                @endif

                @if ($comment->user_id === auth()->id())
                    <button type="button" class="cm-delete-btn" data-comment-id="{{ $comment->id }}">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        @endif

        {{-- Replies always render for top-level comments, even if THIS
             comment was deleted — soft delete keeps the thread intact. --}}
        @if (!$comment->parent_id)
            <div class="cm-replies" id="cmReplies{{ $comment->id }}">
                @foreach ($comment->replies as $reply)
                    @include('posts.partials.comment', ['comment' => $reply, 'post' => $post])
                @endforeach
            </div>

            @unless ($comment->trashed())
                <form class="cm-reply-form d-none"
                      id="cmReplyForm{{ $comment->id }}"
                      data-post-uuid="{{ $post->uuid }}"
                      data-parent-id="{{ $comment->id }}">
                    @csrf
                    <input type="text" class="cm-reply-input" placeholder="{{ __('Write a reply...') }}">
                    <button type="submit" class="cm-reply-send-btn" title="{{ __('Send reply') }}">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            @endunless
        @endif

    </div>
</div>