{{--
    Layout dasar untuk halaman dokter (authenticated).

    Slot yang tersedia:
      - @yield('title')      → judul tab browser
      - @yield('content')    → main content (tengah)
      - @yield('rightbar')   → sidebar kanan (opsional)

    Prop:
      - $active : tandai menu sidebar yang aktif (lihat
        components/dokter/sidebar.blade.php)

    Contoh pemakaian:

        @extends('layouts.dokter', ['active' => 'jadwal'])

        @section('title', 'Jadwal Temu - MediHub')

        @section('content')
            <h1>Jadwal Saya</h1>
            ...
        @endsection

        @section('rightbar')
            ...
        @endsection
--}}
@php
    $active = $active ?? 'dashboard';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MediHub')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
<div class="mediq-app-shell">
    <div class="mediq-shell-grid">
        <x-dokter.sidebar :active="$active" />

        <main class="mediq-main">
            @yield('content')
        </main>

        @hasSection('rightbar')
            <aside class="mediq-rightbar">
                @yield('rightbar')
            </aside>
        @endif
    </div>
</div>

@stack('scripts')
</body>
</html>
