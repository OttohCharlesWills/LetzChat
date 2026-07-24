@php
    $quickMessages = [
        __('Happy Birthday, :name! 🎉🎂', ['name' => $friend->first_name]),
        __('HBD! 🎁🎉'),
        __('Enjoy your day! 🥳🎁'),
        __('Happy birthday! Wishing you the best 🎈'),
    ];
    $alwaysOpen = $alwaysOpen ?? false;
@endphp

<div class="quick-messages">
    @foreach ($quickMessages as $msg)
        <button type="button" class="quick-msg-btn" data-message="{{ $msg }}">{{ $msg }}</button>
    @endforeach
</div>

<div class="quick-composer {{ $alwaysOpen ? 'active' : '' }}" id="composer-{{ $id }}">
    <form action="{{ route('friends.birthdays.message', $friend) }}" method="POST" class="d-flex w-100" style="gap:8px;">
        @csrf
        <textarea name="message" rows="1" placeholder="{{ __('Write a message...') }}"></textarea>
        <button type="submit" class="btn-fb btn-fb-primary">{{ __('Send') }}</button>
    </form>
</div>