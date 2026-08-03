{{--
    Reusable person card. Pass in:
    - $person          : User instance (needs mutual_count set if you want it shown)
    - $variant         : 'request' | 'suggestion' | 'friend'
    - $friendshipId    : required for 'request' and 'friend' variants

    {{ route('profile.show', $person->username) }}
     {{ route('profile.show', $person->username) }}
--}}
<div class="pc-card">
    <a href="" class="pc-photo-link">
        @if ($person->profile_photo)
            <img src="{{ $person->profile_photo }}" class="pc-photo">
        @else
            <span class="pc-photo pc-photo-fallback">
                {{ strtoupper(substr($person->first_name, 0, 1)) }}
            </span>
        @endif
    </a>

    <div class="pc-body">
        <a href="" class="pc-name">
            {{ $person->first_name }} {{ $person->last_name }}
        </a>

        @if (isset($person->mutual_count) && $person->mutual_count > 0)
            <div class="pc-mutual">
                {{ $person->mutual_count }} {{ Str::plural('mutual friend', $person->mutual_count) }}
            </div>
        @endif

        <div class="pc-actions">
            @if ($variant === 'request')
                <form method="POST" action="{{ route('friends.accept', $friendshipId) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Confirm') }}</button>
                </form>
                <form method="POST" action="{{ route('friends.decline', $friendshipId) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">{{ __('Delete') }}</button>
                </form>
            @elseif ($variant === 'suggestion')
                <form method="POST" action="{{ route('friends.request', $person->uuid) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Add friend') }}</button>
                </form>
            @elseif ($variant === 'friend')
                <form method="POST" action="{{ route('friends.destroy', $friendshipId) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">{{ __('Remove') }}</button>
                </form>
            @endif
        </div>
    </div>
</div>