<?php

namespace App\Services;

use App\Models\BannedWord;
use Illuminate\Support\Facades\Cache;

class ContentModerationService
{
    /**
     * Check a piece of text against the banned word list.
     *
     * Returns:
     *   'status'        => 'clean' | 'flagged' | 'blocked'
     *   'matched_words' => array of the actual banned words found (for admin review UI)
     *
     * 'blocked' wins over 'flagged' if a text matches both severities at once.
     */
    public function check(string $text): array
    {
        $normalized = mb_strtolower($text);
        $matches = [];
        $status = 'clean';

        foreach ($this->wordList() as $entry) {
            $pattern = '/\b' . preg_quote(mb_strtolower($entry['word']), '/') . '\b/u';

            if (preg_match($pattern, $normalized)) {
                $matches[] = $entry['word'];

                if ($entry['severity'] === 'block') {
                    $status = 'blocked';
                } elseif ($status !== 'blocked') {
                    $status = 'flagged';
                }
            }
        }

        return [
            'status'        => $status,
            'matched_words' => $matches,
        ];
    }

    public function isClean(string $text): bool
    {
        return $this->check($text)['status'] === 'clean';
    }

    /**
     * Cached for 6 hours (invalidated immediately on any BannedWord change
     * via the model's booted() hooks) — this runs on every post, comment,
     * and chat message, so we don't want a DB hit each time.
     */
    private function wordList(): array
    {
        return Cache::remember(BannedWord::CACHE_KEY, now()->addHours(6), function () {
            return BannedWord::query()->get(['word', 'severity'])->toArray();
        });
    }
}