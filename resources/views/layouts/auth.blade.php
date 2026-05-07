<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MediHub - Auth')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased @yield('auth_page_class')">
    <div class="mediq-auth-shell">
        <div class="mediq-auth-grid">
            <!-- Left visual panel -->
            <div class="mediq-auth-visual">
                <div class="mediq-auth-lines"></div>
                <img class="mediq-auth-image" src="@yield('auth_visual_image', asset('images/dokter-auth-illustration.png'))" alt="MediHub" />
            </div>

            <!-- Right form panel -->
            <div class="mediq-auth-panel">
                <div class="mediq-auth-card">
                    <a href="{{ route('welcome') }}" class="mediq-logo">
                        <img src="{{ asset('images/medihub-logo.png') }}" alt="MediHub" />
                    </a>

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @yield('page-scripts')
</body>
</html>
