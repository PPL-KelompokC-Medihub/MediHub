@props(['active' => 'beranda'])

@php
    $linkClass = fn (string $key) => $active === $key
        ? 'group flex h-8 items-center text-[15px] font-medium text-[#58A7F7] transition-all duration-200 hover:-translate-y-[2px]'
        : 'group flex h-8 items-center text-[15px] font-normal text-[#111827] transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]';

    $iconClass = fn (string $key) => $active === $key
        ? 'w-[26px] min-w-[26px] text-left text-[18px] text-[#58A7F7] transition-all duration-200'
        : 'w-[26px] min-w-[26px] text-left text-[18px] text-[#8A8A8A] transition-all duration-200 group-hover:text-[#58A7F7]';

    $textClass = fn (string $key) => $active === $key
    ? 'text-[#58A7F7] transition-all duration-200'
    : 'text-[#111827] transition-all duration-200 group-hover:text-[#58A7F7]';
@endphp

<aside class="sticky left-0 top-0 flex h-screen w-full flex-col border-r border-gray-200 bg-white">
    <div class="flex flex-1 flex-col px-6 py-10">
        <img
            src="{{ asset('images/Medihub.png') }}"
            alt="Logo MediHub"
            class="mb-16 h-auto w-[128px] object-contain"
        >

        <p class="mb-8 font-[Poppins] text-[18px] font-medium text-black">Menu</p>

        <nav class="flex flex-col gap-7">
            <a href="{{ route('pasien.beranda') }}" class="{{ $linkClass('beranda') }}">
                <div class="grid w-[150px] grid-cols-[24px_1fr] items-center gap-5">
                    <i class="fa-solid fa-house {{ $iconClass('beranda') }}"></i>
                    <span class="{{ $active === 'beranda' ? 'text-[#58A7F7]' : 'text-[#111827] group-hover:text-[#58A7F7]' }} transition-all duration-200">Beranda</span>
                </div>
            </a>

            <a href="{{ route('pasien.layanan') }}" class="{{ $linkClass('layanan') }}">
                <div class="grid w-[150px] grid-cols-[24px_1fr] items-center gap-5">
                    <i class="fa-solid fa-bed-pulse {{ $iconClass('layanan') }}"></i>
                    <span class="{{ $active === 'layanan' ? 'text-[#58A7F7]' : 'text-[#111827] group-hover:text-[#58A7F7]' }} transition-all duration-200">Layanan</span>
                </div>
            </a>

            <a href="#" class="{{ $linkClass('riwayat') }}">
                <div class="grid w-[150px] grid-cols-[24px_1fr] items-center gap-5">
                    <i class="fa-regular fa-clock {{ $iconClass('riwayat') }}"></i>
                    <span class="{{ $active === 'riwayat' ? 'text-[#58A7F7]' : 'text-[#111827] group-hover:text-[#58A7F7]' }} transition-all duration-200">Riwayat</span>
                </div>
            </a>

            <a href="{{ route('pasien.profile') }}" class="{{ $linkClass('profil') }}">
                <div class="grid w-[150px] grid-cols-[24px_1fr] items-center gap-5">
                    <i class="fa-regular fa-user {{ $iconClass('profil') }}"></i>
                    <span class="{{ $active === 'profil' ? 'text-[#58A7F7]' : 'text-[#111827] group-hover:text-[#58A7F7]' }} transition-all duration-200">Profil</span>
                </div>
            </a>
        </nav>
    </div>

    <form action="{{ route('logout') }}" method="POST" class="mt-auto px-6 pb-8 pt-4">
        @csrf
        <button class="group flex w-full items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-left text-[14px] font-normal text-[#8A8A8A] transition-all duration-200 hover:-translate-y-[2px] hover:bg-gray-50 hover:text-[#58A7F7]">
            <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center transition-all duration-200 group-hover:text-[#58A7F7]"></i>
            <span>Keluar</span>
        </button>
    </form>
</aside>