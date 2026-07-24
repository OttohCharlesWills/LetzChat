@if ($friend->avatar)
    <img src="{{ asset('storage/'.$friend->avatar) }}" class="bday-avatar" alt="{{ $friend->first_name }}">
@else
    <div class="bday-avatar-fallback">
        {{ strtoupper(mb_substr($friend->first_name, 0, 1)) }}
    </div>
@endif