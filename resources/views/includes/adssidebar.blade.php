<section id="user-sidebar" data-turbo-permanent>
    <div class="sb-wrap">

        {{-- Profile --}}
        <a href="{{ route('profile.show', auth()->user()->uuid) }}" class="sb-item sb-profile">
            @if (auth()->user()->profile_photo)
                <img src="{{ auth()->user()->profile_photo }}"
                    alt="{{ auth()->user()->first_name }}"
                    class="sb-avatar">
            @else
                <span class="sb-avatar sb-avatar-fallback">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </span>
            @endif
            <span class="sb-label">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
        </a>

        <a href="{{ url('/ads') }}" class="sb-item {{ request()->is('ads*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="bi bi-megaphone-fill"></i></span>
            <span class="sb-label">{{ __('Ads') }}</span>
        </a>

        <a href="{{ url('/wallet') }}" class="sb-item {{ request()->is('wallet*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="bi bi-wallet2"></i></span>
            <span class="sb-label">{{ __('Wallet') }}</span>
        </a>

    </div>
</section>