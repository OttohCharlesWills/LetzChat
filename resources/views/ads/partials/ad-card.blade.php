@include('posts.partials.post-card', [
    'post' => $ad->post,
    'isSponsored' => true,
    'adUuid' => $ad->uuid,
])