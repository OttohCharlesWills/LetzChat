<div class="db-sidebar" id="dbSidebar">

    <a href="{{ route('dashboard.index') }}" class="db-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
        <i class="bi bi-house-door-fill"></i> {{ __('Home') }}
    </a>

    {{-- ---- Insights ---- --}}
    <button type="button" class="db-group-toggle" data-target="dbGroupInsights">
        <i class="bi bi-graph-up-arrow"></i>
        <span>{{ __('Insights') }}</span>
        <i class="bi bi-chevron-down db-chevron"></i>
    </button>
    <div class="db-group" id="dbGroupInsights">
        <a href="{{ route('dashboard.index') }}" class="db-subitem {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
            <i class="bi bi-eye-fill"></i> {{ __('Views') }}
        </a>
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-cash-coin"></i> {{ __('Earnings') }}
        </button>
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-chat-dots-fill"></i> {{ __('Engagement') }}
        </button>
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-people-fill"></i> {{ __('Audience') }}
        </button>
    </div>

    {{-- ---- Content ---- --}}
    <button type="button" class="db-group-toggle" data-target="dbGroupContent">
        <i class="bi bi-grid-fill"></i>
        <span>{{ __('Content') }}</span>
        <i class="bi bi-chevron-down db-chevron"></i>
    </button>
    <div class="db-group" id="dbGroupContent">
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-collection-fill"></i> {{ __('Content Library') }}
        </button>
    </div>

    {{-- ---- Monetisation ---- --}}
    <button type="button" class="db-group-toggle" data-target="dbGroupMonetisation">
        <i class="bi bi-currency-exchange"></i>
        <span>{{ __('Monetisation') }}</span>
        <i class="bi bi-chevron-down db-chevron"></i>
    </button>
    <div class="db-group" id="dbGroupMonetisation">
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-bar-chart-fill"></i> {{ __('Overview') }}
        </button>
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-cash-stack"></i> {{ __('Content monetisation') }}
        </button>
    </div>

    {{-- ---- Engagement ---- --}}
    <button type="button" class="db-group-toggle" data-target="dbGroupEngagement">
        <i class="bi bi-chat-square-heart-fill"></i>
        <span>{{ __('Engagement') }}</span>
        <i class="bi bi-chevron-down db-chevron"></i>
    </button>
    <div class="db-group" id="dbGroupEngagement">
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-chat-left-text-fill"></i> {{ __('Comments manager') }}
        </button>
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-shield-check"></i> {{ __('Moderation Assist') }}
        </button>
        <button type="button" class="db-subitem" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-clock-history"></i> {{ __('Activity log') }}
        </button>
    </div>

    <div class="db-divider"></div>

    <button type="button" class="db-item" disabled title="{{ __('Coming soon') }}">
        <i class="bi bi-tools"></i> {{ __('All tools') }}
    </button>

</div>

<style>
    .db-sidebar {
        background: var(--nb-bg, #fff);
        border: 1px solid var(--nb-border, #e4e6eb);
        border-radius: 10px;
        padding: 10px;
        height: fit-content;
    }

    .db-item,
    .db-group-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--nb-text, #050505);
        text-decoration: none;
    }

    .db-item i:first-child,
    .db-group-toggle i:first-child {
        width: 20px;
        text-align: center;
        flex-shrink: 0;
    }

    .db-item:hover:not(:disabled),
    .db-group-toggle:hover {
        background: var(--nb-hover, #f0f2f5);
        color: var(--nb-text, #050505);
    }

    .db-item.active {
        background: var(--nb-hover, #f0f2f5);
        color: var(--nb-accent, #0d6efd);
    }

    .db-item:disabled,
    .db-subitem:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .db-group-toggle span {
        flex: 1;
    }

    .db-chevron {
        font-size: 0.75rem;
        transition: transform 0.15s ease;
        flex-shrink: 0;
    }

    .db-group-toggle.collapsed .db-chevron {
        transform: rotate(-90deg);
    }

    .db-group {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        max-height: 500px;
        transition: max-height 0.2s ease;
    }

    .db-group.collapsed {
        max-height: 0;
    }

    .db-subitem {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 8px 10px 8px 34px;
        border-radius: 8px;
        font-size: 0.86rem;
        color: var(--nb-text-secondary, #65676b);
        text-decoration: none;
    }

    .db-subitem:hover:not(:disabled) {
        background: var(--nb-hover, #f0f2f5);
        color: var(--nb-text, #050505);
    }

    .db-subitem.active {
        color: var(--nb-accent, #0d6efd);
        font-weight: 600;
    }

    .db-divider {
        height: 1px;
        background: var(--nb-border, #e4e6eb);
        margin: 10px 6px;
    }
</style>

<script>
(function () {
    document.querySelectorAll('.db-group-toggle').forEach((toggle) => {
        const target = document.getElementById(toggle.dataset.target);
        if (!target) return;

        toggle.addEventListener('click', function () {
            toggle.classList.toggle('collapsed');
            target.classList.toggle('collapsed');
        });
    });
})();
</script>