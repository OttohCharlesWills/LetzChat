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

        <a href="{{ route('dashboard.index') }}" class="sb-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
            <span class="sb-icon"><i class="bi bi-house-door-fill"></i></span>
            <span class="sb-label">{{ __('Dashboard') }}</span>
        </a>

        <a href="{{ url('/friends') }}" class="sb-item {{ request()->is('friends*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="bi bi-people-fill"></i></span>
            <span class="sb-label">{{ __('Friends') }}</span>
        </a>

        <a href="{{ url('/groups') }}" class="sb-item {{ request()->is('groups*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="bi bi-collection-fill"></i></span>
            <span class="sb-label">{{ __('Groups') }}</span>
        </a>

        <a href="{{ url('/notifications') }}" class="sb-item {{ request()->is('notifications*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="bi bi-bell-fill"></i></span>
            <span class="sb-label">{{ __('Notifications') }}</span>
        </a>

    </div>
</section>