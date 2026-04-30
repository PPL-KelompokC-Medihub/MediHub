{{--
    Sidebar reusable untuk halaman dokter.

    Pakai prop `:active` untuk menandai menu yang sedang aktif.
    Nilai yang valid: dashboard | jadwal | riwayat | profil

    Contoh:
        <x-dokter.sidebar :active="'dashboard'" />
--}}
@props(['active' => 'dashboard'])

@php
    $isActive = fn (string $key) => $active === $key ? 'is-active' : '';
@endphp

<aside class="mediq-sidebar">
    <a href="{{ route('dokter.dashboard') }}" class="mediq-logo">
        <img src="{{ asset('images/medihub-logo.png') }}" alt="MediHub" />
    </a>

    <nav class="mediq-nav">
        <p class="mediq-nav-title">Menu</p>

        <a href="{{ route('dokter.dashboard') }}" class="mediq-nav-item {{ $isActive('dashboard') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('dokter.jadwal.index') }}" class="mediq-nav-item {{ $isActive('jadwal') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            Jadwal Temu
        </a>

        <a href="#" class="mediq-nav-item {{ $isActive('riwayat') }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Riwayat
        </a>

        <a href="{{ route('dokter.profile') }}" class="mediq-nav-item {{ $isActive('profil') }}">
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
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:10px">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </form>
    </div>
</aside>
