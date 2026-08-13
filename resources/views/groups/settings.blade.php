@extends('layouts.grouplist')

@section('content')
<div class="gs-page">
    <div class="gs-header">
        <a href="{{ route('groups.show', $group) }}" class="gs-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="gs-eyebrow">{{ $group->name }}</div>
            <div class="gs-title">{{ __('Group Settings') }}</div>
        </div>
    </div>

    @if (session('status'))
        <div class="gs-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('groups.settings.update', $group) }}" class="gs-form">
        @csrf
        @method('PATCH')

        {{-- ================= POSTING PERMISSIONS ================= --}}
        <div class="gs-section">
            <div class="gs-section-title">{{ __('Who can post') }}</div>
            <div class="gs-section-subtitle">{{ __('Control who is allowed to create posts in this group.') }}</div>

            <label class="gs-radio-row">
                <input type="radio" name="post_permission" value="everyone"
                    {{ old('post_permission', $group->post_permission) === 'everyone' ? 'checked' : '' }}>
                <div>
                    <div class="gs-radio-label">{{ __('Everyone') }}</div>
                    <div class="gs-radio-desc">{{ __('Any member of the group can create a post.') }}</div>
                </div>
            </label>

            <label class="gs-radio-row">
                <input type="radio" name="post_permission" value="admin_only"
                    {{ old('post_permission', $group->post_permission) === 'admin_only' ? 'checked' : '' }}>
                <div>
                    <div class="gs-radio-label">{{ __('Only admins and moderators') }}</div>
                    <div class="gs-radio-desc">{{ __('Regular members cannot post.') }}</div>
                </div>
            </label>

            <div class="gs-toggle-row" id="gsApprovalRow">
                <label class="gs-switch">
                    <input type="checkbox" name="require_post_approval" value="1"
                        {{ old('require_post_approval', $group->require_post_approval) ? 'checked' : '' }}>
                    <span class="gs-switch-track"><span class="gs-switch-thumb"></span></span>
                </label>
                <div>
                    <div class="gs-radio-label">{{ __('Require approval for member posts') }}</div>
                    <div class="gs-radio-desc">{{ __("Member posts won't appear until an admin or moderator approves them. Doesn't apply to your own posts.") }}</div>
                </div>
            </div>
        </div>

        {{-- ================= JOIN APPROVAL ================= --}}
        <div class="gs-section">
            <div class="gs-section-title">{{ __('Who can join') }}</div>
            <div class="gs-section-subtitle">{{ __('Control whether new members need approval before joining.') }}</div>

            <label class="gs-radio-row">
                <input type="radio" name="join_approval" value="automatic"
                    {{ old('join_approval', $group->join_approval) === 'automatic' ? 'checked' : '' }}>
                <div>
                    <div class="gs-radio-label">{{ __('Automatic') }}</div>
                    <div class="gs-radio-desc">{{ __('Anyone who requests to join is added instantly.') }}</div>
                </div>
            </label>

            <label class="gs-radio-row">
                <input type="radio" name="join_approval" value="manual"
                    {{ old('join_approval', $group->join_approval) === 'manual' ? 'checked' : '' }}>
                <div>
                    <div class="gs-radio-label">{{ __('Require approval') }}</div>
                    <div class="gs-radio-desc">{{ __('New join requests wait in a queue until an admin or moderator approves them.') }}</div>
                </div>
            </label>
        </div>

        <button type="submit" class="gs-save-btn">{{ __('Save changes') }}</button>
    </form>
</div>

<style>
    .gs-page {
        max-width: 640px;
        margin: 24px auto;
    }

    .gs-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .gs-back {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--pc-hover, #f0f2f5);
        color: var(--pc-text, #050505);
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        flex-shrink: 0;
    }

    .gs-eyebrow {
        font-size: 0.78rem;
        color: var(--pc-text-secondary, #65676b);
        font-weight: 600;
    }

    .gs-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--pc-text, #050505);
    }

    .gs-status {
        background: #d1e7dd;
        color: #0f5132;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.85rem;
        margin-bottom: 16px;
    }

    .gs-section {
        background: var(--pc-bg, #fff);
        border: 1px solid var(--pc-border, #e4e6eb);
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 16px;
    }

    .gs-section-title {
        font-weight: 700;
        font-size: 1.02rem;
        color: var(--pc-text, #050505);
    }

    .gs-section-subtitle {
        font-size: 0.82rem;
        color: var(--pc-text-secondary, #65676b);
        margin-bottom: 14px;
    }

    .gs-radio-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        cursor: pointer;
        border-top: 1px solid var(--pc-border, #e4e6eb);
    }

    .gs-radio-row:first-of-type {
        border-top: none;
    }

    .gs-radio-row input[type="radio"] {
        margin-top: 3px;
        flex-shrink: 0;
    }

    .gs-radio-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--pc-text, #050505);
    }

    .gs-radio-desc {
        font-size: 0.8rem;
        color: var(--pc-text-secondary, #65676b);
        margin-top: 2px;
    }

    .gs-toggle-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 0 4px;
        margin-top: 4px;
        border-top: 1px solid var(--pc-border, #e4e6eb);
    }

    .gs-switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .gs-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .gs-switch-track {
        position: absolute;
        inset: 0;
        background: var(--pc-border, #ccc);
        border-radius: 22px;
        transition: background 0.15s ease;
    }

    .gs-switch-thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.15s ease;
    }

    .gs-switch input:checked + .gs-switch-track {
        background: #0d6efd;
    }

    .gs-switch input:checked + .gs-switch-track .gs-switch-thumb {
        transform: translateX(18px);
    }

    .gs-save-btn {
        width: 100%;
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .gs-save-btn:hover {
        background: #0a58ca;
    }
</style>

<script>
    (function () {
        // "Require approval for member posts" only makes sense when
        // regular members can post at all — disable it visually when
        // post_permission is set to admin_only.
        const radios = document.querySelectorAll('input[name="post_permission"]');
        const approvalRow = document.getElementById('gsApprovalRow');
        const approvalCheckbox = approvalRow.querySelector('input[type="checkbox"]');

        function syncApprovalAvailability() {
            const isAdminOnly = document.querySelector('input[name="post_permission"]:checked')?.value === 'admin_only';
            approvalRow.style.opacity = isAdminOnly ? '0.45' : '1';
            approvalCheckbox.disabled = isAdminOnly;
        }

        radios.forEach(r => r.addEventListener('change', syncApprovalAvailability));
        syncApprovalAvailability();
    })();
</script>
@endsection