<div class="composer-trigger-card">
    <div class="composer-trigger-row">
        <a href="{{ route('profile.show', auth()->user()->uuid) }}">
            @if (auth()->user()->profile_photo)
                <img src="{{ auth()->user()->profile_photo }}" class="composer-trigger-avatar" alt="{{ auth()->user()->first_name }}">
            @else
                <div class="composer-trigger-avatar-fallback">
                    {{ strtoupper(mb_substr(auth()->user()->first_name, 0, 1)) }}
                </div>
            @endif
        </a>

        <button type="button" class="composer-trigger-pill" id="composerTriggerBtn"
            onclick="openCreatePostModal({ groupId: {{ $group->id }}, groupName: '{{ addslashes($group->name) }}' })">
            {{ __("Share An Idea With The Group, :name", ['name' => auth()->user()->first_name]) }}
        </button>
    </div>

</div>

<style>
    .composer-trigger-card {
        background: var(--sb-bg);
        border: 1px solid var(--sb-border);
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }

    .composer-trigger-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .composer-trigger-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--sb-avatar-fallback-bg);
    }

    .composer-trigger-avatar-fallback {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--sb-avatar-fallback-bg);
        color: var(--sb-avatar-fallback-text) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none !important;
        flex-shrink: 0;
    }

    .composer-trigger-pill {
        flex: 1;
        text-align: left;
        background: var(--sb-hover);
        border: none;
        border-radius: 20px;
        padding: 10px 16px;
        color: var(--sb-text-secondary);
        font-size: 0.95rem;
    }

    .composer-trigger-pill:hover {
        background: var(--sb-border);
    }

    .composer-trigger-divider {
        border-top: 1px solid var(--sb-border);
        margin: 12px 0 8px 0;
    }

    .composer-trigger-actions {
        display: flex;
        justify-content: space-between;
        gap: 6px;
    }

    .composer-trigger-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: none;
        border: none;
        border-radius: 6px;
        padding: 8px 6px;
        color: var(--sb-text-secondary);
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .composer-trigger-action:not(:disabled):hover {
        background: var(--sb-hover);
        color: var(--sb-text);
    }

    .composer-trigger-action:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .composer-trigger-icon {
        font-size: 1.05rem;
        line-height: 1;
    }
</style>