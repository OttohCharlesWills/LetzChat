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
     * Rough estimate of how many ad impressions the whole platform serves
     * per hour, on average. Purely a planning estimate for the advertiser's
     * budget/duration calculator — actual serving is always governed by
     * real traffic and stops the moment the budget is spent, whichever
     * comes first. Revisit this number periodically as real usage data
     * comes in (the same user_activities table backing this could later
     * compute a rolling real average instead of a hardcoded guess).
     */
    public const ESTIMATED_IMPRESSIONS_PER_HOUR = 50;

    public function estimatedHoursForBudget(float $budget, float $costPerImpression): float
    {
        if ($costPerImpression <= 0) {
            return 0;
        }

        $affordableImpressions = $budget / $costPerImpression;

        return round($affordableImpressions / self::ESTIMATED_IMPRESSIONS_PER_HOUR, 1);
    }

    public function estimatedBudgetForHours(float $hours, float $costPerImpression): float
    {
        return round($hours * self::ESTIMATED_IMPRESSIONS_PER_HOUR * $costPerImpression, 2);
    }

    /**
     * Pick up to $count active ads eligible for this viewer. Random
     * selection among eligible ads — no auction/bidding, kept simple.
     */
    public function pickAdsFor(User $viewer, int $count = 1): \Illuminate\Support\Collection
    {
        return Ad::with(['post.user', 'post.images', 'post.videos'])
            ->where('status', 'active')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereColumn('spent', '<', 'budget')
            ->inRandomOrder()
            ->limit(20)
            ->get()
            ->filter(fn (Ad $ad) => $ad->isEligibleFor($viewer))
            ->take($count)
            ->values();
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
        abort_if(now()->toDateString() > $ad->end_at->toDateString(), 422, 'This ad\'s end date has already passed.');
        abort_if($ad->remainingBudget() <= 0, 422, 'This ad has no remaining budget.');

        $ad->update(['status' => 'active']);
    }
}