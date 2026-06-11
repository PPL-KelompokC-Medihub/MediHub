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
                        <h1 class="text-lg font-semibold transition-colors duration-200 group-hover:text-[#58A7F7]">
                            Halo, {{ auth()->user()->name ?? auth()->user()->fullname ?? 'Pasien' }} 👋
                        </h1>

                        <p class="text-sm text-gray-500">
                            Lihat riwayat jadwal temu kamu
                        </p>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <div class="flex w-[330px] items-center rounded-xl border border-gray-200 bg-white px-4 py-3">
                        <input
                            id="searchHistory"
                            type="text"
                            placeholder="Cari jadwal temu..."
                            class="w-full bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>

                    <button class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500">
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
                <h2 class="mb-6 text-2xl font-semibold">Riwayat Jadwal Temu</h2>

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
                                    'selesai' => 'bg-green-100 text-green-700',
                                    'dibatalkan' => 'bg-red-100 text-red-700',
                                    default => 'bg-yellow-100 text-yellow-700',
                                };
                                $statusIcon = match ($statusKey) {
                                    'selesai' => 'fa-check-circle',
                                    'dibatalkan' => 'fa-circle-xmark',
                                    default => 'fa-clock',
                                };
                            @endphp
                            <div
                                class="appointment-card overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-[2px] cursor-pointer"
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
                                <div class="flex flex-col p-5">
                                    <!-- Top Section -->
                                    <div class="mb-5 flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <i class="fa-solid fa-user-doctor text-2xl text-[#58A7F7] shrink-0"></i>

                                            <div>
                                                <h3 class="text-sm font-semibold text-blue-600">
                                                    {{ $appointment['jenis'] }}
                                                </h3>
                                                <p class="text-xs text-gray-400">
                                                    {{ $appointment['rs'] }} - Bandung
                                                </p>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ $appointment['status'] }}
                                        </span>
                                    </div>

                                    <!-- Doctor Name -->
                                    <div class="mb-4">
                                        <h4 class="text-sm font-bold text-gray-900">
                                            {{ $appointment['dokter'] }}
                                        </h4>
                                    </div>

                                    <!-- Date and Time -->
                                    <div class="grid grid-cols-[1fr_1.35fr] gap-4 pt-4 border-t border-gray-100">
                                        <div class="flex flex-col items-start gap-1.5">
                                            <i class="fa-regular fa-calendar text-gray-400 text-base"></i>
                                            <p class="text-[13px] text-gray-500 font-medium">{{ $appointment['tanggal'] }}</p>
                                        </div>

                                        <div class="border-l border-gray-200 pl-4 flex flex-col items-start gap-1.5">
                                            <i class="fa-regular fa-clock text-gray-400 text-base"></i>
                                            <p class="text-[13px] text-gray-500 font-medium">{{ $appointment['jam'] }}</p>
                                        </div>
                                    </div>
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

                <div class="flex flex-col gap-5 overflow-y-auto flex-1 pr-1">

                    @forelse ($upcomingBookings as $appointment)

                        <div>
                            <p class="mb-2 text-xs text-gray-400 uppercase tracking-wide">
                                {{ $appointment['hari'] }}
                            </p>

                            <div 
                                class="upcoming-appointment-card relative rounded-xl bg-white p-5 shadow-md border border-gray-100 hover:shadow-lg transition-all duration-200 cursor-pointer"
                                data-status="mendatang"
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

                                <!-- CHECKBOX -->
                                <label class="cancel-checkbox absolute right-4 top-4 cursor-pointer hidden" onclick="event.stopPropagation();">
                                    <input 
                                        type="checkbox"
                                        name="appointments[]"
                                        value="{{ $appointment['id'] }}"
                                        class="h-6 w-6 accent-red-500"
                                        onclick="event.stopPropagation();"
                                    >
                                </label>

                                <div class="mb-5 flex items-center gap-3">
                                    <i class="fa-solid fa-user-doctor text-2xl text-[#58A7F7] shrink-0"></i>

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

                <!-- Data Pasien Section -->
                <div class="mb-6">
                    <h4 class="text-xs font-bold text-gray-900 border-b border-gray-100 pb-2 mb-3">Data Pasien</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Nama Pasien</p>
                            <p id="modalPatientName" class="text-xs font-semibold text-gray-800">Naswa Gyna Sahira</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Gejala</p>
                            <p id="modalPatientSymptoms" class="text-xs text-gray-600 leading-relaxed">Demam, batuk kering, sakit kepala ringan...</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Riwayat Alergi</p>
                            <p id="modalPatientAllergy" class="text-xs text-gray-600 font-medium">Aspirin</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section (Dynamic content) -->
                
                <!-- Hasil Diagnosis Section -->
                <div id="modalDiagnosisSection" class="hidden">
                    <h4 class="text-xs font-bold text-gray-900 border-b border-gray-100 pb-2 mb-3">Hasil Diagnosis</h4>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-1.5">Pemeriksaan Fisik</p>
                            <ul id="modalPhysicalExamList" class="text-xs text-gray-600 list-disc list-inside space-y-1 pl-1 leading-relaxed">
                                <!-- Bullet points go here -->
                            </ul>
                            <p id="modalPhysicalExamText" class="text-xs text-gray-600 leading-relaxed hidden">-</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Diagnosis Sementara</p>
                            <p id="modalTempDiagnosis" class="text-xs text-gray-800 font-semibold leading-relaxed">Infeksi saluran pernafasan...</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Rencana Penanganan</p>
                            <p id="modalTreatmentPlan" class="text-xs text-gray-600 leading-relaxed">-</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Catatan Dokter</p>
                            <p id="modalDoctorNotes" class="text-xs text-gray-600 leading-relaxed">-</p>
                        </div>
                        <div id="modalPrescriptionDiv">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Resep Obat</p>
                            <p id="modalPrescription" class="text-xs text-gray-800 font-semibold leading-relaxed">-</p>
                        </div>
                    </div>
                </div>

                <!-- Alasan Pembatalan Section -->
                <div id="modalCancellationSection" class="hidden">
                    <h4 class="text-xs font-bold text-gray-900 border-b border-gray-100 pb-2 mb-3">Alasan Pembatalan</h4>
                    <p id="modalCancellationReason" class="text-xs text-gray-600 leading-relaxed">Salah memilih waktu jadwal</p>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter & Search Logic
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

                    // Update active tab
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    this.style.borderBottomColor = '#3B82F6';
                    this.style.color = '#111827';
                    filterTabs.forEach(t => {
                        if (t !== this) {
                            t.style.borderBottomColor = 'transparent';
                            t.style.color = '#4B5563';
                        }
                    });

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
