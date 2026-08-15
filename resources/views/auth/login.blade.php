<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - LetzChat</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', Arial, sans-serif;
            min-height: 100vh;
            background: #fff8f5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #2a1713;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand a {
            text-decoration: none;
            font-size: 38px;
            font-weight: 800;
            color: #e85d3f;
            letter-spacing: -1.5px;
        }

        .brand span {
            color: #2a1713;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 24px;
        }

        .welcome-text h1 {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 7px;
        }

        .welcome-text p {
            color: #806963;
            font-size: 15px;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #f1ddd5;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(80, 35, 25, 0.10);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #4b2720;
        }

        .form-control {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #e7cfc7;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            font-family: inherit;
            transition: 0.2s ease;
            color: #2a1713;
            background: #fff;
        }

        .form-control:focus {
            border-color: #e85d3f;
            box-shadow: 0 0 0 4px rgba(232, 93, 63, 0.14);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 6px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 5px 0 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: #e85d3f;
            cursor: pointer;
        }

        .remember-me label {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .forgot-password {
            color: #e85d3f;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .forgot-password:hover {
            color: #d94c30;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            border: none;
            background: #e85d3f;
            color: white;
            padding: 14px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s ease;
            box-shadow: 0 10px 25px rgba(232, 93, 63, 0.22);
        }

        .login-btn:hover {
            background: #d94c30;
            transform: translateY(-2px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .divider {
            height: 1px;
            background: #f1e3de;
            margin: 25px 0;
        }

        .register-text {
            text-align: center;
            font-size: 14px;
            color: #806963;
        }

        .register-text a {
            color: #e85d3f;
            font-weight: 800;
            text-decoration: none;
        }

        .register-text a:hover {
            color: #d94c30;
            text-decoration: underline;
        }

        .welcome-icon {
            color: #e85d3f;
            font-size: 24px;
            vertical-align: middle;
            margin-left: 5px;
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .login-card {
                padding: 25px 20px;
                border-radius: 16px;
            }

            .brand a {
                font-size: 34px;
            }

            .welcome-text h1 {
                font-size: 26px;
            }

            .remember-row {
                align-items: flex-start;
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <div class="brand">
            <a href="{{ url('/') }}">
                LetzChat<span>.</span>
            </a>
        </div>

        <div class="welcome-text">
            <h1>
                Welcome back
                <i class="bi bi-hand-index-thumb-fill welcome-icon"></i>
            </h1>
            <p>Log in and catch up with your world.</p>
        </div>

        <div class="login-card">

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>

                    <input
                        id="email"
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        autofocus
                        placeholder="Enter your email"
                    >

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        id="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <div class="remember-row">

                    <div class="remember-me">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >

                        <label for="remember">
                            Remember me
                        </label>
                    </div>


                    @if (Route::has('password.request'))
                        <a
                            class="forgot-password"
                            href="{{ route('password.request') }}"
                        >
                            Forgot password?
                        </a>
                    @endif

                </div>


                <button type="submit" class="login-btn">
                    Log in
                </button>

            </form>

            <div class="divider"></div>

            @if (Route::has('register'))
                <div class="register-text">
                    Don't have an account?
                    <a href="{{ route('register') }}">
                        Join LetzChat
                    </a>
                </div>
            @endif

        </div>

    </div>

</body>
</html>