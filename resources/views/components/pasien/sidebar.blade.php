@props(['active' => 'beranda'])

@php
    $linkClass = fn (string $key) => $active === $key
        ? 'group flex h-8 w-full items-center justify-start text-[15px] font-medium text-[#58A7F7] transition-all duration-200 hover:-translate-y-[2px]'
        : 'group flex h-8 w-full items-center justify-start text-[15px] font-normal text-[#111827] transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]';

    $iconClass = fn (string $key) => $active === $key
        ? 'text-[18px] text-[#58A7F7] transition-all duration-200'
        : 'text-[18px] text-[#8A8A8A] transition-all duration-200 group-hover:text-[#58A7F7]';

    $textClass = fn (string $key) => $active === $key
    ? 'text-[#58A7F7] transition-all duration-200'
    : 'text-[#111827] transition-all duration-200 group-hover:text-[#58A7F7]';
@endphp

<aside class="sticky top-0 flex h-screen w-[220px] flex-col border-r border-gray-200 bg-white">
    <div class="flex flex-1 flex-col px-6 py-10">
        <img
            src="{{ asset('images/Medihub.png') }}"
            alt="Logo MediHub"
            class="mb-16 h-auto w-[128px] object-contain"
        >

        <p class="mb-8 font-[Poppins] text-[18px] font-medium text-black">Menu</p>

        <nax`v class="flex w-full flex-col gap-7 pl-4">
            <a href="{{ route('pasien.beranda') }}" class="{{ $linkClass('beranda') }}">
                <div class="grid w-full grid-cols-[28px_1fr] items-center gap-3">
                    <i class="fa-solid fa-house {{ $iconClass('beranda') }} flex h-6 w-6 items-center justify-center"></i>
                    <span class="{{ $textClass('beranda') }}">Beranda</span>
                </div>
            </a>

            <a href="{{ route('pasien.layanan') }}" class="{{ $linkClass('layanan') }}">
                <div class="grid w-full grid-cols-[28px_1fr] items-center gap-3">
                    <i class="fa-solid fa-bed-pulse {{ $iconClass('layanan') }} flex h-6 w-6 items-center justify-center"></i>
                    <span class="{{ $textClass('layanan') }}">Layanan</span>
                </div>
            </a>

            <a href="#" class="{{ $linkClass('riwayat') }}">
                <div class="grid w-full grid-cols-[28px_1fr] items-center gap-3">
                    <i class="fa-regular fa-clock {{ $iconClass('riwayat') }} flex h-6 w-6 items-center justify-center"></i>
                    <span class="{{ $textClass('riwayat') }}">Riwayat</span>
                </div>
            </a>

            <a href="{{ route('pasien.profile') }}" class="{{ $linkClass('profil') }}">
                <div class="grid w-full grid-cols-[28px_1fr] items-center gap-3">
                    <i class="fa-regular fa-user {{ $iconClass('profil') }} flex h-6 w-6 items-center justify-center"></i>
                    <span class="{{ $textClass('profil') }}">Profil</span>
                </div>
            </a>
        </nav>
    </div>

    <div class="border-t border-gray-100 px-6 py-6">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="group flex h-10 w-full items-center gap-3 rounded-lg border border-gray-200 px-4 text-sm text-gray-500 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"
            >
                <i class="fa-solid fa-arrow-right-from-bracket transition-colors duration-200 group-hover:text-[#58A7F7]"></i>
                
                <span class="transition-colors duration-200 group-hover:text-[#58A7F7]">
                    Keluar
                </span>
            </button>
        </form>
    </div>
</aside>