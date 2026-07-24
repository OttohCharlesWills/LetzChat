<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class BirthdayController extends Controller
{
    /**
     * How many days ahead counts as "upcoming".
     */
    protected int $upcomingWindowDays = 30;

    /**
     * How many days back counts as "recent".
     */
    protected int $recentWindowDays = 7;

    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $friends = $user->friends()
            ->filter(fn ($friend) => ! is_null($friend->date_of_birth));

        $withBirthdayData = $friends->map(function ($friend) use ($today) {
            return $this->buildBirthdayData($friend, $today);
        });

        $upcoming = $withBirthdayData
            ->filter(fn ($d) => $d['days_until'] <= $this->upcomingWindowDays)
            ->sortBy('days_until')
            ->values();

        $recent = $withBirthdayData
            ->filter(fn ($d) => $d['days_since'] > 0 && $d['days_since'] <= $this->recentWindowDays)
            ->sortBy('days_since')
            ->values();

        $featuredIds = $upcoming->pluck('user.id')
            ->merge($recent->pluck('user.id'))
            ->unique();

        $monthGroups = $this->buildMonthGroups($withBirthdayData, $featuredIds, $today);

        return view('friends.birthdays', compact('upcoming', 'recent', 'monthGroups'));
    }

    /**
     * Group the remaining friends (not already shown in upcoming/recent) by
     * birth month, starting with the current month and wrapping around
     * the year — mirrors the "browse all birthdays by month" list on Facebook.
     */
    protected function buildMonthGroups($withBirthdayData, $featuredIds, Carbon $today)
    {
        $remaining = $withBirthdayData->reject(fn ($d) => $featuredIds->contains($d['user']->id));

        $byMonth = $remaining->groupBy(fn ($d) => (int) $d['dob']->format('n'));

        $currentMonth = (int) $today->format('n');

        return collect(range($currentMonth, $currentMonth + 11))
            ->map(fn ($m) => (($m - 1) % 12) + 1)
            ->map(function ($month) use ($byMonth) {
                $friendsInMonth = $byMonth->get($month, collect())
                    ->sortBy(fn ($d) => $d['dob']->day)
                    ->values();

                return [
                    'month' => $month,
                    'label' => Carbon::create()->month($month)->format('F'),
                    'friends' => $friendsInMonth,
                ];
            })
            ->filter(fn ($group) => $group['friends']->isNotEmpty())
            ->values();
    }

    /**
     * Build the birthday metadata for a single friend, relative to $today.
     * Handles Feb 29 by falling back to Feb 28 in non-leap years.
     */
    protected function buildBirthdayData($friend, Carbon $today): array
    {
        $dob = Carbon::parse($friend->date_of_birth);

        $nextBirthday = $this->birthdayInYear($dob, $today->year);
        if ($nextBirthday->lt($today)) {
            $nextBirthday = $this->birthdayInYear($dob, $today->year + 1);
        }

        $lastBirthday = $this->birthdayInYear($dob, $today->year);
        if ($lastBirthday->gt($today)) {
            $lastBirthday = $this->birthdayInYear($dob, $today->year - 1);
        }

        return [
            'user' => $friend,
            'dob' => $dob,
            'is_today' => $nextBirthday->isSameDay($today),
            'next_birthday' => $nextBirthday,
            'days_until' => $today->diffInDays($nextBirthday, false),
            'turning_age' => $nextBirthday->year - $dob->year,
            'last_birthday' => $lastBirthday,
            'days_since' => $lastBirthday->diffInDays($today, false),
            'turned_age' => $lastBirthday->year - $dob->year,
        ];
    }

    /**
     * Send a quick birthday message to a friend from the birthdays page.
     *
     * ASSUMPTION (adjust to match your real schema): this expects a
     * Conversation model with a "direct" type reachable via the user's
     * conversations() relation, and a Message model with
     * conversation_id / sender_id / body columns. Share your Message
     * and Conversation models if these names don't match and I'll fix this up.
     */
    public function sendMessage(Request $request, \App\Models\User $friend)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $conversation = $user->conversations()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $friend->id))
            ->first();

        if (! $conversation) {
            $conversation = \App\Models\Conversation::create([
                'type' => 'direct',
            ]);

            $conversation->participants()->attach([
                $user->id => ['role' => 'member'],
                $friend->id => ['role' => 'member'],
            ]);
        }

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->input('message'),
        ]);

        return back()->with('status', __('Birthday message sent to :name!', ['name' => $friend->first_name]));
    }

    /**
     * Get a friend's birthday date landed on a specific year,
     * safely handling Feb 29 birthdays in non-leap years.
     */
    protected function birthdayInYear(Carbon $dob, int $year): Carbon
    {
        if ($dob->month === 2 && $dob->day === 29 && ! Carbon::create($year)->isLeapYear()) {
            return Carbon::create($year, 2, 28);
        }

        return Carbon::create($year, $dob->month, $dob->day);
    }
}