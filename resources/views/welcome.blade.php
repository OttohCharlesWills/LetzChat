<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>LetzChat — Connect. Share. Grow.</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff8f5;
            color: #241512;
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        /* NAVBAR */
        .navbar {
            width: 100%;
            padding: 18px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 248, 245, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #f1ddd5;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #e85d3f;
            letter-spacing: -1px;
        }

        .logo span {
            color: #4b2720;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-block;
            padding: 11px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.25s ease;
        }

        .btn-login {
            color: #4b2720;
            background: transparent;
        }

        .btn-login:hover {
            background: #f5e5df;
        }

        .btn-register {
            background: #e85d3f;
            color: white;
            box-shadow: 0 8px 20px rgba(232, 93, 63, 0.25);
        }

        .btn-register:hover {
            background: #d94c30;
            transform: translateY(-2px);
        }

        /* HERO */
        .hero {
            min-height: 100vh;
            padding: 140px 7% 70px;
            display: flex;
            align-items: center;
            position: relative;
            background:
                radial-gradient(circle at top left, rgba(255, 190, 160, 0.35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(232, 93, 63, 0.15), transparent 30%),
                #fff8f5;
        }

        .hero-content {
            max-width: 1200px;
            width: 100%;
            margin: auto;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 70px;
        }

        .hero-text {
            position: relative;
            z-index: 2;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffe8df;
            color: #c9472d;
            padding: 8px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 22px;
        }

        .hero h1 {
            font-size: clamp(45px, 6vw, 78px);
            line-height: 1.05;
            letter-spacing: -3px;
            margin-bottom: 24px;
            color: #2a1713;
        }

        .hero h1 span {
            color: #e85d3f;
        }

        .hero p {
            max-width: 620px;
            font-size: 18px;
            color: #745c56;
            margin-bottom: 32px;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .hero-btn {
            padding: 15px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
        }

        .hero-btn-primary {
            background: #e85d3f;
            color: #fff;
            box-shadow: 0 12px 25px rgba(232, 93, 63, 0.25);
        }

        .hero-btn-primary:hover {
            background: #d94c30;
        }

        .hero-btn-secondary {
            border: 1px solid #e7cfc7;
            color: #4b2720;
            background: #fff;
        }

        .hero-btn-secondary:hover {
            background: #fff1eb;
        }

        /* HERO CARD */
        .hero-visual {
            position: relative;
            min-height: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-card {
            width: 100%;
            max-width: 410px;
            background: white;
            border-radius: 24px;
            padding: 22px;
            box-shadow: 0 25px 70px rgba(80, 35, 25, 0.16);
            border: 1px solid #f2e1db;
            transform: rotate(2deg);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f2e9e6;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e85d3f, #ffad7d);
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .user-info h4 {
            font-size: 15px;
            color: #2a1713;
        }

        .user-info p {
            font-size: 12px;
            color: #9a8078;
            margin: 0;
        }

        .post-content {
            padding: 20px 0;
        }

        .post-content p {
            color: #513933;
            font-size: 15px;
            margin-bottom: 18px;
        }

        .fake-image {
            height: 190px;
            border-radius: 16px;
            background:
                linear-gradient(135deg, rgba(232, 93, 63, 0.9), rgba(255, 180, 130, 0.8)),
                #e85d3f;
            position: relative;
            overflow: hidden;
        }

        .fake-image::before {
            content: "🌸";
            position: absolute;
            font-size: 100px;
            opacity: 0.35;
            right: 20px;
            bottom: -25px;
        }

        .post-actions {
            display: flex;
            gap: 22px;
            padding-top: 17px;
            color: #8a7068;
            font-size: 14px;
            font-weight: 600;
        }

        .floating-card {
            position: absolute;
            background: white;
            padding: 16px 20px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(80, 35, 25, 0.14);
            border: 1px solid #f2e1db;
        }

        .floating-card.one {
            left: -20px;
            bottom: 55px;
        }

        .floating-card.two {
            right: -25px;
            top: 50px;
        }

        .floating-card strong {
            display: block;
            color: #2a1713;
            font-size: 15px;
        }

        .floating-card span {
            color: #9a8078;
            font-size: 12px;
        }

        /* FEATURES */
        .features {
            padding: 100px 7%;
            background: white;
        }

        .section-header {
            text-align: center;
            max-width: 650px;
            margin: 0 auto 55px;
        }

        .section-header h2 {
            font-size: clamp(32px, 4vw, 48px);
            letter-spacing: -1.5px;
            margin-bottom: 15px;
            color: #2a1713;
        }

        .section-header p {
            color: #806963;
            font-size: 16px;
        }

        .feature-grid {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .feature-card {
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #f0e2dd;
            transition: 0.25s ease;
            background: #fffdfc;
        }

        .feature-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 20px 40px rgba(80, 35, 25, 0.08);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            background: #ffe8df;
            font-size: 25px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #2a1713;
        }

        .feature-card p {
            color: #806963;
            font-size: 14px;
        }

        /* CTA */
        .cta {
            padding: 90px 7%;
            background: #2d1814;
            text-align: center;
        }

        .cta h2 {
            color: white;
            font-size: clamp(35px, 5vw, 58px);
            letter-spacing: -2px;
            margin-bottom: 18px;
        }

        .cta p {
            max-width: 600px;
            margin: 0 auto 30px;
            color: #d8c3bd;
            font-size: 17px;
        }

        .cta .btn-register {
            padding: 15px 28px;
            font-size: 16px;
        }

        /* FOOTER */
        footer {
            padding: 25px 7%;
            background: #21110e;
            color: #b99f98;
            text-align: center;
            font-size: 13px;
        }

        footer strong {
            color: #e85d3f;
        }

        /* MOBILE */
        @media (max-width: 850px) {
            .navbar {
                padding: 16px 5%;
            }

            .hero {
                padding: 130px 5% 70px;
            }

            .hero-content {
                grid-template-columns: 1fr;
                gap: 35px;
            }

            .hero-text {
                text-align: center;
            }

            .hero p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-visual {
                min-height: 420px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .features {
                padding: 75px 5%;
            }
        }

        @media (max-width: 500px) {
            .logo {
                font-size: 24px;
            }

            .btn {
                padding: 9px 13px;
                font-size: 12px;
            }

            .hero h1 {
                letter-spacing: -2px;
            }

            .hero p {
                font-size: 16px;
            }

            .main-card {
                transform: none;
            }

            .floating-card.one {
                left: 0;
                bottom: 15px;
            }

            .floating-card.two {
                right: 0;
                top: 10px;
            }

            .hero-visual {
                min-height: 460px;
            }
        }
    </style>
</head>

<body>

    {{-- NAVIGATION --}}
    <nav class="navbar">

        <a href="{{ url('/') }}" class="logo">
            LetzChat<span>.</span>
        </a>

        @if (Route::has('login'))
            <div class="nav-actions">

                @auth
                    <a href="{{ url('/home') }}" class="btn btn-register">
                        Go to Feed
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-login">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-register">
                            Join LetzChat
                        </a>
                    @endif
                @endauth

            </div>
        @endif

    </nav>


    {{-- HERO --}}
    <section class="hero">

        <div class="hero-content">

            <div class="hero-text">

                <div class="badge">
                    🌸 Your world. Your people.
                </div>

                <h1>
                    Where people <span>connect</span> and moments bloom.
                </h1>

                <p>
                    LetzChat is your space to meet people, share your thoughts,
                    discover communities, and keep up with the moments that
                    actually matter.
                </p>

                <div class="hero-buttons">

                    @guest
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hero-btn hero-btn-primary">
                                Create your account →
                            </a>
                        @endif

                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="hero-btn hero-btn-secondary">
                                I already have an account
                            </a>
                        @endif
                    @else
                        <a href="{{ url('/home') }}" class="hero-btn hero-btn-primary">
                            Go to your feed →
                        </a>
                    @endguest

                </div>

            </div>


            {{-- FAKE SOCIAL POST VISUAL --}}
            <div class="hero-visual">

                <div class="main-card">

                    <div class="card-header">

                        <div class="avatar">W</div>

                        <div class="user-info">
                            <h4>Welcome to LetzChat</h4>
                            <p>Just now · 🌍</p>
                        </div>

                    </div>

                    <div class="post-content">

                        <p>
                            New people. New conversations.
                            New memories waiting to happen. 🌸
                        </p>

                        <div class="fake-image"></div>

                    </div>

                    <div class="post-actions">
                        <span>❤️ 2.4k</span>
                        <span>💬 328</span>
                        <span>↗ Share</span>
                    </div>

                </div>


                <div class="floating-card one">
                    <strong>👥 Find your people</strong>
                    <span>Build real connections.</span>
                </div>

                <div class="floating-card two">
                    <strong>🌸 Join communities</strong>
                    <span>Discover what interests you.</span>
                </div>

            </div>

        </div>

    </section>


    {{-- FEATURES --}}
    <section class="features">

        <div class="section-header">
            <h2>More than just another feed.</h2>

            <p>
                LetzChat gives you a place to connect, share, and discover
                without making everything feel like a chaotic digital marketplace.
            </p>
        </div>


        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">👋</div>

                <h3>Connect</h3>

                <p>
                    Follow people you know, discover new ones, and build
                    connections around real conversations.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">💬</div>

                <h3>Share freely</h3>

                <p>
                    Post your thoughts, updates, photos, and moments with
                    the people who actually want to see them.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">🌍</div>

                <h3>Discover communities</h3>

                <p>
                    Join groups, explore interests, and find communities
                    where your weirdly specific interests finally make sense.
                </p>

            </div>

        </div>

    </section>


    {{-- CTA --}}
    <section class="cta">

        <h2>Your next connection could start here.</h2>

        <p>
            Don't just scroll through the internet like it's a waiting room.
            Join people, conversations, groups, and moments worth sticking around for.
        </p>

        @guest
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-register">
                    Join LetzChat today →
                </a>
            @endif
        @else
            <a href="{{ url('/home') }}" class="btn btn-register">
                Go to your feed →
            </a>
        @endguest

    </section>


    {{-- FOOTER --}}
    <footer>
        © {{ date('Y') }} <strong>LetzChat</strong>. Connect. Share. Grow. 🌸
    </footer>

</body>
</html>