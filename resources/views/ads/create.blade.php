@extends('layouts.app')

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

    <form method="POST" action="{{ route('ads.store') }}" class="pc-card">
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

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('Budget') }} (NGN)</label>
                <input type="number" name="budget" class="form-control" min="100" step="1" value="{{ old('budget', 1000) }}" required>
                <p class="text-muted small mt-1">{{ __('Minimum ₦100.') }}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Cost per view') }}</label>
                <input type="text" class="form-control" value="₦0.50 (flat rate)" disabled>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('Start date') }}</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('End date') }}</label>
                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', now()->addDays(7)->toDateString()) }}" required>
            </div>
        </div>

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
@endsection