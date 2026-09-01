<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="/app.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --navbar-height: 56px;
        }

        html, body {
            background: #f0f2f5;
        }

        /* Keep the navbar pinned; adjust selector to whatever wraps your navbar */
        #app > nav {
            position: sticky;
            top: 0;
            z-index: 1030;
            height: var(--navbar-height);
        }

        .app-main {
            padding-top: 1rem;
        }

        .sidebar-col {
            position: sticky;
            top: calc(var(--navbar-height) + 1rem);
            height: calc(100vh - var(--navbar-height) - 2rem);
            overflow-y: auto;
        }

        /* Hide scrollbar but keep scroll functionality (optional, Facebook-style) */
        .sidebar-col::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-col::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.15);
            border-radius: 3px;
        }

        .content-col {
            min-height: calc(100vh - var(--navbar-height) - 2rem);
        }

        /* Below lg breakpoint: stack everything, drop the sticky behavior */
        @media (max-width: 991.98px) {
            .sidebar-col {
                position: static;
                height: auto;
                overflow-y: visible;
            }
        }
    </style>
</head>
<body>
    <div id="app">

        @include('includes.usernavbar')

        <main class="app-main">
            <div class="container-fluid">
                <div class="row">

                    <aside class="col-lg-3 d-none d-lg-block sidebar-col">
                        @include('includes.adssidebar')
                    </aside>

                    <section class="col-12 col-lg-9 content-col">
                        @yield('content')
                    </section>

                </div>
            </div>
        </main>
    </div>
    
</body>
</html>