<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Jadwal Temu - MediHub</title>
    @include('partials.favicons')

    @vite(['resources/css/app.css', 'resources/css/pasien/riwayat.css', 'resources/js/pasien/riwayat.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <x-pasien.sidebar active="riwayat" />

    <div class="ml-[220px] grid h-screen grid-cols-[minmax(0,1fr)_390px] overflow-hidden bg-white">
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
                @if (count($historyBookings) > 0)
                    <div class="grid grid-cols-2 gap-5" data-history-grid>
                        @foreach ($historyBookings as $appointment)
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
                                data-appointment-id="{{ $appointment['id'] }}"
                                data-jenis="{{ $appointment['jenis'] }}"
                                data-dokter="{{ $appointment['dokter'] }}"
                                data-dokter-foto="{{ $appointment['dokter_foto'] }}"
                                data-tanggal="{{ $appointment['tanggal'] }}"
                                data-jam="{{ $appointment['jam'] }}"
                                data-keluhan="{{ $appointment['keluhan'] ?? '-' }}"
                                data-alergi="{{ $appointment['alergi'] ?? '-' }}"
                                data-alasan-pembatalan="{{ $appointment['alasan_pembatalan'] ?? '-' }}"
                                data-pemeriksaan-fisik="{{ $appointment['pemeriksaan_fisik'] ?? '-' }}"
                                data-diagnosis-sementara="{{ $appointment['diagnosis_sementara'] ?? '-' }}"
                                data-rencana-penanganan="{{ $appointment['rencana_penanganan'] ?? '-' }}"
                                data-catatan-dokter="{{ $appointment['catatan_dokter'] ?? '-' }}"
                                data-resep-obat="{{ $appointment['resep_obat'] ?? '-' }}"
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
        <aside class="sticky top-0 flex h-screen w-[390px] shrink-0 flex-col border-l border-gray-200 bg-white px-7 py-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Jadwal Temu Mendatang</h2>
                <button id="toggleCancelBtn" class="text-xs font-medium text-[#58A7F7] hover:text-[#4796E6] transition-colors">
                    Batalkan
                </button>
            </div>

            <div class="flex flex-col gap-5 overflow-y-auto flex-1 pr-2">
                @forelse ($upcomingBookings as $appointment)
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

                    @endforelse

                </div>

                <button
                    id="submitCancelButton"
                    type="button"
                    class="group relative mt-6 flex items-center justify-between overflow-hidden rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium shadow-md transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_10px_24px_rgba(96,165,250,0.45)]"
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

    <!-- Modal Detail Jadwal Temu -->
    <div id="appointmentDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-all duration-300">
        <div class="relative w-full max-w-[420px] rounded-3xl bg-white p-6 shadow-2xl transition-all duration-300 scale-95 overflow-y-auto max-h-[90vh]">
            <!-- Close Button -->
            <button id="closeModalBtn" class="absolute left-6 top-6 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <div class="mt-8 flex flex-col">
                <!-- Modal Title & Status Badge -->
                <div class="mb-5 flex items-center justify-between">
                    <h2 id="modalTitle" class="text-[17px] font-semibold text-gray-950">Riwayat Jadwal Temu</h2>
                    <span id="modalStatusBadge" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        <span id="modalStatusText">Selesai</span>
                    </span>
                </div>

                <!-- Doctor Row -->
                <div class="mb-5 flex items-center gap-4">
                    <img id="modalDoctorPhoto" src="" alt="Doctor Avatar" class="h-16 w-16 rounded-full object-cover">
                    <div>
                        <h3 id="modalDoctorName" class="text-sm font-semibold text-gray-900">dr. Clara Wulandari, M.Ked</h3>
                        <p id="modalDoctorSpecialization" class="text-xs text-gray-500">Dokter umum</p>
                    </div>
                </div>

                <!-- Date & Time Row -->
                <div class="mb-6 grid grid-cols-2 gap-4 rounded-2xl bg-gray-50 p-4">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-1">
                            <i class="fa-regular fa-calendar mr-1"></i> Tanggal
                        </p>
                        <p id="modalAppointmentDate" class="text-xs font-semibold text-gray-800">30 Agustus 2025</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-1">
                            <i class="fa-regular fa-clock mr-1"></i> Waktu
                        </p>
                        <p id="modalAppointmentTime" class="text-xs font-semibold text-gray-800">13:00 WIB - 13:15 WIB</p>
                    </div>
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

            // Modal Detail Setup
            const modal = document.getElementById('appointmentDetailModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const modalTitle = document.getElementById('modalTitle');
            const modalStatusBadge = document.getElementById('modalStatusBadge');
            const modalStatusText = document.getElementById('modalStatusText');
            const modalDoctorPhoto = document.getElementById('modalDoctorPhoto');
            const modalDoctorName = document.getElementById('modalDoctorName');
            const modalDoctorSpecialization = document.getElementById('modalDoctorSpecialization');
            const modalAppointmentDate = document.getElementById('modalAppointmentDate');
            const modalAppointmentTime = document.getElementById('modalAppointmentTime');
            const modalPatientName = document.getElementById('modalPatientName');
            const modalPatientSymptoms = document.getElementById('modalPatientSymptoms');
            const modalPatientAllergy = document.getElementById('modalPatientAllergy');
            
            const modalDiagnosisSection = document.getElementById('modalDiagnosisSection');
            const modalPhysicalExamList = document.getElementById('modalPhysicalExamList');
            const modalPhysicalExamText = document.getElementById('modalPhysicalExamText');
            const modalTempDiagnosis = document.getElementById('modalTempDiagnosis');
            const modalTreatmentPlan = document.getElementById('modalTreatmentPlan');
            const modalDoctorNotes = document.getElementById('modalDoctorNotes');
            const modalPrescriptionDiv = document.getElementById('modalPrescriptionDiv');
            const modalPrescription = document.getElementById('modalPrescription');
            
            const modalCancellationSection = document.getElementById('modalCancellationSection');
            const modalCancellationReason = document.getElementById('modalCancellationReason');

            const openModal = (card) => {
                const status = card.getAttribute('data-status');
                const title = card.getAttribute('data-jenis');
                const doctor = card.getAttribute('data-dokter');
                const doctorFoto = card.getAttribute('data-dokter-foto');
                const date = card.getAttribute('data-tanggal');
                const time = card.getAttribute('data-jam');
                const symptoms = card.getAttribute('data-keluhan');
                const allergy = card.getAttribute('data-alergi');
                
                const cancellationReason = card.getAttribute('data-alasan-pembatalan');
                const physicalExam = card.getAttribute('data-pemeriksaan-fisik');
                const tempDiagnosis = card.getAttribute('data-diagnosis-sementara');
                const treatmentPlan = card.getAttribute('data-rencana-penanganan');
                const doctorNotes = card.getAttribute('data-catatan-dokter');
                const prescription = card.getAttribute('data-resep-obat');

                // Set content
                modalTitle.textContent = status === 'mendatang' ? 'Jadwal Temu Mendatang' : 'Riwayat Jadwal Temu';
                modalDoctorPhoto.src = doctorFoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor) + '&background=6aa4ef&color=fff&size=80';
                modalDoctorName.textContent = doctor;
                modalDoctorSpecialization.textContent = title;
                modalAppointmentDate.textContent = date;
                modalAppointmentTime.textContent = time;
                modalPatientName.textContent = card.dataset.patientName || "{{ auth()->user()->name ?? auth()->user()->fullname ?? 'Pasien' }}";
                modalPatientSymptoms.textContent = (symptoms && symptoms !== '-') ? symptoms : 'Tidak ada gejala tercatat';
                modalPatientAllergy.textContent = (allergy && allergy !== '-') ? allergy : 'Tidak ada alergi obat';

                // Status Badge Styling
                let statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
                modalStatusText.textContent = statusLabel;
                modalStatusBadge.className = 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold';
                if (status === 'selesai') {
                    modalStatusBadge.classList.add('bg-green-100', 'text-green-700');
                } else if (status === 'dibatalkan') {
                    modalStatusBadge.classList.add('bg-red-100', 'text-red-700');
                } else { // mendatang
                    modalStatusBadge.classList.add('bg-blue-100', 'text-blue-700');
                }

                // Show/hide sections based on status
                if (status === 'selesai') {
                    modalDiagnosisSection.classList.remove('hidden');
                    modalCancellationSection.classList.add('hidden');

                    // Parse & Format Physical Exam list
                    modalPhysicalExamList.innerHTML = '';
                    if (physicalExam && physicalExam !== '-' && physicalExam.trim() !== '') {
                        // Split by newline or bullet points
                        const lines = physicalExam.split(/[\r\n]+/)
                            .map(line => line.replace(/^[•\-\*\s]+/, '').trim())
                            .filter(line => line !== '');
                        
                        if (lines.length > 0) {
                            modalPhysicalExamText.classList.add('hidden');
                            modalPhysicalExamList.classList.remove('hidden');
                            lines.forEach(line => {
                                const li = document.createElement('li');
                                li.textContent = line;
                                modalPhysicalExamList.appendChild(li);
                            });
                        } else {
                            modalPhysicalExamList.classList.add('hidden');
                            modalPhysicalExamText.classList.remove('hidden');
                            modalPhysicalExamText.textContent = '-';
                        }
                    } else {
                        modalPhysicalExamList.classList.add('hidden');
                        modalPhysicalExamText.classList.remove('hidden');
                        modalPhysicalExamText.textContent = '-';
                    }

                    modalTempDiagnosis.textContent = tempDiagnosis || '-';
                    modalTreatmentPlan.textContent = treatmentPlan || '-';
                    modalDoctorNotes.textContent = doctorNotes || '-';

                    if (prescription && prescription !== '-' && prescription.trim() !== '') {
                        modalPrescriptionDiv.classList.remove('hidden');
                        modalPrescription.textContent = prescription;
                    } else {
                        modalPrescriptionDiv.classList.add('hidden');
                    }
                } else if (status === 'dibatalkan') {
                    modalDiagnosisSection.classList.add('hidden');
                    modalCancellationSection.classList.remove('hidden');
                    modalCancellationReason.textContent = (cancellationReason && cancellationReason !== '-') ? cancellationReason : 'Dibatalkan oleh sistem/pasien';
                } else { // mendatang / pending
                    modalDiagnosisSection.classList.remove('hidden');
                    modalCancellationSection.classList.add('hidden');

                    modalPhysicalExamList.classList.add('hidden');
                    modalPhysicalExamText.classList.remove('hidden');
                    modalPhysicalExamText.textContent = '-';
                    modalTempDiagnosis.textContent = '-';
                    modalTreatmentPlan.textContent = '-';
                    modalDoctorNotes.textContent = '-';
                    modalPrescriptionDiv.classList.add('hidden');
                }

                // Show modal with opacity transition
                modal.classList.remove('pointer-events-none');
                modal.classList.remove('opacity-0');
                modal.firstElementChild.classList.remove('scale-95');
                modal.firstElementChild.classList.add('scale-100');
            };

            const closeModal = () => {
                modal.classList.add('opacity-0');
                modal.classList.add('pointer-events-none');
                modal.firstElementChild.classList.remove('scale-100');
                modal.firstElementChild.classList.add('scale-95');
            };

            let cancelMode = false;
            const toggleButton = document.getElementById('toggleCancelMode');
            const cancelButton = document.getElementById('submitCancelButton');

            if (cancelButton) {
                cancelButton.addEventListener('click', () => {
                    // MODE NORMAL → KE HALAMAN BOOKING
                    if (!cancelMode) {
                        window.location.href = "{{ route('pasien.booking.create') }}";
                        return;
                    }
                    // MODE PEMBATALAN → SUBMIT FORM DELETE
                    document.getElementById('cancelAppointmentForm').submit();
                });
            }

            if (toggleButton) {
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
            }

            // Event Listeners for click on cards
            document.querySelectorAll('.appointment-card').forEach(card => {
                card.addEventListener('click', () => openModal(card));
            });

            document.querySelectorAll('.upcoming-appointment-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (cancelMode) {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        if (checkbox && e.target !== checkbox) {
                            checkbox.checked = !checkbox.checked;
                        }
                    } else {
                        openModal(this);
                    }
                });
            });

            closeModalBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
