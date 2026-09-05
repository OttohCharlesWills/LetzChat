<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Post;
use App\Services\AdService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    public function __construct(
        protected AdService $adService,
        protected WalletService $walletService
    ) {
        $this->middleware('auth');
    }

    /**
     * Advertiser's own ads, with running stats.
     */
    public function index(Request $request)
    {
        $ads = $request->user()->ads()
            ->with('post:id,uuid,body,views_count,likes_count')
            ->orderByDesc('created_at')
            ->paginate(15);

        $wallet = $this->walletService->walletFor($request->user());

        return view('ads.index', compact('ads', 'wallet'));
    }

    /**
     * Boost form — pick from the user's own published, non-flagged posts.
     */
        public function create(Request $request)
    {
        $boostablePosts = $request->user()->posts()
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->take(30)
            ->get(['id', 'uuid', 'body']);

        $wallet = $this->walletService->walletFor($request->user());

        $costPerImpression = 0.5; // matches the default on the Ad model/migration
        $impressionsPerHour = \App\Services\AdService::ESTIMATED_IMPRESSIONS_PER_HOUR;

        return view('ads.create', compact('boostablePosts', 'wallet', 'costPerImpression', 'impressionsPerHour'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id'           => ['required', 'exists:posts,id'],
            'budget'            => ['required', 'numeric', 'min:100'],
            'start_at'          => ['required', 'date', 'after_or_equal:now'],
            'end_at'            => ['required', 'date', 'after:start_at'],
            'target_min_age'    => ['nullable', 'integer', 'min:13', 'max:100'],
            'target_max_age'    => ['nullable', 'integer', 'min:13', 'max:100', 'gte:target_min_age'],
            'target_gender'     => ['required', 'in:any,male,female'],
            'target_locations'  => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $post = Post::findOrFail($validated['post_id']);

        abort_unless($post->user_id === $user->id, 403, 'You can only boost your own posts.');
        abort_unless($post->status === 'published', 422, 'Only published posts can be boosted.');

        $locations = $validated['target_locations']
            ? array_values(array_filter(array_map('trim', explode(',', $validated['target_locations']))))
            : null;

        try {
            $ad = DB::transaction(function () use ($user, $post, $validated, $locations) {
                $ad = Ad::create([
                    'user_id'            => $user->id,
                    'post_id'            => $post->id,
                    'status'             => 'active',
                    'budget'             => $validated['budget'],
                    'start_at'           => $validated['start_at'],
                    'end_at'             => $validated['end_at'],
                    'target_min_age'     => $validated['target_min_age'] ?? null,
                    'target_max_age'     => $validated['target_max_age'] ?? null,
                    'target_gender'      => $validated['target_gender'],
                    'target_locations'   => $locations,
                ]);

                $this->walletService->escrowForAd($user, $ad);

                return $ad;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['budget' => $e->getMessage()]);
        }

        return redirect()->route('ads.show', $ad->uuid)->with('status', __('Your ad is live.'));
    }

    public function show(Request $request, Ad $ad)
    {
        abort_unless($ad->user_id === $request->user()->id, 403);

        $ad->load('post.user', 'post.images');

        $ctr = $ad->impressions_count > 0
            ? round(($ad->clicks_count / $ad->impressions_count) * 100, 2)
            : 0;

        return view('ads.show', compact('ad', 'ctr'));
    }

    public function pause(Request $request, Ad $ad)
    {
        abort_unless($ad->user_id === $request->user()->id, 403);

        $this->adService->pauseAd($ad);

        return back()->with('status', __('Ad paused.'));
    }

    public function resume(Request $request, Ad $ad)
    {
        abort_unless($ad->user_id === $request->user()->id, 403);

        $this->adService->resumeAd($ad);

        return back()->with('status', __('Ad resumed.'));
    }

    /**
     * Fired when a viewer clicks a sponsored post in their feed.
     */
    public function recordClick(Request $request, Ad $ad)
    {
        $this->adService->recordClick($ad, $request->user());

        return response()->json(['recorded' => true]);
    }
}