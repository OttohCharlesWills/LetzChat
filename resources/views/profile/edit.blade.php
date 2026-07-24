@extends('layouts.profile')

@section('content')
<div class="pf-edit-page">

    <div class="pf-card">
        <div class="pf-card-title">{{ __('Edit Profile') }}</div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                           value="{{ old('first_name', $user->first_name) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                           value="{{ old('last_name', $user->last_name) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">{{ __('Username') }}</label>
                <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="text" id="username" name="username" class="form-control"
                           value="{{ old('username', $user->username) }}"
                           pattern="[A-Za-z0-9_.]+" required>
                </div>
                <div class="form-text">{{ __('Letters, numbers, dots and underscores only.') }}</div>
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">{{ __('Bio') }}</label>
                <textarea id="bio" name="bio" class="form-control" rows="3" maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="location" class="form-label">{{ __('Lives in') }}</label>
                    <input type="text" id="location" name="location" class="form-control"
                           value="{{ old('location', $user->location) }}" placeholder="{{ __('e.g. Port Harcourt, Nigeria') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="education" class="form-label">{{ __('Education') }}</label>
                    <input type="text" id="education" name="education" class="form-control"
                           value="{{ old('education', $user->education) }}" placeholder="{{ __('e.g. University of Lagos') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">{{ __('Gender') }}</label>
                <div class="btn-group w-100" role="group">
                    @foreach (['male' => __('Male'), 'female' => __('Female'), 'other' => __('Other')] as $value => $label)
                        <input type="radio" class="btn-check" name="gender" id="gender_{{ $value }}"
                               value="{{ $value }}" autocomplete="off"
                               {{ old('gender', $user->gender) === $value ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="gender_{{ $value }}">{{ $label }}</label>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                       value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}">
                <div id="ageDisplay" class="text-muted small mt-1"></div>
            </div>

            <div class="mb-4">
                <label for="phone" class="form-label">{{ __('Phone Number') }} <span class="text-muted">({{ __('optional') }})</span></label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="{{ old('phone', $user->phone) }}">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                <a href="{{ route('profile.show', $user->username) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>

</div>

<style>
    .pf-edit-page {
        max-width: 640px;
        margin: 0 auto;
    }

    .pf-card {
        background: var(--pf-bg, #fff);
        border-radius: 10px;
        padding: 20px;
    }

    .pf-card-title {
        font-weight: 700;
        font-size: 1.3rem;
        color: var(--pf-text, #050505);
        margin-bottom: 16px;
    }
</style>

<script>
    (function () {
        const dobInput = document.getElementById('date_of_birth');
        const ageDisplay = document.getElementById('ageDisplay');

        function calculateAge(dobString) {
            const dob = new Date(dobString);
            if (isNaN(dob.getTime())) return null;

            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            return age;
        }

        function updateAgeDisplay() {
            const age = calculateAge(dobInput.value);
            ageDisplay.textContent = (age === null || age < 0 || !dobInput.value)
                ? ''
                : `{{ __('You are') }} ${age} {{ __('years old') }}`;
        }

        dobInput.addEventListener('input', updateAgeDisplay);
        if (dobInput.value) updateAgeDisplay();
    })();
</script>
@endsection