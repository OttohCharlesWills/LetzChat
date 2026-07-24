@extends('layouts.group')

@section('content')
<div class="gc-page">

    <div class="gc-layout">

        {{-- ================= LEFT: FORM ================= --}}
        <div class="gc-form-col">
            <a href="{{ route('groups.index') }}" class="gc-back">
                <i class="bi bi-x-lg"></i>
            </a>

            <h1 class="gc-heading">{{ __('Groups') }} &rsaquo; {{ __('Create group') }}</h1>
            <div class="gc-heading-main">{{ __('Create group') }}</div>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('groups.store') }}" id="gcForm">
                @csrf

                <div class="gc-creator-row">
                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="gc-creator-avatar">
                    @else
                        <span class="gc-creator-avatar gc-creator-avatar-fallback">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                        </span>
                    @endif
                    <div>
                        <div class="gc-creator-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                        <div class="gc-creator-role">{{ __('Admin') }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <input type="text" id="gcNameInput" name="name" class="form-control gc-name-input"
                           placeholder="{{ __('Group name') }}" value="{{ old('name') }}" maxlength="255" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Choose privacy') }}</label>
                    <select id="gcPrivacySelect" name="privacy" class="form-select">
                        <option value="public" {{ old('privacy', 'public') === 'public' ? 'selected' : '' }}>
                            {{ __('Public — Anyone can see who\'s in the group and what they post') }}
                        </option>
                        <option value="private" {{ old('privacy') === 'private' ? 'selected' : '' }}>
                            {{ __('Private — Only members can see who\'s in the group and what they post') }}
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="gcDescription" class="form-label">{{ __('Description') }} <span class="text-muted">({{ __('optional') }})</span></label>
                    <textarea id="gcDescription" name="description" class="form-control" rows="3" maxlength="2000">{{ old('description') }}</textarea>
                </div>

                <div class="gc-toggle-row">
                    <div>
                        <div class="gc-toggle-title">{{ __('Invite followers') }}</div>
                        <div class="gc-toggle-desc">{{ __("Send one-off group invitations to current, active followers of your profile.") }}</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="gcInviteFollowers" checked>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3" id="gcSubmitBtn" disabled>
                    {{ __('Create') }}
                </button>
            </form>
        </div>

        {{-- ================= RIGHT: LIVE PREVIEW ================= --}}
        <div class="gc-preview-col">
            <div class="gc-preview-label">{{ __('Desktop preview') }}</div>

            <div class="gc-preview-card">
                <div class="gc-preview-cover"></div>

                <div class="gc-preview-body">
                    <div class="gc-preview-name" id="gcPreviewName">{{ __('Group name') }}</div>
                    <div class="gc-preview-meta">
                        <span id="gcPreviewPrivacy">{{ __('Public') }}</span> &middot; 1 {{ __('member') }}
                    </div>

                    <div class="gc-preview-tabs">
                        <span class="gc-preview-tab active">{{ __('About') }}</span>
                        <span class="gc-preview-tab">{{ __('Posts') }}</span>
                        <span class="gc-preview-tab">{{ __('Members') }}</span>
                        <span class="gc-preview-tab">{{ __('Events') }}</span>
                    </div>

                    <div class="gc-preview-composer">
                        @if (auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="gc-preview-composer-avatar">
                        @else
                            <span class="gc-preview-composer-avatar gc-creator-avatar-fallback">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="gc-preview-composer-input">{{ __("What's on your mind?") }}</span>
                    </div>

                    <div class="gc-preview-about">
                        <div class="gc-preview-about-title">{{ __('About') }}</div>
                        <div class="gc-preview-about-text" id="gcPreviewDescription">
                            {{ __('Add a description so people know what this group is about.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    :root {
        --gc-bg: #ffffff;
        --gc-text: #050505;
        --gc-text-secondary: #65676b;
        --gc-border: #e4e6eb;
        --gc-hover: #f0f2f5;
        --gc-avatar-fallback-bg: #0d6efd;
        --gc-avatar-fallback-text: #ffffff;
    }

    [data-theme="dark"] {
        --gc-bg: #242526;
        --gc-text: #e4e6eb;
        --gc-text-secondary: #b0b3b8;
        --gc-border: #3e4042;
        --gc-hover: #3a3b3c;
        --gc-avatar-fallback-bg: #4599ff;
        --gc-avatar-fallback-text: #050505;
    }

    .gc-layout {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .gc-layout {
            grid-template-columns: 1fr;
        }
    }

    .gc-form-col {
        background: var(--gc-bg);
        border-radius: 10px;
        padding: 20px;
        position: relative;
    }

    .gc-back {
        position: absolute;
        top: 20px;
        right: 20px;
        color: var(--gc-text-secondary);
        font-size: 1.1rem;
    }

    .gc-heading {
        font-size: 0.85rem;
        color: var(--gc-text-secondary);
        margin-bottom: 2px;
    }

    .gc-heading-main {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--gc-text);
        margin-bottom: 16px;
    }

    .gc-creator-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }

    .gc-creator-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .gc-creator-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gc-avatar-fallback-bg);
        color: var(--gc-avatar-fallback-text);
        font-weight: 700;
    }

    .gc-creator-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--gc-text);
    }

    .gc-creator-role {
        font-size: 0.78rem;
        color: var(--gc-text-secondary);
    }

    .gc-name-input {
        font-size: 1rem;
    }

    .gc-toggle-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid var(--gc-border);
    }

    .gc-toggle-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--gc-text);
    }

    .gc-toggle-desc {
        font-size: 0.8rem;
        color: var(--gc-text-secondary);
        max-width: 280px;
    }

    /* ---------- Preview panel ---------- */
    .gc-preview-label {
        font-weight: 700;
        color: var(--gc-text-secondary);
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .gc-preview-card {
        background: var(--gc-bg);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--gc-border);
    }

    .gc-preview-cover {
        height: 140px;
        background: linear-gradient(135deg, #dfe3e8, #c3c9d1);
    }

    .gc-preview-body {
        padding: 16px 20px;
    }

    .gc-preview-name {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--gc-text);
        word-break: break-word;
    }

    .gc-preview-meta {
        color: var(--gc-text-secondary);
        font-size: 0.88rem;
        margin-bottom: 12px;
    }

    .gc-preview-tabs {
        display: flex;
        gap: 18px;
        border-bottom: 1px solid var(--gc-border);
        padding-bottom: 10px;
        margin-bottom: 14px;
    }

    .gc-preview-tab {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--gc-text-secondary);
    }

    .gc-preview-tab.active {
        color: var(--gc-text);
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
        margin-bottom: -11px;
    }

    .gc-preview-composer {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--gc-hover);
        border-radius: 20px;
        padding: 10px 16px;
        margin-bottom: 16px;
    }

    .gc-preview-composer-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        font-size: 0.8rem;
    }

    .gc-preview-composer-input {
        color: var(--gc-text-secondary);
        font-size: 0.9rem;
    }

    .gc-preview-about {
        background: var(--gc-hover);
        border-radius: 10px;
        padding: 14px;
    }

    .gc-preview-about-title {
        font-weight: 700;
        color: var(--gc-text);
        margin-bottom: 6px;
    }

    .gc-preview-about-text {
        font-size: 0.88rem;
        color: var(--gc-text-secondary);
    }
</style>

<script>
    (function () {
        const nameInput = document.getElementById('gcNameInput');
        const privacySelect = document.getElementById('gcPrivacySelect');
        const descriptionInput = document.getElementById('gcDescription');
        const submitBtn = document.getElementById('gcSubmitBtn');

        const previewName = document.getElementById('gcPreviewName');
        const previewPrivacy = document.getElementById('gcPreviewPrivacy');
        const previewDescription = document.getElementById('gcPreviewDescription');

        function updatePreview() {
            previewName.textContent = nameInput.value.trim() || '{{ __('Group name') }}';
            previewPrivacy.textContent = privacySelect.options[privacySelect.selectedIndex].text.split(' — ')[0];
            previewDescription.textContent = descriptionInput.value.trim()
                || '{{ __('Add a description so people know what this group is about.') }}';

            submitBtn.disabled = nameInput.value.trim().length === 0;
        }

        nameInput.addEventListener('input', updatePreview);
        privacySelect.addEventListener('change', updatePreview);
        descriptionInput.addEventListener('input', updatePreview);

        updatePreview();
    })();
</script>
@endsection