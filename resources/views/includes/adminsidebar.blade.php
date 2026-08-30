<section id="user-sidebar" data-turbo-permanent>
    <div class="sb-wrap">

        {{-- Profile link {{ route('profile.show', auth()->user()->username) }} --}}
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

        <a href="{{ url('/friends') }}" class="sb-item">
            <span class="sb-icon"><i class="bi bi-people-fill"></i></span>
            <span class="sb-label">{{ __('Friends') }}</span>
        </a>

        <a href="{{ url('/dashboard') }}" class="sb-item">
            <span class="sb-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="sb-label">{{ __('Dashboard') }}</span>
        </a>
        
        <a href="{{ url('/groups') }}" class="sb-item">
            <span class="sb-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="sb-label">{{ __('Groups') }}</span>
        </a>
        
        <a href="{{ url('/notifications') }}" class="sb-item">
            <span class="sb-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="sb-label">{{ __('Notifications') }}</span>
        </a>

        @if (auth()->user()->is_admin)
            <div class="sb-divider"></div>

            <a href="{{ route('admin.flagged-posts.index') }}" class="sb-item">
                <span class="sb-icon"><i class="bi bi-flag-fill"></i></span>
                <span class="sb-label">{{ __('Flagged Posts') }}</span>
            </a>

            <a href="{{ route('admin.banned-words.index') }}" class="sb-item">
                <span class="sb-icon"><i class="bi bi-shield-exclamation"></i></span>
                <span class="sb-label">{{ __('Banned Words') }}</span>
            </a>
        @endif

    </div>
</section>