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
    <div class="grid min-h-screen grid-cols-[220px_1fr_380px] overflow-hidden">
        <aside class="flex flex-col justify-between border-r border-gray-200 px-7 py-8">
            <div>
                <div class="mb-10">
                    <img 
                        src="{{ asset('images/Medihub.png') }}"
                        alt="Logo MediHub"
                        class="h-14 w-auto object-contain"
                    >
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
                            id="animatedSearch"
                            type="text" 
                            placeholder=""
                            class="w-full bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>

                    <button class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500">
                        <i class="fa-regular fa-bell"></i>
                    </button>
                </div>
            </header>

            <section class="relative mb-6 h-[230px] overflow-hidden rounded-xl bg-[linear-gradient(90deg,#5FA5F5_0%,#8BDAFE_100%)] px-9 text-white shadow-[0_4px_16px_rgba(0,0,0,0.10)]">
                <img 
                    src="{{ asset('images/lamp.png') }}"
                    alt="Lampu"
                    class="absolute left-1 -top-1 z-50 h-[70px] w-auto rotate-[-8deg] object-contain"
                >
                <div
                    class="pointer-events-none absolute left-[50px] top-[10px] z-[1] h-[240px] w-[560px] rotate-[1deg] opacity-60"
                    style="
                        background: linear-gradient(
                            100deg,
                            rgba(255,255,255,0.42) 0%,
                            rgba(255,255,255,0.26) 24%,
                            rgba(255,255,255,0.10) 55%,
                            rgba(255,255,255,0.00) 100%
                        );

                        clip-path: polygon(
                            0% 12%,
                            100% 34%,
                            100% 100%,
                            12% 100%
                        );

                        filter: blur(3px);
                    ">
                </div>

                <div class="relative z-10 grid h-full grid-cols-[1fr_240px_1fr] items-center">
                    <div class="ml-35">
                        <h2 class="mb-4 bg-[linear-gradient(180deg,#FFFFFF_0%,#E7EEF8_100%)] bg-clip-text text-[26px] font-bold leading-none tracking-wide text-transparent drop-shadow-[0_1px_2px_rgba(255,255,255,0.35)]">PROMO SPESIAL</h2>
                        <button
                            type="button"
                            class="group relative inline-flex cursor-pointer overflow-hidden rounded-[14px]
                                border border-white/55
                                bg-white/12
                                px-5 py-2
                                text-sm font-medium text-white
                                backdrop-blur-xl

                                shadow-[
                                        inset_0_2px_6px_rgba(255,255,255,0.32),
                                        inset_0_-3px_8px_rgba(255,255,255,0.08),
                                        0_4px_12px_rgba(255,255,255,0.10)
                                ]

                                transition-all duration-300
                                hover:bg-white/18
                                hover:shadow-[
                                        inset_0_2px_10px_rgba(255,255,255,0.42),
                                        inset_0_-3px_12px_rgba(255,255,255,0.12),
                                        0_0_18px_rgba(255,255,255,0.16)
                                ]">

                            <span class="relative z-20">
                                Book Sekarang
                            </span>

                            <span
                                class="pointer-events-none absolute left-[8%] top-[6%]
                                    h-[42%] w-[84%]
                                    rounded-full
                                    bg-white/22
                                    blur-md">
                            </span>

                            <span
                                class="pointer-events-none absolute left-[-120%] top-[-35%]
                                    z-10 h-[220%] w-[34%]
                                    rotate-[22deg]
                                    bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.32),transparent)]
                                    blur-md
                                    transition-all duration-1000
                                    group-hover:left-[160%]">
                            </span>
                        </button>
                    </div>

                    <div class="relative h-full">
                        <img 
                            src="{{ asset('images/doctor-promo.png') }}"
                            alt="Dokter Promo"
                            class="absolute bottom-0 left-1/2 h-[205px] -translate-x-1/2 object-contain"
                        >
                    </div>

                    <div class="relative">
                        <h2 class="mb-1 text-[32px] font-semibold leading-none tracking-tight">
                            DISKON 30%
                        </h2>

                        <p class="mb-4 text-[18px] font-light leading-[1.4] text-white/95">
                            Dapatkan diskon untuk pengguna baru
                        </p>

                        <img 
                            src="{{ asset('images/Frame 13.png') }}"
                            alt="MediQ"
                            class="h-6 w-auto object-contain"
                        >
                    </div>
                </div>
            </section>

            <section class="mb-7">
                <h2 class="mb-4 text-lg font-semibold">Kategori Poli</h2>

                <div class="flex gap-6 overflow-x-auto px-2 pt-2 pb-4">
                    @foreach ($categories as $category)
                        <button 
                            type="button"
                            onclick="openPoliPanel('{{ $category['nama'] }}')"
                            class="group min-w-[82px] text-center">
                            
                            <div class="mx-auto mb-2 flex h-[72px] w-[72px] items-center justify-center rounded-full bg-blue-400 text-3xl text-white transition-transform duration-200 group-hover:scale-105">
                                <img 
                                    src="{{ asset('images/categories/' . $category['icon']) }}"
                                    alt="{{ $category['nama'] }}"
                                    class="h-9 w-9 object-contain"
                                >
                            </div>

                            <p class="text-sm leading-tight">{{ $category['nama'] }}</p>
                        </button>
                    @endforeach
                </div>
            </section>

            <section>
                <h2 class="mb-4 text-lg font-semibold">Dokter Pilihan Pasien</h2>

                <div class="flex gap-4 overflow-x-auto pb-4">
                    @foreach ($doctors as $doctor)
                        <div class="min-w-[215px] overflow-hidden rounded-xl bg-white shadow-md">
                            @if ($doctor['foto'])
                                <img 
                                    src="{{ $doctor['foto'] }}" 
                                    alt="{{ $doctor['nama'] }}"
                                    class="h-[155px] w-full bg-blue-100 object-cover"
                                >
                            @else
                                <div class="flex h-[155px] w-full items-center justify-center bg-blue-50">
                                    <i class="fa-solid fa-user-doctor text-5xl text-blue-300"></i>
                                </div>
                            @endif

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
                @forelse ($appointments as $appointment)
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
                @empty
                    <div class="mt-10 flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-10 text-center">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <i class="fa-regular fa-calendar-xmark text-2xl"></i>
                        </div>

                        <h3 class="mb-1 text-sm font-semibold text-gray-700">
                            Belum ada jadwal temu
                        </h3>

                        <p class="text-xs leading-relaxed text-gray-400">
                            Kamu belum memiliki jadwal temu mendatang.
                        </p>
                    </div>
                @endforelse
            </div>

            <button class="mt-auto flex items-center justify-between rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium text-white shadow-md">
                Buat Jadwal Temu
                <i class="fa-solid fa-plus"></i>
            </button>
        </aside>
    </div>

    <div id="poliOverlay"
        onclick="closePoliPanel()"
        class="fixed inset-0 z-[9998] hidden bg-transparent">
    </div>

    <div id="poliPanel"
        class="fixed right-0 top-0 z-[9999] h-screen w-[380px] translate-x-full border-l border-gray-200 bg-white px-7 py-10 shadow-[-8px_0_24px_rgba(0,0,0,0.08)] transition-transform duration-300 ease-out">

        <button 
            type="button"
            onclick="closePoliPanel()"
            class="mb-14">
            
            <img 
                src="{{ asset('images/close.png') }}"
                alt="Close"
                class="h-10 w-auto object-contain opacity-80 transition hover:opacity-140"
            >
        </button>

        <div class="mb-6">
            <h2 class="text-[32px] font-medium leading-none text-blue-400">
                Poli
            </h2>

            <h1 id="poliTitle" class="mt-1 text-[32px] font-medium leading-none text-black"></h1>
        </div>

        <h3 class="mb-4 text-[18px] font-medium text-black">
            Dokter Jaga Hari Ini
        </h3>

        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                <i class="fa-solid fa-user-doctor text-2xl"></i>
            </div>

            <p class="text-sm font-semibold text-gray-700">
                Belum ada dokter jaga
            </p>

            <p class="mt-1 text-xs leading-relaxed text-gray-400">
                Data dokter jaga untuk poli ini belum tersedia.
            </p>
        </div>
    </div>

    <script>
        function openPoliPanel(poliName) {
            document.getElementById('poliTitle').innerText = poliName;

            document.getElementById('poliOverlay').classList.remove('hidden');
            document.getElementById('poliPanel').classList.remove('translate-x-full');
        }

        function closePoliPanel() {
            document.getElementById('poliPanel').classList.add('translate-x-full');

            setTimeout(() => {
                document.getElementById('poliOverlay').classList.add('hidden');
            }, 300);
        }
    </script>
    <script>
        const placeholders = [
            "Dokter Umum",
            "Cari dokter kandungan",
            "Konsultasi dokter",
            "Cari jadwal konsultasi"
        ];

        const input = document.getElementById('animatedSearch');

        let textIndex = 0;
        let charIndex = 0;
        let isDeleting = false;

        function typePlaceholder() {
            const currentText = placeholders[textIndex];

            if (!isDeleting) {
                input.setAttribute(
                    'placeholder',
                    currentText.substring(0, charIndex + 1)
                );

                charIndex++;

                if (charIndex === currentText.length) {
                    isDeleting = true;
                    setTimeout(typePlaceholder, 1800);
                    return;
                }
            } else {
                input.setAttribute(
                    'placeholder',
                    currentText.substring(0, charIndex - 1)
                );

                charIndex--;

                if (charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % placeholders.length;
                }
            }

            setTimeout(typePlaceholder, isDeleting ? 35 : 70);
        }

        typePlaceholder();
    </script>
</body>
</html>