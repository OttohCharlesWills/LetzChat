@extends('layouts.adapp')

@section('content')
<div class="container" style="max-width: 640px;">

    <h4 class="mb-3">{{ __('Boost a post') }}</h4>

    <div class="pc-card mb-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">{{ __('Wallet balance') }}</span>
        <div>
            <strong>{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</strong>
            <a href="{{ route('wallet.index') }}" class="btn btn-sm btn-outline-primary ms-2">{{ __('Top up') }}</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('ads.store') }}" class="pc-card" id="adCreateForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">{{ __('Post to boost') }}</label>
            <select name="post_id" class="form-select" required>
                <option value="">{{ __('Select a post…') }}</option>
                @foreach ($boostablePosts as $post)
                    <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Str::limit($post->body ?? __('(Image post)'), 60) }}
                    </option>
                @endforeach
            </select>
            @if ($boostablePosts->isEmpty())
                <p class="text-muted small mt-1">{{ __('You need at least one published post before you can boost it.') }}</p>
            @endif
        </div>

        <hr>
        <p class="fw-bold mb-1">{{ __('Budget & Duration') }}</p>
        <p class="text-muted small mb-3">
            {{ __('These are linked — change one and the other updates automatically. This is an estimate based on average reach; the ad stops the moment its budget is spent, even if the duration hasn\'t ended yet.') }}
        </p>

        <div class="mb-3">
            <label class="form-label">{{ __('Budget') }} (NGN)</label>
            <input type="number" id="adBudgetInput" class="form-control" min="100" step="1" value="{{ old('budget', 1000) }}" required>
            <p class="text-muted small mt-1">{{ __('Minimum ₦100.') }}</p>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-4">
                <label class="form-label">{{ __('Weeks') }}</label>
                <input type="number" id="adDurWeeks" class="form-control" min="0" value="0">
            </div>
            <div class="col-4">
                <label class="form-label">{{ __('Days') }}</label>
                <input type="number" id="adDurDays" class="form-control" min="0" value="7">
            </div>
            <div class="col-4">
                <label class="form-label">{{ __('Hours') }}</label>
                <input type="number" id="adDurHours" class="form-control" min="0" max="23" value="0">
            </div>
        </div>

        <p class="text-muted small mb-3" id="adEstimateNote"></p>

        <div class="mb-3">
            <label class="form-label">{{ __('Start') }}</label>
            <input type="datetime-local" id="adStartAt" class="form-control" required>
        </div>

        <input type="hidden" name="budget" id="adBudgetHidden">
        <input type="hidden" name="start_at" id="adStartAtHidden">
        <input type="hidden" name="end_at" id="adEndAtHidden">

        <hr>
        <p class="fw-bold mb-2">{{ __('Audience (optional — leave blank to reach everyone)') }}</p>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">{{ __('Min age') }}</label>
                <input type="number" name="target_min_age" class="form-control" min="13" max="100" value="{{ old('target_min_age') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Max age') }}</label>
                <input type="number" name="target_max_age" class="form-control" min="13" max="100" value="{{ old('target_max_age') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Gender') }}</label>
                <select name="target_gender" class="form-select">
                    <option value="any">{{ __('Any') }}</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Locations') }}</label>
            <input type="text" name="target_locations" class="form-control" value="{{ old('target_locations') }}" placeholder="{{ __('e.g. Port Harcourt, Lagos') }}">
            <p class="text-muted small mt-1">{{ __('Comma-separated. Matches against each viewer\'s profile location.') }}</p>
        </div>

        <button type="submit" class="btn btn-primary w-100" {{ $boostablePosts->isEmpty() ? 'disabled' : '' }}>
            {{ __('Launch ad') }}
        </button>
    </form>

</div>

<script>
(function () {
    const COST_PER_IMPRESSION = {{ $costPerImpression }};
    const IMPRESSIONS_PER_HOUR = {{ $impressionsPerHour }};

    const budgetInput = document.getElementById('adBudgetInput');
    const weeksInput = document.getElementById('adDurWeeks');
    const daysInput = document.getElementById('adDurDays');
    const hoursInput = document.getElementById('adDurHours');
    const startInput = document.getElementById('adStartAt');
    const noteEl = document.getElementById('adEstimateNote');

    const budgetHidden = document.getElementById('adBudgetHidden');
    const startHidden = document.getElementById('adStartAtHidden');
    const endHidden = document.getElementById('adEndAtHidden');

    function totalHours() {
        return (parseFloat(weeksInput.value) || 0) * 168
             + (parseFloat(daysInput.value) || 0) * 24
             + (parseFloat(hoursInput.value) || 0);
    }

    function setDurationFromHours(hours) {
        hours = Math.max(0, hours);
        const weeks = Math.floor(hours / 168);
        hours -= weeks * 168;
        const days = Math.floor(hours / 24);
        hours -= days * 24;

        weeksInput.value = weeks;
        daysInput.value = days;
        hoursInput.value = Math.round(hours);
    }

    let lastEdited = 'duration'; // tracks which side the user touched last

    function recalcFromBudget() {
        lastEdited = 'budget';
        const budget = parseFloat(budgetInput.value) || 0;
        const affordableImpressions = COST_PER_IMPRESSION > 0 ? budget / COST_PER_IMPRESSION : 0;
        const hours = affordableImpressions / IMPRESSIONS_PER_HOUR;
        setDurationFromHours(hours);
        updateNote();
    }

    function recalcFromDuration() {
        lastEdited = 'duration';
        const hours = totalHours();
        const budget = hours * IMPRESSIONS_PER_HOUR * COST_PER_IMPRESSION;
        budgetInput.value = Math.max(100, Math.round(budget));
        updateNote();
    }

    function updateNote() {
        const hours = totalHours();
        const budget = parseFloat(budgetInput.value) || 0;
        noteEl.textContent = `{{ __('Estimated: ₦') }}${budget.toLocaleString()} {{ __('runs for about') }} ${hours.toFixed(1)} {{ __('hours, based on average reach.') }}`;
        syncHiddenFields();
    }

    function syncHiddenFields() {
        budgetHidden.value = budgetInput.value;
        startHidden.value = startInput.value;

        if (startInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(start.getTime() + totalHours() * 3600 * 1000);
            // toISOString gives UTC; format as local datetime string the backend can parse
            const pad = (n) => String(n).padStart(2, '0');
            const endLocal = `${end.getFullYear()}-${pad(end.getMonth() + 1)}-${pad(end.getDate())} ${pad(end.getHours())}:${pad(end.getMinutes())}:00`;
            endHidden.value = endLocal;
        }
    }

    budgetInput.addEventListener('input', recalcFromBudget);
    [weeksInput, daysInput, hoursInput].forEach((el) => el.addEventListener('input', recalcFromDuration));
    startInput.addEventListener('input', syncHiddenFields);

    // Default start = right now, in datetime-local's expected format
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    startInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;

    recalcFromDuration(); // initial calc based on default 7 days

    document.getElementById('adCreateForm').addEventListener('submit', function () {
        syncHiddenFields();
    });
})();
</script>
@endsection