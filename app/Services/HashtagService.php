<?php

namespace App\Services;

use App\Models\Hashtag;
use Illuminate\Database\Eloquent\Model;

class HashtagService
{
    public function extract(string $text): array
    {
        preg_match_all('/#([\p{L}\p{N}_]{2,64})/u', $text, $matches);

        return collect($matches[1])
            ->map(fn ($tag) => mb_strtolower($tag))
            ->filter(fn ($tag) => preg_match('/\p{L}/u', $tag))
            ->unique()
            ->values()
            ->all();
    }

    public function syncFor(Model $model, string $text): void
    {
        $names = $this->extract($text);

        $hashtagIds = collect($names)->map(
            fn ($name) => Hashtag::firstOrCreate(['name' => $name])->id
        );

        $currentIds = $model->hashtags()->pluck('hashtags.id');

        $toAdd = $hashtagIds->diff($currentIds);
        $toRemove = $currentIds->diff($hashtagIds);

        if ($toAdd->isNotEmpty()) {
            $model->hashtags()->attach($toAdd);
            Hashtag::whereIn('id', $toAdd)->increment('usage_count');
        }

        if ($toRemove->isNotEmpty()) {
            $model->hashtags()->detach($toRemove);
            Hashtag::whereIn('id', $toRemove)->decrement('usage_count');
        }
    }

    /**
     * Escape text, then turn #hashtags into clickable links. Use this
     * anywhere post/comment body text is displayed — never on raw
     * unescaped input.
     */
    public function linkify(string $text): string
    {
        $escaped = e($text);

        return preg_replace_callback('/#([\p{L}\p{N}_]{2,64})/u', function ($m) {
            if (! preg_match('/\p{L}/u', $m[1])) {
                return $m[0]; // pure-number tags like "#2024" stay plain text
            }

            $name = mb_strtolower($m[1]);
            $url = route('hashtags.show', $name);

            return '<a href="' . $url . '" class="pc-hashtag-link">#' . $m[1] . '</a>';
        }, $escaped);
    }
}