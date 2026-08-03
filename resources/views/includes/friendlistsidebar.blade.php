    {{-- ================= LEFT: SEARCHABLE FRIEND LIST ================= --}}
    <div class="fl-list-col">
        <div class="fl-list-header">
            <a href="{{ route('friends.index') }}" class="fl-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="fl-eyebrow">{{ __('Friends') }}</div>
                <div class="fl-title">{{ __('All friends') }}</div>
            </div>
        </div>
 
        <div class="fl-search-wrap">
            <i class="bi bi-search fl-search-icon"></i>
            <input type="text" id="flSearchInput" class="fl-search-input" placeholder="{{ __('Search Friends') }}">
        </div>
 
        <div class="fl-count" id="flCount">{{ $friends->count() }} {{ Str::plural('friend', $friends->count()) }}</div>
 
        <div class="fl-list" id="flList">
            @forelse ($friends as $friend)
               <div
                    class="fl-item"
                    tabindex="0"
                    role="button"
                    data-name="{{ strtolower($friend->first_name . ' ' . $friend->last_name) }}"
                    data-fullname="{{ $friend->first_name }} {{ $friend->last_name }}"
                    data-username="{{ $friend->username }}"
                    data-avatar="{{ $friend->profile_photo ?? '' }}"
                    data-initial="{{ strtoupper(substr($friend->first_name, 0, 1)) }}"
                    data-mutual="{{ $friend->mutual_count ?? 0 }}"
                    data-friendship-id="{{ $friend->friendship_id }}"
                >
                    @if ($friend->profile_photo)
                        <img src="{{ $friend->profile_photo }}" class="fl-avatar">
                    @else
                        <span class="fl-avatar fl-avatar-fallback">
                            {{ strtoupper(substr($friend->first_name, 0, 1)) }}
                        </span>
                    @endif
 
                    <span class="fl-item-body">
                        <span class="fl-item-name">{{ $friend->first_name }} {{ $friend->last_name }}</span>
                        @if (($friend->mutual_count ?? 0) > 0)
                            <span class="fl-item-mutual">{{ $friend->mutual_count }} {{ Str::plural('mutual friend', $friend->mutual_count) }}</span>
                        @endif
                    </span>
 
                    <span class="fl-item-menu" onclick="event.stopPropagation(); toggleFlMenu(this)">
                        <i class="bi bi-three-dots"></i>
 
                        <div class="fl-dropdown" onclick="if (!event.target.closest('[data-open-messenger]')) event.stopPropagation()">
                            {{-- Unfollow: hides their posts from your feed without unfriending.
                                 No backend route yet — wire this to whatever your feed/follow
                                 logic ends up being (e.g. a follows table or a flag on Friendship). --}}
                            <button type="button" class="fl-dropdown-item">
                                {{ __('Unfollow') }} {{ $friend->first_name }}
                            </button>
 
                            {{-- Message: point this at your actual chat route --}}
                            <a href="#" class="fl-dropdown-item" data-open-messenger
                                data-friend-uuid="{{ $friend->uuid }}"
                                data-friend-name="{{ $friend->first_name }} {{ $friend->last_name }}"
                                data-friend-avatar="{{ $friend->profile_photo ?? '' }}"
                                data-friend-initial="{{ strtoupper(substr($friend->first_name, 0, 1)) }}">
                                {{ __('Message') }}
                            </a>
 
                            <form method="POST" action="{{ route('friends.destroy', $friend->friendship_id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="fl-dropdown-item fl-dropdown-danger">
                                    {{ __('Unfriend') }}
                                </button>
                            </form>
                        </div>
                    </span>
                </div>
            @empty
                <p class="text-muted px-2">{{ __("You don't have any friends yet.") }}</p>
            @endforelse
        </div>
    </div>

<script>
    document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-open-messenger]');
            if (!trigger) return;

            e.preventDefault();
            e.stopPropagation();

            const { friendUuid, friendName, friendAvatar, friendInitial } = trigger.dataset;

            chatAvatarWrap.innerHTML = friendAvatar
                ? `<img src="${friendAvatar}" alt="${friendName}">`
                : `<span class="nb-mp-avatar-fallback">${friendInitial}</span>`;
            chatName.textContent = friendName;

            thread.innerHTML = `<p class="nb-mp-thread-placeholder">Loading…</p>`;

            listView.classList.add('d-none');
            chatView.classList.remove('d-none');
            messengerPanel.classList.add('show');

            openConversation(friendUuid, friendName);
        });
</script>