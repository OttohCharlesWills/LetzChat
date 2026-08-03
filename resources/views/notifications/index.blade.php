@extends('layouts.app')

@section('content')

<div class="ntf-page">
    <div class="ntf-page-header">
        <h1>{{ __('Notifications') }}</h1>
        <button type="button" id="ntfPageMarkAllBtn" class="ntf-page-markall">{{ __('Mark all as read') }}</button>
    </div>

    <div class="ntf-page-list" id="ntfPageList">
        @include('notifications.partials.list', ['notifications' => $notifications])
    </div>

    <div class="ntf-page-pagination">
        {{ $notifications->links() }}
    </div>
</div>

<style>
    .ntf-page {
        max-width: 640px;
        margin: 24px auto;
        background: var(--pc-bg, #fff);
        border: 1px solid var(--pc-border, #e4e6eb);
        border-radius: 10px;
        overflow: hidden;
    }

    .ntf-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--pc-border, #e4e6eb);
    }

    .ntf-page-header h1 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: var(--pc-text, #050505);
    }

    .ntf-page-markall {
        background: none;
        border: none;
        color: #0d6efd;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .ntf-page-pagination {
        padding: 16px 20px;
    }
</style>

<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const list = document.getElementById('ntfPageList');
        const markAllBtn = document.getElementById('ntfPageMarkAllBtn');

        list.addEventListener('click', function (e) {
            const item = e.target.closest('.ntf-item');
            if (!item || !item.classList.contains('ntf-unread')) return;

            const id = item.dataset.notificationId;
            item.classList.remove('ntf-unread');
            item.querySelector('.ntf-dot')?.remove();

            fetch(`/notifications/${id}/read`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            }).catch(() => {});
        });

        markAllBtn.addEventListener('click', function () {
            fetch('{{ route("notifications.read-all") }}', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            }).then(() => {
                document.querySelectorAll('.ntf-unread').forEach(el => {
                    el.classList.remove('ntf-unread');
                    el.querySelector('.ntf-dot')?.remove();
                });
            }).catch(() => {});
        });
    })();
</script>
@endsection