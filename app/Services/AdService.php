<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\User;
use App\Models\UserActivity;

class AdService
{
    public function __construct(protected WalletService $wallet)
    {
    }

    /**
     * Pick up to $count active ads eligible for this viewer. Random
     * selection among eligible ads — no auction/bidding, kept simple.
     */
    public function pickAdsFor(User $viewer, int $count = 1): \Illuminate\Support\Collection
    {
        return Ad::with(['post.user', 'post.images', 'post.videos'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->whereColumn('spent', '<', 'budget')
            ->inRandomOrder()
            ->limit(20) // pull a pool, then filter precisely in PHP (gender/age/location aren't cleanly indexable together)
            ->get()
            ->filter(fn (Ad $ad) => $ad->isEligibleFor($viewer))
            ->take($count)
            ->values();
    }

    /**
     * Record that an ad was shown to a viewer: logs the event, deducts
     * cost from the ad's spent budget, auto-completes + refunds leftover
     * if this impression exhausts the budget.
     */
    public function recordImpression(Ad $ad, User $viewer): void
    {
        $ad->increment('impressions_count');
        $ad->increment('spent', $ad->cost_per_impression);
        $ad->refresh();

        UserActivity::create([
            'user_id'    => $viewer->id,
            'event_name' => 'ad_impression',
            'context'    => 'feed',
            'metadata'   => ['ad_id' => $ad->id, 'post_id' => $ad->post_id],
        ]);

        if ($ad->remainingBudget() < (float) $ad->cost_per_impression) {
            $this->completeAd($ad);
        }
    }

    public function recordClick(Ad $ad, User $viewer): void
    {
        $ad->increment('clicks_count');

        UserActivity::create([
            'user_id'    => $viewer->id,
            'event_name' => 'ad_click',
            'context'    => 'feed',
            'metadata'   => ['ad_id' => $ad->id, 'post_id' => $ad->post_id],
        ]);
    }

    public function completeAd(Ad $ad): void
    {
        $ad->update(['status' => 'completed']);
        $this->wallet->refundRemaining($ad);
    }

    public function pauseAd(Ad $ad): void
    {
        $ad->update(['status' => 'paused']);
    }

    public function resumeAd(Ad $ad): void
    {
        abort_if(now()->toDateString() > $ad->end_date->toDateString(), 422, 'This ad\'s end date has already passed.');
        abort_if($ad->remainingBudget() <= 0, 422, 'This ad has no remaining budget.');

        $ad->update(['status' => 'active']);
    }
}