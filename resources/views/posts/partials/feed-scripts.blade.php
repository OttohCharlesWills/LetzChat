
{{--
    Include this once on any page that shows the post feed AND the
    create-post modal. It expects a container with id="feedPostsList"
    wrapping your list of .pc-card posts (see posts.partials.post-card).
--}}
<script>
    // New post created via the modal -> prepend it to the feed, no reload.
    document.addEventListener('post:created', function (e) {
        const list = document.getElementById('feedPostsList');
        if (!list) return;

        list.insertAdjacentHTML('afterbegin', e.detail.html);
    });

    // Delete a post -> remove its card from the DOM, no reload.
    window.deletePost = function (uuid, btnEl) {
        if (!confirm('{{ __('Delete this post?') }}')) return;

        fetch(`/posts/${uuid}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
            .then((response) => {
                if (!response.ok) throw new Error('Delete failed');
                return response.json();
            })
            .then(() => {
                const card = btnEl.closest('.pc-card');
                if (card) card.remove();
            })
            .catch(() => alert('{{ __('Could not delete this post. Please try again.') }}'));
    };
</script>