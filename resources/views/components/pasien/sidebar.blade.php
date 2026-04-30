{{--
    Sidebar reusable untuk halaman pasien.

    Pakai prop `:active` untuk menandai menu aktif.
    Nilai valid: beranda | layanan | riwayat | profil

    Contoh:
        <x-pasien.sidebar :active="'beranda'" />

    NOTE: View existing di pasien/* belum dimigrate ke component ini
    karena tim sedang aktif edit. Ke depan, ganti `<aside>` di view
    pasien dengan tag ini supaya tidak duplikasi sidebar 4 kali.
--}}
@props(['active' => 'beranda'])

@php
    $linkClass = fn (string $key) => $active === $key
        ? 'flex items-center gap-3 font-medium text-blue-500'
        : 'flex items-center gap-3 text-gray-500';
@endphp

<aside class="flex flex-col justify-between border-r border-gray-200 px-7 py-8">
    <div>
        <img
            src="{{ asset('images/Medihub.png') }}"
            alt="Logo MediHub"
            class="mb-10 h-14 w-auto object-contain"
        >

        <p class="mb-6 text-lg font-semibold">Menu</p>

        <nav class="flex flex-col gap-7 text-[15px]">
            <a href="{{ route('pasien.beranda') }}" class="{{ $linkClass('beranda') }}">
                <i class="fa-solid fa-house"></i> Beranda
            </a>
            <a href="{{ route('pasien.layanan') }}" class="{{ $linkClass('layanan') }}">
                <i class="fa-solid fa-bed-pulse"></i> Layanan
            </a>
            <a href="#" class="{{ $linkClass('riwayat') }}">
                <i class="fa-regular fa-clock"></i> Riwayat
            </a>
            <a href="{{ route('pasien.profile') }}" class="{{ $linkClass('profil') }}">
                <i class="fa-regular fa-user"></i> Profil
            </a>
        </nav>
    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left text-sm text-gray-500">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>
            Keluar
        </button>
    </form>
</aside>
