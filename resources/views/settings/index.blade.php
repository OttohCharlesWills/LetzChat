@extends('layouts.settingapp')

@section('content')
<div class="st-page container">

    <div class="st-sidebar">
        <div class="st-sidebar-title">{{ __('Settings') }}</div>

        <a href="{{ route('profile.edit') ?? '#' }}" class="st-item">
            <i class="bi bi-person-circle"></i> {{ __('Update Profile') }}
        </a>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-shield-lock-fill"></i> {{ __('Password & Security') }}
        </button>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-person-workspace"></i> {{ __('Account Center') }}
        </button>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-bell-fill"></i> {{ __('Notifications') }}
        </button>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-eye-slash-fill"></i> {{ __('Privacy — Lock Profile') }}
        </button>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-binoculars-fill"></i> {{ __('Profile Viewers') }}
        </button>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-people-fill"></i> {{ __('Followers') }}
        </button>

        <button type="button" class="st-item" disabled title="{{ __('Coming soon') }}">
            <i class="bi bi-slash-circle-fill"></i> {{ __('Blocked Accounts') }}
        </button>

        <div class="st-divider"></div>

        <a href="#" class="st-item" id="stAdminLink">
            <i class="bi bi-gem"></i> {{ __('Admin') }}
        </a>
    </div>

    <div class="st-content">
        <div class="st-card">
            <p class="text-muted mb-0">{{ __('Select a setting from the sidebar.') }}</p>
        </div>
    </div>

</div>

{{-- ---- Fun admin auth loader overlay ---- --}}
<div class="st-admin-overlay" id="stAdminOverlay">
    <div class="st-admin-loader">
        <div class="st-spinner"></div>
        <p id="stAdminOverlayText">{{ __('Authenticating…') }}</p>
    </div>
</div>

<style>
    .st-page {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 20px;
        padding-top: 20px;
    }

    @media (max-width: 800px) {
        .st-page { grid-template-columns: 1fr; }
    }

    .st-sidebar {
        background: var(--nb-bg, #fff);
        border: 1px solid var(--nb-border, #e4e6eb);
        border-radius: 10px;
        padding: 10px;
        height: fit-content;
    }

    .st-sidebar-title {
        font-weight: 700;
        font-size: 1.1rem;
        padding: 8px 10px 14px 10px;
        color: var(--nb-text, #050505);
    }

    .st-item {
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
        color: var(--nb-text, #050505);
        text-decoration: none;
    }

    .st-item:hover:not(:disabled) {
        background: var(--nb-hover, #f0f2f5);
        color: var(--nb-text, #050505);
    }

    .st-item:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .st-item i {
        width: 20px;
        text-align: center;
    }

    .st-divider {
        height: 1px;
        background: var(--nb-border, #e4e6eb);
        margin: 10px 6px;
    }

    .st-content {
        min-width: 0;
    }

    .st-card {
        background: var(--nb-bg, #fff);
        border: 1px solid var(--nb-border, #e4e6eb);
        border-radius: 10px;
        padding: 20px;
    }

    /* ---- Admin fun loader ---- */
    .st-admin-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        z-index: 3000;
        align-items: center;
        justify-content: center;
    }

    .st-admin-overlay.show {
        display: flex;
    }

    .st-admin-loader {
        text-align: center;
        color: #fff;
    }

    .st-spinner {
        width: 42px;
        height: 42px;
        border: 3px solid rgba(255,255,255,0.25);
        border-top-color: #fff;
        border-radius: 50%;
        margin: 0 auto 14px auto;
        animation: st-spin 0.8s linear infinite;
    }

    @keyframes st-spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
(function () {
    const adminLink = document.getElementById('stAdminLink');
    const overlay = document.getElementById('stAdminOverlay');
    const overlayText = document.getElementById('stAdminOverlayText');

    if (!adminLink) return;

    adminLink.addEventListener('click', function (e) {
        e.preventDefault();

        overlay.classList.add('show');
        overlayText.textContent = '{{ __('Authenticating…') }}';

        setTimeout(() => {
            overlayText.textContent = '{{ __('Verifying admin access…') }}';
        }, 700);

        setTimeout(() => {
            window.location.href = "{{ route('admin.flagged-posts.index') }}";
        }, 1500);
    });
})();
</script>
@endsection