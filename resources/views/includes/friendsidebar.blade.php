<section>
    @php
        $current = request()->route()->getName();
    @endphp

    <div class="fnav-wrap">
        <div class="fnav-title">{{ __('Friends') }}</div>

        <a href="{{ route('friends.index') }}" class="fnav-item {{ $current === 'friends.index' ? 'active' : '' }}">
            <span class="fnav-icon"><i class="bi bi-house-fill"></i></span>
            <span>{{ __('Home') }}</span>
        </a>

        <a href="{{ route('friends.requests') }}" class="fnav-item {{ $current === 'friends.requests' ? 'active' : '' }}">
            <span class="fnav-icon"><i class="bi bi-person-plus-fill"></i></span>
            <span>{{ __('Friend requests') }}</span>
        </a>

        <a href="{{ route('friends.birthdays') }}" class="fnav-item {{ $current === 'friends.all' ? 'active' : '' }}">
            <span class="fnav-icon"><i class="bi bi-people-fill"></i></span>
            <span>{{ __('Birthdays') }}</span>
        </a>

        <a href="{{ route('friends.suggestions') }}" class="fnav-item {{ $current === 'friends.suggestions' ? 'active' : '' }}">
            <span class="fnav-icon"><i class="bi bi-person-lines-fill"></i></span>
            <span>{{ __('Suggestions') }}</span>
        </a>

        <a href="{{ route('friends.all') }}" class="fnav-item {{ $current === 'friends.all' ? 'active' : '' }}">
            <span class="fnav-icon"><i class="bi bi-people-fill"></i></span>
            <span>{{ __('All friends') }}</span>
        </a>
    </div>

    <style>
        :root {
            --fnav-bg: #ffffff;
            --fnav-text: #050505;
            --fnav-text-secondary: #65676b;
            --fnav-hover: #f0f2f5;
            --fnav-active-bg: #e7f3ff;
            --fnav-active-text: #e85d3f;
        }

        [data-theme="dark"] {
            --fnav-bg: #3a3b3c;
            --fnav-text: #e4e6eb;
            --fnav-text-secondary: #b0b3b8;
            --fnav-hover: #4e4f50;
            --fnav-active-bg: #263951;
            --fnav-active-text: #e85d3f;
        }

        .fnav-wrap {
            background: var(--fnav-bg);
            border-radius: 10px;
            padding: 8px;
            height:85.5vh;
        }

        .fnav-title {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--fnav-text);
            padding: 6px 10px 12px 10px;
        }

        .fnav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--fnav-text);
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.15s ease;
        }

        .fnav-item:hover {
            background: var(--fnav-hover);
            color: var(--fnav-text);
        }

        .fnav-item.active {
            background: var(--fnav-active-bg);
            color: var(--fnav-active-text);
        }

        .fnav-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--fnav-hover);
            color: var(--fnav-text-secondary);
            font-size: 1rem;
        }

        .fnav-item.active .fnav-icon {
            background: var(--fnav-active-bg);
            color: var(--fnav-active-text);
        }
    </style>
</section>