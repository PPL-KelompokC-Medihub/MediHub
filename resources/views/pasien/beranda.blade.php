<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Pasien - MediHub</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <div class="grid min-h-screen grid-cols-[220px_1fr_340px] overflow-hidden">
        <aside class="flex flex-col justify-between border-r border-gray-200 px-7 py-8">
            <div>
                <div class="mb-10 text-2xl font-bold text-blue-400">
                    MediHub<i class="fa-solid fa-magnifying-glass-plus ml-1 text-blue-500"></i>
                </div>

                <p class="mb-6 text-lg font-semibold">Menu</p>

                <nav class="flex flex-col gap-7 text-[15px]">
                    <a href="#" class="flex items-center gap-3 font-medium text-blue-500">
                        <i class="fa-solid fa-house"></i>
                        Beranda
                    </a>

                    <a href="#" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-solid fa-bed-pulse"></i>
                        Layanan
                    </a>

                    <a href="#" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-regular fa-clock"></i>
                        Riwayat
                    </a>

                    <a href="{{ route('pasien.profile') }}" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-regular fa-user"></i>
                        Profil
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

        <main class="overflow-hidden bg-[#fbfbfb] px-6 py-8">
            <header class="mb-6 flex items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <img 
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=300&auto=format&fit=crop"
                        class="h-14 w-14 rounded-full object-cover"
                        alt="Avatar"
                    >

                    <div>
                        <h1 class="text-lg font-semibold">
                            Halo, {{ auth()->user()->name ?? auth()->user()->fullname ?? 'Pasien' }} 👋
                        </h1>
                        <p class="text-sm text-gray-500">Bagaimana kabarmu?</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex w-[330px] items-center rounded-xl border border-gray-200 bg-white px-4 py-3">
                        <input 
                            type="text" 
                            placeholder="Dokter mata terdekat"
                            class="w-full bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>

                    <button class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500">
                        <i class="fa-regular fa-bell"></i>
                    </button>
                </div>
            </header>

            <section class="relative mb-6 h-[230px] overflow-hidden rounded-xl bg-gradient-to-r from-[#58A7F7] to-[#96CAFF] px-9 text-white shadow-sm">
                <img 
                    src="{{ asset('images/lamp.png') }}"
                    alt="Lampu"
                    class="absolute left-1 -top-8 z-50 h-[250px] w-auto rotate-[-8deg] object-contain"
                >
                <div
                    class="pointer-events-none absolute left-[42px] top-[18px] z-10 h-[250px] w-[560px] rotate-[7deg] opacity-70 blur-[15px]"
                    style="background: radial-gradient(ellipse at left, rgba(255,255,255,0.65) 0%, rgba(255,255,255,0.28) 35%, rgba(255,255,255,0.08) 62%, transparent 78%);">
                </div>

                <div class="relative z-10 grid h-full grid-cols-[1fr_240px_1fr] items-center">
                    <div class="ml-35">
                        <h2 class="mb-3 text-[24px] font-bold leading-none">PROMO SPESIAL</h2>
                        <a href="#" class="inline-block rounded-lg border border-white/60 px-4 py-2 text-sm text-white">
                            Book Sekarang
                        </a>
                    </div>

                    <div class="relative h-full">
                        <img 
                            src="{{ asset('images/doctor-promo.png') }}"
                            alt="Dokter Promo"
                            class="absolute bottom-0 left-1/2 h-[205px] -translate-x-1/2 object-contain"
                        >
                    </div>

                    <div>
                        <h2 class="mb-2 text-[30px] font-bold leading-none">DISKON 30%</h2>
                        <p class="text-[15px] leading-relaxed">
                            Dapatkan diskon untuk pengguna baru
                        </p>
                        <p class="text-[15px] font-semibold">mediHub</p>
                    </div>
                </div>
            </section>

            <section class="mb-7">
                <h2 class="mb-4 text-lg font-semibold">Kategori Poli</h2>

                <div class="flex gap-6 overflow-x-auto pb-2">
                    @foreach ($categories as $category)
                        <div class="min-w-[82px] text-center">
                            <div class="mx-auto mb-2 flex h-[72px] w-[72px] items-center justify-center rounded-full bg-blue-400 text-3xl text-white">
                                <i class="fa-solid {{ $category['icon'] }}"></i>
                            </div>
                            <p class="text-sm leading-tight">{{ $category['nama'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section>
                <h2 class="mb-4 text-lg font-semibold">Dokter Pilihan Pasien</h2>

                <div class="flex gap-4 overflow-x-auto pb-4">
                    @foreach ($doctors as $doctor)
                        <div class="min-w-[215px] overflow-hidden rounded-xl bg-white shadow-md">
                            <img 
                                src="{{ $doctor['foto'] }}" 
                                alt="{{ $doctor['nama'] }}"
                                class="h-[155px] w-full bg-blue-100 object-cover"
                            >

                            <div class="p-4">
                                <h3 class="mb-1 text-[15px] font-semibold leading-snug">
                                    {{ $doctor['nama'] }}
                                </h3>

                                <p class="mb-3 text-xs leading-snug text-gray-500">
                                    {{ $doctor['spesialis'] }}
                                </p>

                                <div class="flex gap-3 text-xs text-gray-500">
                                    <span>
                                        <i class="fa-solid fa-star text-yellow-400"></i>
                                        {{ $doctor['rating'] }}
                                    </span>
                                    <span>{{ $doctor['pasien'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="flex flex-col border-l border-gray-200 bg-white px-7 py-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Jadwal Temu Mendatang</h2>
                <a href="#" class="text-sm text-blue-500">Batalkan</a>
            </div>

            <div class="flex flex-col gap-5">
                @foreach ($appointments as $appointment)
                    <div>
                        <p class="mb-2 text-sm text-gray-400">{{ $appointment['hari'] }}</p>

                        <div class="rounded-xl bg-white p-5 shadow-md">
                            <div class="mb-5 flex gap-3">
                                <i class="fa-solid fa-user-doctor text-2xl text-blue-600"></i>

                                <div>
                                    <h3 class="text-sm font-semibold text-blue-600">
                                        {{ $appointment['jenis'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500">{{ $appointment['rs'] }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-[1fr_1.35fr] gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Antrian</p>
                                    <h2 class="text-3xl font-semibold">{{ $appointment['antrian'] }}</h2>
                                </div>

                                <div class="border-l border-gray-200 pl-4">
                                    <p class="mb-1 text-xs text-gray-500">
                                        <i class="fa-regular fa-calendar mr-2"></i>
                                        {{ $appointment['tanggal'] }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        <i class="fa-regular fa-clock mr-2"></i>
                                        {{ $appointment['jam'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="mt-auto flex items-center justify-between rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium text-white shadow-md">
                Buat Jadwal Temu
                <i class="fa-solid fa-plus"></i>
            </button>
        </aside>
    </div>
</body>
</html>