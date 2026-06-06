<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Jadwal Temu - MediHub</title>

    @vite(['resources/css/app.css', 'resources/css/pasien/riwayat.css', 'resources/js/pasien/riwayat.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <div class="grid h-screen grid-cols-[220px_1fr_390px] overflow-hidden">
        <x-pasien.sidebar active="riwayat" />

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
                        <h1 class="text-lg font-semibold transition-colors duration-200 group-hover:text-[#58A7F7] flex items-center gap-1">
                            Halo, {{ explode(' ', auth()->user()->name ?? auth()->user()->fullname ?? 'Pasien')[0] }}
                            <i class="fa-regular fa-circle-user text-sm text-gray-500"></i>
                        </h1>

                        <p class="text-sm text-gray-500">
                            Bagaimana kabarmu?
                        </p>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <div class="flex w-[330px] items-center rounded-xl border border-gray-200 bg-white px-4 py-3">
                        <input
                            id="searchHistory"
                            type="text"
                            placeholder="Di..."
                            class="w-full bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>

                    <button class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500 flex items-center justify-center">
                        <i class="fa-regular fa-bell"></i>
                    </button>
                </div>
            </header>

            @if (session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-xl bg-green-50 px-5 py-4 border border-green-200">
                    <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                    <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-green-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 flex items-center gap-3 rounded-xl bg-red-50 px-5 py-4 border border-red-200">
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                    <div class="text-sm font-medium text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-red-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            <section class="mb-8">
                <h2 class="mb-6 text-2xl font-bold text-gray-900">Riwayat Jadwal Temu</h2>

                <!-- Filter Tabs -->
                <div class="mb-6 flex gap-3 border-b border-gray-200 pb-4">
                    <button
                        data-filter="semua"
                        class="filter-tab px-4 py-2 text-sm font-medium text-gray-600 border-b-2 border-transparent transition-all duration-200 hover:text-gray-900 hover:border-gray-300 active"
                    >
                        Semua
                    </button>
                    <button
                        data-filter="selesai"
                        class="filter-tab px-4 py-2 text-sm font-medium text-gray-600 border-b-2 border-transparent transition-all duration-200 hover:text-gray-900 hover:border-gray-300"
                    >
                        Selesai
                    </button>
                    <button
                        data-filter="dibatalkan"
                        class="filter-tab px-4 py-2 text-sm font-medium text-gray-600 border-b-2 border-transparent transition-all duration-200 hover:text-gray-900 hover:border-gray-300"
                    >
                        Dibatalkan
                    </button>
                </div>

                <!-- Appointment History Grid -->
                @if (count($riwayatJadwal) > 0)
                    <div class="grid grid-cols-2 gap-5" data-history-grid>
                        @foreach ($riwayatJadwal as $appointment)
                            @php
                                $statusKey = $appointment['status_key'] ?? strtolower($appointment['status']);
                                $statusClasses = match ($statusKey) {
                                    'selesai' => 'bg-[#E8F8F0] text-[#0E7043]',
                                    'dibatalkan' => 'bg-[#FEECEB] text-[#BC2218]',
                                    default => 'bg-[#FFF9E6] text-[#B7791F]',
                                };
                                $dotColor = match ($statusKey) {
                                    'selesai' => 'bg-[#0E7043]',
                                    'dibatalkan' => 'bg-[#BC2218]',
                                    default => 'bg-[#B7791F]',
                                };
                            @endphp
                            <div
                                class="appointment-card overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-[2px]"
                                data-status="{{ $statusKey }}"
                            >
                                <div class="flex flex-col">
                                    <!-- Top Section -->
                                    <div class="mb-4 flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EBF5FF] text-[#3B82F6]">
                                                <i class="fa-solid fa-user-doctor text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-gray-900">
                                                    {{ $appointment['jenis'] }}
                                                </h3>
                                                <p class="text-xs text-gray-500">
                                                    {{ $appointment['rs'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                            {{ $appointment['status'] }}
                                        </span>
                                    </div>

                                    <!-- Doctor Name -->
                                    <h4 class="text-base font-semibold text-gray-900 mb-4">
                                        {{ $appointment['dokter'] }}
                                    </h4>

                                    <!-- Date and Time -->
                                    <div class="flex items-center gap-6 pt-3 border-t border-gray-50">
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <i class="fa-regular fa-calendar text-sm text-gray-400"></i>
                                            <span>{{ $appointment['tanggal'] }}</span>
                                        </div>

                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <i class="fa-regular fa-clock text-sm text-gray-400"></i>
                                            <span>{{ $appointment['jam'] }}</span>
                                        </div>
                                    </div>

                                    @if (($appointment['keluhan'] ?? null) || ($appointment['diagnosa'] ?? null) || ($appointment['catatan_medis'] ?? null) || ($appointment['resep_obat'] ?? null) || $statusKey === 'selesai')
                                        <div class="mt-5 space-y-3 rounded-xl bg-[#F8FAFC] p-4 border border-gray-50">
                                            @if ($appointment['keluhan'] ?? null)
                                                <div>
                                                    <p class="mb-1 text-xs font-semibold text-gray-400">Keluhan</p>
                                                    <p class="text-sm leading-relaxed text-gray-800">{{ $appointment['keluhan'] }}</p>
                                                </div>
                                            @endif

                                            @if ($appointment['diagnosa'] ?? null)
                                                <div>
                                                    <p class="mb-1 text-xs font-semibold text-gray-400">Hasil Diagnosa</p>
                                                    <p class="text-sm leading-relaxed text-gray-800">{{ $appointment['diagnosa'] }}</p>
                                                </div>
                                            @elseif ($statusKey === 'selesai')
                                                <div>
                                                    <p class="mb-1 text-xs font-semibold text-gray-400">Hasil Diagnosa</p>
                                                    <p class="text-sm leading-relaxed text-gray-400 italic">Belum ada hasil diagnosa.</p>
                                                </div>
                                            @endif

                                            @if ($appointment['catatan_medis'] ?? null)
                                                <div>
                                                    <p class="mb-1 text-xs font-semibold text-gray-400">Catatan Medis</p>
                                                    <p class="text-sm leading-relaxed text-gray-800">{{ $appointment['catatan_medis'] }}</p>
                                                </div>
                                            @endif

                                            @if ($appointment['resep_obat'] ?? null)
                                                <div>
                                                    <p class="mb-1 text-xs font-semibold text-gray-400">Resep Obat</p>
                                                    <p class="text-sm leading-relaxed text-gray-800">{{ $appointment['resep_obat'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-16 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <i class="fa-regular fa-calendar-xmark text-3xl"></i>
                        </div>

                        <h3 class="mb-2 text-lg font-semibold text-gray-700">
                            Belum ada riwayat jadwal temu
                        </h3>

                        <p class="text-sm leading-relaxed text-gray-500 mb-6">
                            Jadwal temu yang sudah selesai atau dibatalkan akan muncul di sini.
                        </p>

                        <a
                            href="{{ route('pasien.booking.create') }}"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
                        >
                            Buat Jadwal Temu Sekarang <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                @endif
            </section>
        </main>

        <!-- Right Panel: Jadwal Mendatang -->
        <aside class="sticky top-0 flex h-screen flex-col border-l border-gray-200 bg-white px-6 py-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Jadwal Temu Mendatang</h2>
                <button id="toggleCancelBtn" class="text-xs font-medium text-[#58A7F7] hover:text-[#4796E6] transition-colors">
                    Batalkan
                </button>
            </div>

            <div class="flex flex-col gap-5 overflow-y-auto flex-1 pr-2">
                @forelse ($jadwalMendatang as $appointment)
                    <div class="group">
                        <p class="mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $appointment['hari'] }}</p>

                        <div class="rounded-xl border border-gray-100 bg-[#F8FAFC] p-4 shadow-sm transition-all duration-200 hover:shadow-md">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EBF5FF] text-[#3B82F6]">
                                    <i class="fa-solid fa-user-doctor text-lg"></i>
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-[#1E3A8A]">
                                        {{ $appointment['jenis'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500">{{ $appointment['rs'] }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-[80px_1fr] gap-4 py-3 border-t border-gray-100">
                                <!-- Antrian -->
                                <div class="border-r border-gray-100 pr-3">
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-0.5">Antrian</p>
                                    <p class="text-3xl font-extrabold text-gray-900 leading-tight">
                                        {{ is_numeric($appointment['antrian']) ? sprintf('%02d', intval($appointment['antrian'])) : $appointment['antrian'] }}
                                    </p>
                                </div>

                                <!-- DateTime -->
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fa-regular fa-calendar text-gray-400 text-sm w-4"></i>
                                        <span>{{ $appointment['tanggal'] }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fa-regular fa-clock text-gray-400 text-sm w-4"></i>
                                        <span>{{ $appointment['jam'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancel Button (Toggled by "Batalkan" header button) -->
                            <div class="cancel-btn-container hidden mt-3 pt-3 border-t border-gray-100">
                                <form
                                    method="POST"
                                    action="{{ route('pasien.booking.cancel', ['id' => $appointment['id']]) }}"
                                    class="cancel-form"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="cancellation_reason" value="Dibatalkan oleh pasien">
                                    <button
                                        type="submit"
                                        class="w-full text-center px-3 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-semibold border border-red-100 transition-all duration-200 hover:bg-red-100"
                                        onclick="return confirm('Apakah Anda yakin ingin membatalkan jadwal temu ini?')"
                                    >
                                        Batalkan Jadwal Temu ini
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-[#F8FAFC] px-4 py-12 text-center">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <i class="fa-regular fa-calendar-check text-2xl"></i>
                        </div>

                        <h3 class="mb-1 text-sm font-semibold text-gray-700">
                            Tidak ada jadwal mendatang
                        </h3>

                        <p class="text-xs leading-relaxed text-gray-400 mb-4">
                            Buat jadwal temu baru sekarang.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Create Appointment Button -->
            <a
                href="{{ route('pasien.booking.create') }}"
                class="mt-6 flex items-center justify-between rounded-xl bg-[#58A7F7] hover:bg-[#4796E6] px-5 py-4 text-sm font-medium text-white shadow-md transition-all duration-300 hover:-translate-y-[2px]"
            >
                <span>Buat Jadwal Temu</span>
                <i class="fa-solid fa-circle-plus text-lg text-white"></i>
            </a>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Cancel Buttons in upcoming schedules
            const toggleCancelBtn = document.getElementById('toggleCancelBtn');
            const cancelBtnContainers = document.querySelectorAll('.cancel-btn-container');

            if (toggleCancelBtn) {
                toggleCancelBtn.addEventListener('click', function() {
                    cancelBtnContainers.forEach(container => {
                        container.classList.toggle('hidden');
                    });
                    if (toggleCancelBtn.textContent.trim() === 'Batalkan') {
                        toggleCancelBtn.textContent = 'Selesai';
                        toggleCancelBtn.classList.remove('text-[#58A7F7]');
                        toggleCancelBtn.classList.add('text-gray-500');
                    } else {
                        toggleCancelBtn.textContent = 'Batalkan';
                        toggleCancelBtn.classList.remove('text-gray-500');
                        toggleCancelBtn.classList.add('text-[#58A7F7]');
                    }
                });
            }

            // Tabs and search filter logic
            const filterTabs = document.querySelectorAll('.filter-tab');
            const appointmentCards = document.querySelectorAll('[data-history-grid] .appointment-card');
            const searchInput = document.getElementById('searchHistory');
            let activeFilter = 'semua';

            const applyFilters = () => {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';

                appointmentCards.forEach(card => {
                    const status = card.dataset.status;
                    const text = card.textContent.toLowerCase();
                    const matchesStatus = activeFilter === 'semua' || status === activeFilter;
                    const matchesSearch = searchTerm === '' || text.includes(searchTerm);

                    if (matchesStatus && matchesSearch) {
                        card.style.display = 'block';
                        setTimeout(() => card.style.opacity = '1', 0);
                    } else {
                        card.style.opacity = '0';
                        setTimeout(() => card.style.display = 'none', 200);
                    }
                });
            };

            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    activeFilter = this.dataset.filter;

                    // Update active tab styles
                    filterTabs.forEach(t => {
                        t.classList.remove('active');
                        t.style.borderBottomColor = 'transparent';
                        t.style.color = '#4B5563';
                    });
                    this.classList.add('active');
                    this.style.borderBottomColor = '#3B82F6';
                    this.style.color = '#111827';

                    applyFilters();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
        });
    </script>
</body>
</html>
