<style>
    :root {
        --fp-bg: #ffffff;
        --fp-text: #050505;
        --fp-text-secondary: #65676b;
        --fp-border: #e4e6eb;
        --fp-avatar-fallback-bg: #0d6efd;
        --fp-avatar-fallback-text: #ffffff;
    }

    [data-theme="dark"] {
        --fp-bg: #3a3b3c;
        --fp-text: #e4e6eb;
        --fp-text-secondary: #b0b3b8;
        --fp-border: #4b4c4d;
        --fp-avatar-fallback-bg: #4599ff;
        --fp-avatar-fallback-text: #050505;
    }

    .fp-section {
        background: var(--fp-bg);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .fp-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .fp-section-title {
        font-weight: 700;
        font-size: 1.15rem;
        color: var(--fp-text);
    }

    .fp-see-all {
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
    }

    .pc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 14px;
    }

    .pc-card {
        background: var(--fp-bg);
        border: 1px solid var(--fp-border);
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .pc-photo-link {
        display: block;
        width: 100%;
        aspect-ratio: 1 / 1;
    }

    .pc-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pc-photo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--fp-avatar-fallback-bg);
        color: var(--fp-avatar-fallback-text);
        font-size: 2.2rem;
        font-weight: 700;
    }

    .pc-body {
        padding: 10px;
    }

    .pc-name {
        display: block;
        font-weight: 700;
        color: var(--fp-text);
        text-decoration: none;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pc-mutual {
        font-size: 0.8rem;
        color: var(--fp-text-secondary);
        margin-bottom: 8px;
    }

    .pc-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
</style>