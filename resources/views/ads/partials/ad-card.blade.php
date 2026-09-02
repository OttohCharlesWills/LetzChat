<div class="pc-card" data-ad-id="{{ $ad->uuid }}">
    <div class="pc-header">
        @if ($ad->post->user->profile_photo)
            <img src="{{ $ad->post->user->profile_photo }}" class="pc-avatar">
        @else
            <div class="pc-avatar-fallback">{{ strtoupper(substr($ad->post->user->first_name, 0, 1)) }}</div>
        @endif

        <div class="pc-header-body">
            <span class="pc-author">{{ $ad->post->user->first_name }} {{ $ad->post->user->last_name }}</span>
            <div class="pc-meta"><i class="bi bi-megaphone-fill"></i> {{ __('Sponsored') }}</div>
        </div>
    </div>

    <a href="{{ route('profile.show', $ad->post->user->uuid) }}"
       class="ad-click-link"
       data-ad-uuid="{{ $ad->uuid }}"
       style="text-decoration:none;color:inherit;">
        <div class="pc-body">{{ $ad->post->body }}</div>

        @if ($ad->post->images->isNotEmpty())
            <div class="pc-images pc-images-count-1">
                <div class="pc-image-item">
                    <img src="{{ $ad->post->images->first()->url }}" alt="" loading="lazy">
                </div>
            </div>
        @endif
    </a>
</div>

@once
<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('.ad-click-link');
    if (!link) return;

    fetch(`/ads/${link.dataset.adUuid}/click`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    }).catch(() => {});
});

const seenAds = new Set();

const adImpressionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const el = entry.target;
        const adUuid = el.dataset.adId;

        if (!adUuid || seenAds.has(adUuid)) return;
        seenAds.add(adUuid);

        fetch(`/ads/${adUuid}/impression`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        }).catch(() => {});

        adImpressionObserver.unobserve(el);
    });
}, { threshold: 0.6 });

function observeAdCards(root = document) {
    root.querySelectorAll('.pc-card[data-ad-id]').forEach((el) => {
        if (!seenAds.has(el.dataset.adId)) {
            adImpressionObserver.observe(el);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => observeAdCards());

// If your feed loads more posts dynamically (infinite scroll), call
// observeAdCards() again after new content is appended to the DOM.
</script>
@endonce