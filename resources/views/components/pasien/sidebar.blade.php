@props(['active' => 'beranda'])

@php
    $isActive = fn (string $key) => $active === $key ? 'is-active' : '';
@endphp

<aside class="mediq-sidebar">
    <a href="{{ route('pasien.beranda') }}" class="mediq-logo">
        <img src="{{ asset('images/Medihub.png') }}" alt="MediHub" />
    </a>

    <nav class="mediq-nav">
        <p class="mediq-nav-title">Menu</p>

        <a href="{{ route('pasien.beranda') }}" class="mediq-nav-item {{ $isActive('beranda') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9.75L12 3l9 6.75V21a1 1 0 01-1 1H4a1 1 0 01-1-1V9.75z"/>
                <path d="M9 22V12h6v10"/>
            </svg>
            Beranda
        </a>

        <a href="{{ route('pasien.layanan') }}" class="mediq-nav-item {{ $isActive('layanan') }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 8.5V19.5"/>
                <path d="M21 12.5V19.5"/>
                <path d="M13 7.5H19"/>
                <path d="M5.5 11H8.5"/>
                <path d="M3 14H21"/>
                <path d="M3 17H21"/>
                <path d="M16 4.5V10.5"/>
            </svg>
            Layanan
        </a>

        <a href="#" class="mediq-nav-item {{ $isActive('riwayat') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Riwayat
        </a>

        <a href="{{ route('pasien.profile') }}" class="mediq-nav-item {{ $isActive('profil') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Profil
        </a>
    </nav>

    <div class="mediq-logout-wrap">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mediq-logout">
                <svg class="mediq-sidebar-icon-spaced" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </form>
    </div>
</aside>