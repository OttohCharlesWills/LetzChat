<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LetzChat') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        .register-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .register-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .register-card .card-header {
            background: #fff;
            border-bottom: none;
            padding: 28px 32px 0 32px;
        }

        .register-card .card-body {
            padding: 8px 32px 32px 32px;
        }

        .brand-title {
            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 4px;
        }

        .brand-sub {
            color: #6c757d;
            margin-bottom: 24px;
        }

        /* Progress steps */
        .step-progress {
            display: flex;
            align-items: center;
            margin-bottom: 32px;
        }

        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .step-dot.active {
            background: #0d6efd;
            color: #fff;
        }

        .step-dot.done {
            background: #198754;
            color: #fff;
        }

        .step-line {
            flex: 1;
            height: 3px;
            background: #e9ecef;
            margin: 0 6px;
            border-radius: 2px;
            transition: background 0.25s ease;
        }

        .step-line.done {
            background: #198754;
        }

        .step-label {
            font-size: 0.75rem;
            text-align: center;
            margin-top: 6px;
            color: #6c757d;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 32px;
        }

        /* Panels */
        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-title {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 2px;
        }

        .step-desc {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 28px;
        }

        .form-control, .form-select {
            padding: 10px 14px;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            background: #e9ecef;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.25s ease, background 0.25s ease;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        /* Username availability */
        #usernameSpinner {
            display: none;
            background: #fff;
        }

        #usernameStatus {
            min-height: 18px;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        #usernameStatus.text-success { color: #198754 !important; }
        #usernameStatus.text-danger { color: #dc3545 !important; }
        #usernameStatus.text-muted { color: #6c757d !important; }

        #ageDisplay {
            font-size: 0.85rem;
            margin-top: 4px;
            min-height: 18px;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="container register-wrap">
            <div class="row justify-content-center w-100">
                <div class="col-md-8 col-lg-6">

                    <div class="card register-card">
                        <div class="card-header">
                            <div class="brand-title">{{ __('Create your account') }}</div>
                            {{-- <div class="brand-sub">{{ __('It only takes a minute.') }}</div> --}}

                            <!-- Step progress indicator -->
                            <div class="step-progress" id="stepProgress">
                                <div class="step-item">
                                    <div class="step-dot active" data-step-dot="1">1</div>
                                    <div class="step-label">{{ __('Account') }}</div>
                                </div>
                                <div class="step-line" data-step-line="1"></div>
                                <div class="step-item">
                                    <div class="step-dot" data-step-dot="2">2</div>
                                    <div class="step-label">{{ __('Name') }}</div>
                                </div>
                                <div class="step-line" data-step-line="2"></div>
                                <div class="step-item">
                                    <div class="step-dot" data-step-dot="3">3</div>
                                    <div class="step-label">{{ __('About you') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                                @csrf

                                {{-- ================= STEP 1 : EMAIL + PASSWORD ================= --}}
                                <div class="step-panel active" data-step="1">
                                    <div class="step-title">{{ __('Sign-in details') }}</div>
                                    <div class="step-desc">{{ __('You\'ll use this to log in.') }}</div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                        <input id="email" type="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               name="email" value="{{ old('email') }}"
                                               required autocomplete="email" autofocus>
                                        <div class="invalid-feedback" data-error-for="email"></div>
                                        @error('email')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">{{ __('Password') }}</label>
                                        <input id="password" type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               name="password" required autocomplete="new-password"
                                               minlength="8">
                                        <div class="password-strength">
                                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                        </div>
                                        <div class="invalid-feedback" data-error-for="password"></div>
                                        @error('password')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                                        <input id="password-confirm" type="password" class="form-control"
                                               name="password_confirmation" required autocomplete="new-password">
                                        <div class="invalid-feedback" data-error-for="password-confirm"></div>
                                    </div>
                                </div>

                                {{-- ================= STEP 2 : NAME + USERNAME ================= --}}
                                <div class="step-panel" data-step="2">
                                    <div class="step-title">{{ __('What\'s your name?') }}</div>
                                    <div class="step-desc">{{ __('This is how you\'ll appear to others.') }}</div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                                            <input id="first_name" type="text"
                                                   class="form-control @error('first_name') is-invalid @enderror"
                                                   name="first_name" value="{{ old('first_name') }}"
                                                   required autocomplete="given-name">
                                            <div class="invalid-feedback" data-error-for="first_name"></div>
                                            @error('first_name')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                                            <input id="last_name" type="text"
                                                   class="form-control @error('last_name') is-invalid @enderror"
                                                   name="last_name" value="{{ old('last_name') }}"
                                                   required autocomplete="family-name">
                                            <div class="invalid-feedback" data-error-for="last_name"></div>
                                            @error('last_name')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="username" class="form-label">{{ __('Username') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">@</span>
                                            <input id="username" type="text"
                                                   class="form-control @error('username') is-invalid @enderror"
                                                   name="username" value="{{ old('username') }}"
                                                   required autocomplete="username" pattern="[A-Za-z0-9_\.]+"
                                                   autocomplete="off">
                                            <span class="input-group-text" id="usernameSpinner">
                                                <span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span>
                                            </span>
                                        </div>
                                        <div class="form-text">{{ __('Letters, numbers, dots and underscores only. We\'ll suggest one based on your name, or you can pick your own.') }}</div>
                                        <div id="usernameStatus" class="text-muted"></div>
                                        <div class="invalid-feedback d-block" data-error-for="username"></div>
                                        @error('username')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ================= STEP 3 : ABOUT YOU ================= --}}
                                <div class="step-panel" data-step="3">
                                    <div class="step-title">{{ __('A bit about you') }}</div>
                                    <div class="step-desc">{{ __('Helps us personalize your experience.') }}</div>

                                    <div class="mb-3">
                                        <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                                        <input id="date_of_birth" type="date"
                                               class="form-control @error('date_of_birth') is-invalid @enderror"
                                               name="date_of_birth" value="{{ old('date_of_birth') }}"
                                               required autocomplete="bday">
                                        <div id="ageDisplay" class="text-muted"></div>
                                        <div class="invalid-feedback" data-error-for="date_of_birth"></div>
                                        @error('date_of_birth')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label d-block">{{ __('Gender') }}</label>
                                        <div class="btn-group w-100" role="group" aria-label="Gender">
                                            @foreach (['male' => __('Male'), 'female' => __('Female'), 'other' => __('Other')] as $value => $label)
                                                <input type="radio" class="btn-check" name="gender"
                                                       id="gender_{{ $value }}" value="{{ $value }}"
                                                       autocomplete="off"
                                                       {{ old('gender') === $value ? 'checked' : '' }} required>
                                                <label class="btn btn-outline-primary" for="gender_{{ $value }}">{{ $label }}</label>
                                            @endforeach
                                        </div>
                                        <div class="invalid-feedback d-block" data-error-for="gender"></div>
                                        @error('gender')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">{{ __('Phone Number') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                        <input id="phone" type="tel"
                                               class="form-control @error('phone') is-invalid @enderror"
                                               name="phone" value="{{ old('phone') }}" autocomplete="tel">
                                        @error('phone')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ================= NAV BUTTONS ================= --}}
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="visibility: hidden;">
                                        {{ __('Back') }}
                                    </button>

                                    <button type="button" class="btn btn-primary" id="nextBtn">
                                        {{ __('Continue') }}
                                    </button>

                                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                        {{ __('Create Account') }}
                                    </button>
                                </div>
                            </form>

                            <div class="login-link">
                                {{ __('Already have an account?') }}
                                <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const totalSteps = 3;
            let currentStep = 1;

            const panels = document.querySelectorAll('.step-panel');
            const dots = document.querySelectorAll('[data-step-dot]');
            const lines = document.querySelectorAll('[data-step-line]');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('registerForm');

            // Per-step required fields to validate before advancing
            const stepFields = {
                1: ['email', 'password', 'password-confirm'],
                2: ['first_name', 'last_name', 'username'],
                3: ['date_of_birth', 'gender'],
            };

            function clearErrors(step) {
                stepFields[step].forEach(id => {
                    const el = document.getElementById(id);
                    const err = document.querySelector(`[data-error-for="${id}"]`);
                    if (el) el.classList.remove('is-invalid');
                    if (err) err.textContent = '';
                });
            }

            function showError(id, message) {
                const el = document.getElementById(id);
                const err = document.querySelector(`[data-error-for="${id}"]`);
                if (el) el.classList.add('is-invalid');
                if (err) err.textContent = message;
            }

            function validateStep(step) {
                clearErrors(step);
                let valid = true;

                if (step === 1) {
                    const email = document.getElementById('email');
                    const password = document.getElementById('password');
                    const confirm = document.getElementById('password-confirm');

                    if (!email.value || !email.checkValidity()) {
                        showError('email', "{{ __('Please enter a valid email address.') }}");
                        valid = false;
                    }
                    if (!password.value || password.value.length < 8) {
                        showError('password', "{{ __('Password must be at least 8 characters.') }}");
                        valid = false;
                    }
                    if (confirm.value !== password.value || !confirm.value) {
                        showError('password-confirm', "{{ __('Passwords do not match.') }}");
                        valid = false;
                    }
                }

                if (step === 2) {
                    const firstName = document.getElementById('first_name');
                    const lastName = document.getElementById('last_name');
                    const username = document.getElementById('username');

                    if (!firstName.value.trim()) {
                        showError('first_name', "{{ __('First name is required.') }}");
                        valid = false;
                    }
                    if (!lastName.value.trim()) {
                        showError('last_name', "{{ __('Last name is required.') }}");
                        valid = false;
                    }
                    if (!username.value.trim() || !/^[A-Za-z0-9_.]+$/.test(username.value.trim())) {
                        showError('username', "{{ __('Enter a valid username (letters, numbers, dots, underscores).') }}");
                        valid = false;
                    } else if (usernameIsTaken === true) {
                        showError('username', "{{ __('This username is already taken.') }}");
                        valid = false;
                    }
                }

                if (step === 3) {
                    const dob = document.getElementById('date_of_birth');
                    const genderChecked = document.querySelector('input[name="gender"]:checked');

                    if (!dob.value) {
                        showError('date_of_birth', "{{ __('Date of birth is required.') }}");
                        valid = false;
                    }
                    if (!genderChecked) {
                        showError('gender', "{{ __('Please select a gender.') }}");
                        valid = false;
                    }
                }

                return valid;
            }

            function goToStep(step) {
                panels.forEach(p => p.classList.toggle('active', Number(p.dataset.step) === step));

                dots.forEach(dot => {
                    const n = Number(dot.dataset.stepDot);
                    dot.classList.toggle('active', n === step);
                    dot.classList.toggle('done', n < step);
                    dot.textContent = n < step ? '✓' : n;
                });

                lines.forEach(line => {
                    const n = Number(line.dataset.stepLine);
                    line.classList.toggle('done', n < step);
                });

                prevBtn.style.visibility = step === 1 ? 'hidden' : 'visible';
                nextBtn.style.display = step === totalSteps ? 'none' : 'inline-block';
                submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';

                currentStep = step;
            }

            nextBtn.addEventListener('click', function () {
                if (validateStep(currentStep)) {
                    goToStep(currentStep + 1);
                }
            });

            prevBtn.addEventListener('click', function () {
                goToStep(currentStep - 1);
            });

            form.addEventListener('submit', function (e) {
                // Final safety check across all steps before allowing submit
                for (let s = 1; s <= totalSteps; s++) {
                    if (!validateStep(s)) {
                        e.preventDefault();
                        goToStep(s);
                        return;
                    }
                }
            });

            // Simple password strength meter
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('passwordStrengthBar');

            passwordInput.addEventListener('input', function () {
                const val = passwordInput.value;
                let score = 0;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

                const colors = ['#dc3545', '#fd7e14', '#ffc107', '#198754'];
                const widths = ['25%', '50%', '75%', '100%'];
                const idx = Math.max(score - 1, 0);

                strengthBar.style.width = val.length ? widths[idx] : '0%';
                strengthBar.style.background = colors[idx];
            });

            // ========================================================
            // USERNAME: auto-suggestion + live availability checking
            // ========================================================
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const usernameInput = document.getElementById('username');
            const usernameSpinner = document.getElementById('usernameSpinner');
            const usernameStatus = document.getElementById('usernameStatus');

            let usernameManuallyEdited = usernameInput.value.trim().length > 0; // if old('username') pre-filled
            let usernameCheckToken = 0;
            let usernameIsTaken = null; // null = unknown, true/false once checked

            function debounce(fn, delay) {
                let t;
                return function (...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            function slugifyUsername(str) {
                return str
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // strip accents
                    .replace(/[^a-z0-9]+/g, '')
                    .slice(0, 20);
            }

            function setSpinner(show) {
                usernameSpinner.style.display = show ? 'flex' : 'none';
            }

            function setStatus(message, type) {
                usernameStatus.textContent = message;
                usernameStatus.classList.remove('text-success', 'text-danger', 'text-muted');
                usernameStatus.classList.add(
                    type === 'ok' ? 'text-success' : type === 'taken' ? 'text-danger' : 'text-muted'
                );
            }

            // Hits the backend availability endpoint. Expects JSON: { available: true|false }
            async function fetchAvailability(username) {
                const res = await fetch(
                    `{{ url('/check-username') }}?username=${encodeURIComponent(username)}`,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }
                );
                if (!res.ok) throw new Error('Request failed');
                return res.json();
            }

            // Manual typing flow: check exactly what the user typed
            const debouncedManualCheck = debounce(async function () {
                const val = usernameInput.value.trim();
                if (!val || !/^[A-Za-z0-9_.]+$/.test(val)) {
                    setSpinner(false);
                    setStatus('', '');
                    usernameIsTaken = null;
                    return;
                }

                const token = ++usernameCheckToken;
                setSpinner(true);
                setStatus("{{ __('Checking availability...') }}", '');

                try {
                    const data = await fetchAvailability(val);
                    if (token !== usernameCheckToken) return; // a newer check superseded this one

                    setSpinner(false);
                    if (data.available) {
                        usernameIsTaken = false;
                        setStatus("{{ __('Username is available') }}", 'ok');
                        usernameInput.classList.remove('is-invalid');
                    } else {
                        usernameIsTaken = true;
                        setStatus("{{ __('Username is already taken') }}", 'taken');
                    }
                } catch (e) {
                    if (token === usernameCheckToken) {
                        setSpinner(false);
                        setStatus('', '');
                        usernameIsTaken = null;
                    }
                }
            }, 450);

            usernameInput.addEventListener('input', function () {
                usernameManuallyEdited = true;
                usernameIsTaken = null;
                debouncedManualCheck();
            });

            // Auto-suggestion flow: builds a username from first + last name,
            // and if it's taken, appends numbers (2, 3, 4...) until one is free.
            const debouncedAutoSuggest = debounce(async function () {
                if (usernameManuallyEdited) return;

                const base = slugifyUsername(firstNameInput.value.trim() + lastNameInput.value.trim());
                if (!base) {
                    setSpinner(false);
                    setStatus('', '');
                    return;
                }

                const token = ++usernameCheckToken;
                setSpinner(true);
                setStatus("{{ __('Checking availability...') }}", '');

                let candidate = base;
                let suffix = 1;
                let available = false;

                try {
                    while (suffix <= 50) {
                        const data = await fetchAvailability(candidate);

                        // Bail out if the user started typing manually mid-check,
                        // or a newer suggestion request has taken over.
                        if (usernameManuallyEdited || token !== usernameCheckToken) return;

                        if (data.available) {
                            available = true;
                            break;
                        }
                        suffix++;
                        candidate = base + suffix;
                    }

                    if (usernameManuallyEdited || token !== usernameCheckToken) return;

                    setSpinner(false);
                    usernameInput.value = candidate;
                    if (available) {
                        usernameIsTaken = false;
                        setStatus("{{ __('Username is available') }}", 'ok');
                    } else {
                        usernameIsTaken = null;
                        setStatus('', '');
                    }
                } catch (e) {
                    if (token === usernameCheckToken) {
                        setSpinner(false);
                        setStatus('', '');
                    }
                }
            }, 500);

            firstNameInput.addEventListener('input', debouncedAutoSuggest);
            lastNameInput.addEventListener('input', debouncedAutoSuggest);

            // ========================================================
            // DATE OF BIRTH: live age calculation
            // ========================================================
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
                if (age === null || age < 0) {
                    ageDisplay.textContent = '';
                    return;
                }
                ageDisplay.textContent = age === 1
                    ? "{{ __('You are 1 year old') }}"
                    : `{{ __('You are') }} ${age} {{ __('years old') }}`;
            }

            dobInput.addEventListener('input', updateAgeDisplay);
            dobInput.addEventListener('change', updateAgeDisplay);
            if (dobInput.value) updateAgeDisplay();

            // If the page reloads with server-side validation errors,
            // jump straight to the first step that has an error.
            @if ($errors->any())
                (function () {
                    const errorFields = @json($errors->keys());
                    for (const [step, fields] of Object.entries(stepFields)) {
                        if (fields.some(f => errorFields.includes(f))) {
                            goToStep(Number(step));
                            break;
                        }
                    }
                })();
            @endif
        })();
    </script>
</body>
</html>