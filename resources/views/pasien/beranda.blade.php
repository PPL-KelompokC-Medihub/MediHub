<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Pasien - MediHub</title>

    @vite(['resources/css/app.css', 'resources/css/pasien/beranda.css', 'resources/js/pasien/beranda.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <div 
        data-patient-home
        data-doctors='@json($doctors)'
    ></div>
    <div class="grid h-screen grid-cols-[220px_1fr_380px] overflow-hidden">
        <x-pasien.sidebar active="beranda" />

        <main class="h-screen overflow-y-auto bg-[#fbfbfb] px-8 py-8">
            <header class="mb-6 flex items-center justify-between gap-6">
                <a 
                    href="{{ route('pasien.profile') }}"
                    class="group flex items-center gap-4 transition-all duration-200 hover:-translate-y-[2px]"
                >
                    <img 
                        src="{{ $patient['profile_pict'] }}"
                        class="h-14 w-14 rounded-full object-cover transition-all duration-200 group-hover:ring-2 group-hover:ring-blue-300"
                        alt="Avatar"
                    >

                    <div>
                        <h1 class="text-lg font-semibold transition-colors duration-200 group-hover:text-[#58A7F7]">
                            Halo, {{ auth()->user()->name ?? auth()->user()->fullname ?? 'Pasien' }} 👋
                        </h1>

                        <p class="text-sm text-gray-500">
                            Bagaimana kabarmu?
                        </p>
                    </div>
                </a>

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

                    <button 
                        id="notificationButton"
                        class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500 relative"
                    >
                        <i class="fa-regular fa-bell"></i>

                        @if(count($appointments) > 0)
                            <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        @endif
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
                    class="patient-promo-light pointer-events-none absolute left-[50px] top-[10px] z-[1] h-[240px] w-[560px] rotate-[1deg] opacity-60">
                </div>

                <div class="relative z-10 grid h-full grid-cols-[1fr_240px_1fr] items-center">
                    <div class="ml-[35px]">
                        <h2 
                            class="mb-4 text-[26px] font-bold leading-none tracking-wide drop-shadow-[0_1px_2px_rgba(255,255,255,0.25)]"
                            style="
                                background: linear-gradient(180deg, #E8E8E8 0%, #FFFFFF 100%);
                                -webkit-background-clip: text;
                                -webkit-text-fill-color: transparent;
                                background-clip: text;
                                color: transparent;
                            "
                        >
                            PROMO SPESIAL
                        </h2>
                        <button
                            type="button"
                            class="mt-3 group relative inline-flex cursor-pointer overflow-hidden rounded-[14px]
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
                        <h2 
                            class="mb-1 text-[28px] font-semibold leading-[1.2]"
                            style="
                                background: linear-gradient(180deg, #E8E8E8 0%, #FFFFFF 100%);
                                -webkit-background-clip: text;
                                -webkit-text-fill-color: transparent;
                                background-clip: text;
                                color: transparent;
                                text-shadow: 0 2px 6px rgba(255,255,255,0.18);
                            "
                        >
                            DISKON 30%
                        </h2>

                        <p 
                            class="mt-2 mb-4 text-[17px] font-normal leading-[1.2]"
                            style="
                                color: #FFFFFF;
                                text-shadow: 0 1px 4px rgba(255,255,255,0.12);
                            "
                        >
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

            <section id="layanan" class="mb-7 scroll-mt-8">
                <h2 class="mb-4 text-lg font-semibold">Kategori Poli</h2>

                <div class="flex gap-6 overflow-x-auto px-2 pt-2 pb-4">
                    @foreach ($categories as $category)
                        <button 
                            type="button"
                            data-poli-name="{{ $category['nama'] }}"
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

            <section class="mb-7">
                <h2 class="mb-4 text-lg font-semibold">Dokter Pilihan Pasien</h2>

                <div class="flex gap-4 overflow-x-auto pb-4">
                    @foreach ($doctors as $doctor)
                        <a href="{{ $doctor['id'] ? route('pasien.booking.create', ['doctor_id' => $doctor['id']]) : route('pasien.booking.create') }}" class="min-w-[215px] overflow-hidden rounded-xl bg-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg">
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
                        </a>
                    @endforeach
                </div>
            </section>

        </main>

        <aside class="sticky top-0 flex h-screen flex-col border-l border-gray-200 bg-white px-7 py-8">

            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Jadwal Temu Mendatang</h2>

                <button 
                    id="toggleCancelMode"
                    type="button"
                    class="text-sm text-blue-500 transition hover:text-blue-700"
                >
                    Batalkan
                </button>
            </div>

            <form 
                id="cancelAppointmentForm"
                action="{{ route('pasien.booking.destroy') }}"
                method="POST"
                class="flex flex-col h-full"
            >
                @csrf
                @method('DELETE')

                <div class="flex flex-col gap-5">

                    @forelse ($appointments as $appointment)

                        <div>
                            <p class="mb-2 text-sm text-gray-400">
                                {{ $appointment['hari'] }}
                            </p>

                            <div class="relative rounded-xl bg-white p-5 shadow-md">

                                <!-- CHECKBOX -->
                                <label class="cancel-checkbox absolute right-4 top-4 cursor-pointer hidden">
                                    <input 
                                        type="checkbox"
                                        name="appointments[]"
                                        value="{{ $appointment['appointment_id'] ?? $appointment['id'] }}"
                                        class="h-6 w-6 accent-red-500"
                                    >
                                </label>

                                <div class="mb-5 flex gap-3">
                                    <i class="fa-solid fa-user-doctor text-2xl text-blue-600"></i>

                                    <div>
                                        <h3 class="text-sm font-semibold text-blue-600">
                                            {{ $appointment['jenis'] }}
                                        </h3>

                                        <p class="text-xs text-gray-500">
                                            {{ $appointment['rs'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-[1fr_1.35fr] gap-4">

                                    <div>
                                        <p class="text-xs text-gray-500">
                                            Antrian
                                        </p>

                                        <h2 class="text-3xl font-semibold">
                                            {{ $appointment['antrian'] }}
                                        </h2>
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

                <button
                    id="submitCancelButton"
                    type="button"
                    class="group relative mt-auto flex items-center justify-between overflow-hidden rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium shadow-md transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_10px_24px_rgba(96,165,250,0.45)]"
                >

                    <span class="relative z-10 text-white">
                        Buat Jadwal Temu
                    </span>

                    <i class="fa-solid fa-circle-plus relative z-10 text-white text-[18px]"></i>

                    <!-- glow -->
                    <span
                        class="pointer-events-none absolute left-[10%] top-[8%]
                        h-[42%] w-[80%]
                        rounded-full bg-white/20 blur-md">
                    </span>

                    <!-- shine -->
                    <span
                        class="pointer-events-none absolute left-[-120%] top-[-40%]
                        h-[220%] w-[35%]
                        rotate-[20deg]
                        bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.35),transparent)]
                        blur-md
                        transition-all duration-1000
                        group-hover:left-[160%]">
                    </span>

                </button>

            </form>

        </aside>
    </div>

    <div id="poliOverlay"
        data-close-poli
        class="fixed inset-0 z-[9998] hidden bg-transparent">
    </div>

    <div id="poliPanel"
        class="fixed right-0 top-0 z-[9999] h-screen w-[420px] translate-x-full overflow-y-auto border-l border-gray-200 bg-white px-7 py-10 shadow-[-8px_0_24px_rgba(0,0,0,0.08)] transition-transform duration-300 ease-out">

        <button 
            type="button"
            data-close-poli
            class="mb-14">
            
            <img 
                src="{{ asset('images/close.png') }}"
                alt="Close"
                class="h-10 w-auto object-contain opacity-80 transition hover:opacity-140"
            >
        </button>

        <div class="mb-6">
            <p class="text-[32px] font-medium leading-none text-[#59A5F5]">
                Poli
            </p>

            <h1 id="poliTitle" class="mt-1 text-[32px] font-medium leading-none text-black"></h1>
        </div>

        <h3 class="mb-4 text-[18px] font-medium text-black">
            Dokter Jaga Hari Ini
        </h3>

        <div id="dokterJagaList" class="space-y-4">
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
 </div>
 <!-- OVERLAY -->
<div 
    id="notificationOverlay"
    class="fixed inset-0 z-[9998] hidden bg-black/20"
></div>

<!-- PANEL NOTIFIKASI -->
<div
    id="notificationPanel"
    class="fixed right-8 top-24 z-[9999] hidden w-[360px] rounded-2xl bg-white p-6 shadow-2xl"
>

    <div class="mb-5 flex items-center justify-between">
        <h2 class="text-xl font-semibold">
            Pusat Notifikasi
        </h2>

        <button 
            id="closeNotification"
            class="text-gray-400 hover:text-gray-600"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="space-y-5">

        @forelse ($notifications as $notification)

            <div class="flex gap-3 py-4">

                {{-- ICON --}}
                <div class="mt-1 text-gray-400">
                    <i class="fa-regular fa-file-lines text-[18px]"></i>
                </div>

                {{-- CONTENT --}}
                <div class="flex-1 border-b border-gray-200 pb-4">

                    {{-- HEADER --}}
                    <div class="mb-2 flex items-start justify-between gap-3">

                        <h3 class="text-[16px] font-semibold leading-5 text-[#1E1E1E]">
                            {{ $notification['title'] }}
                        </h3>

                        <span class="whitespace-nowrap text-[13px] text-gray-400">
                            @if(\Carbon\Carbon::parse($notification['date'])->isToday())
                                Hari ini
                            @else
                                {{ \Carbon\Carbon::parse($notification['date'])->translatedFormat('d F') }}
                            @endif
                        </span>

                    </div>

                    {{-- MESSAGE --}}
                    <p class="text-[15px] leading-[22px] text-[#7A7A7A]">
                        {{ $notification['message'] }}
                    </p>

                </div>

            </div>

        @empty

            <div class="py-10 text-center text-sm text-gray-400">
                Belum ada notifikasi
            </div>

        @endforelse


    <script>
        const notificationButton = document.getElementById('notificationButton');
        const notificationPanel = document.getElementById('notificationPanel');
        const notificationOverlay = document.getElementById('notificationOverlay');
        const closeNotification = document.getElementById('closeNotification');

        notificationButton.addEventListener('click', () => {

            notificationPanel.classList.remove('hidden');
            notificationOverlay.classList.remove('hidden');

        });

        closeNotification.addEventListener('click', closeNotificationPanel);
        notificationOverlay.addEventListener('click', closeNotificationPanel);

        function closeNotificationPanel() {

            notificationPanel.classList.add('hidden');
            notificationOverlay.classList.add('hidden');

        }

        const toggleButton = document.getElementById('toggleCancelMode');
        const cancelButton = document.getElementById('submitCancelButton');

        let cancelMode = false;

        // BUTTON BAWAH
        cancelButton.addEventListener('click', () => {

            // MODE NORMAL → KE HALAMAN BOOKING
            if (!cancelMode) {

                window.location.href = "{{ route('pasien.booking.create') }}";
                return;
            }

            // MODE PEMBATALAN → SUBMIT FORM DELETE
            document.getElementById('cancelAppointmentForm').submit();
        });

        // TOGGLE MODE BATALKAN
        toggleButton.addEventListener('click', () => {

            cancelMode = !cancelMode;

            const checkboxes = document.querySelectorAll('.cancel-checkbox');

            checkboxes.forEach(el => {
                el.classList.toggle('hidden');
            });

            if (cancelMode) {

                toggleButton.innerText = 'Kembali';

                cancelButton.classList.remove('bg-blue-400');
                cancelButton.classList.add('bg-red-500');

                cancelButton.querySelector('span').innerText = 'Batalkan Jadwal Temu';

            } else {

                toggleButton.innerText = 'Batalkan';

                cancelButton.classList.remove('bg-red-500');
                cancelButton.classList.add('bg-blue-400');

                cancelButton.querySelector('span').innerText = 'Buat Jadwal Temu';

            }

        });

    </script>

</body>
</html>
