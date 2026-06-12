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
                                class="appointment-card cursor-pointer overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-[2px]"
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
                                data-patient-name="{{ $patient['name'] ?? auth()->user()->name ?? 'Pasien' }}"
                                dusk="history-card-{{ $appointment['id'] }}"
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

        <!-- Right Panel: Sidebar -->
        <aside class="sticky top-0 flex h-screen w-[390px] shrink-0 flex-col border-l border-gray-200 bg-white px-7 py-8 overflow-hidden">
            
            <!-- 1. Upcoming Bookings Panel (Default View) -->
            <div id="upcoming-bookings-panel" class="flex flex-col h-full flex-1 overflow-hidden">
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
                            <h3 class="mb-1 text-sm font-semibold text-gray-700">Tidak ada jadwal terdekat</h3>
                            <p class="text-xs text-gray-400">Semua jadwal temu mendatang akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Create Appointment Button -->
                <a
                    href="{{ route('pasien.booking.create') }}"
                    class="group relative mt-6 flex items-center justify-between overflow-hidden rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium shadow-md transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_10px_24px_rgba(96,165,250,0.45)]"
                >
                    <span class="relative z-10 text-white">
                        Buat Jadwal Temu
                    </span>

                    <i class="fa-solid fa-circle-plus relative z-10 text-white text-[18px]"></i>

                    <!-- glow -->
                    <span
                        class="pointer-events-none absolute left-[10%] top-[8%] h-[42%] w-[80%] rounded-full bg-white/20 blur-md">
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
                </a>
            </div>

            <!-- 2. History Detail Panel (Initially Hidden) -->
            <div id="history-detail-panel" class="hidden flex-col h-full flex-1 overflow-hidden" dusk="history-detail-panel">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <button id="closeHistoryDetailBtn" class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors" dusk="close-history-detail">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                    <span id="detailStatusBadge" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold">
                        <span id="detailStatusDot" class="h-1.5 w-1.5 rounded-full"></span>
                        <span id="detailStatusText">Selesai</span>
                    </span>
                </div>

                <div class="flex flex-col gap-6 overflow-y-auto flex-1 pr-2 pb-6">
                    <!-- Title -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Riwayat Jadwal Temu</h2>
                    </div>

                    <!-- Doctor Row -->
                    <div class="flex items-center gap-4">
                        <div class="relative h-16 w-16 overflow-hidden rounded-full border border-gray-100 bg-gray-50 flex items-center justify-center">
                            <img id="detailDoctorPhoto" src="" alt="Doctor Avatar" class="h-full w-full object-cover">
                        </div>
                        <div>
                            <h3 id="detailDoctorName" class="text-base font-semibold text-gray-900">-</h3>
                            <p id="detailDoctorSpecialization" class="text-xs text-gray-500">-</p>
                        </div>
                    </div>

                    <!-- Date & Time Row -->
                    <div class="grid grid-cols-2 gap-4 rounded-2xl bg-gray-50 p-4">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-1">
                                <i class="fa-regular fa-calendar mr-1"></i> Tanggal
                            </p>
                            <p id="detailAppointmentDate" class="text-xs font-semibold text-gray-800">-</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium mb-1">
                                <i class="fa-regular fa-clock mr-1"></i> Waktu
                            </p>
                            <p id="detailAppointmentTime" class="text-xs font-semibold text-gray-800">-</p>
                        </div>
                    </div>

                    <hr class="border-gray-100 my-1">

                    <!-- Data Pasien Section -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 tracking-wide">Data Pasien</h3>
                        
                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama Pasien</p>
                            <p id="detailPatientName" class="text-sm font-medium text-gray-900">-</p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Gejala / Keluhan Utama</p>
                            <p id="detailPatientSymptoms" class="text-sm text-gray-700 leading-relaxed">-</p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Riwayat Alergi</p>
                            <p id="detailPatientAllergy" class="text-sm text-gray-700 leading-relaxed">-</p>
                        </div>
                    </div>

                    <!-- Hasil Diagnosis Section (for Selesai status) -->
                    <div id="detailDiagnosisSection" class="space-y-4 hidden">
                        <hr class="border-gray-100 my-1">
                        <h3 class="text-sm font-bold text-gray-900 tracking-wide">Hasil Diagnosis</h3>

                        <div class="space-y-2">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pemeriksaan Fisik / Hasil Observasi</p>
                            <ul id="detailPhysicalExamList" class="list-disc pl-4 text-sm text-gray-700 space-y-1 hidden"></ul>
                            <p id="detailPhysicalExamText" class="text-sm text-gray-700 leading-relaxed">-</p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Diagnosis Sementara / Kesimpulan</p>
                            <p id="detailTempDiagnosis" class="text-sm text-gray-700 leading-relaxed">-</p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Rencana Penanganan / Rekomendasi</p>
                            <p id="detailTreatmentPlan" class="text-sm text-gray-700 leading-relaxed">-</p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Resep Obat</p>
                            <p id="detailPrescription" class="text-sm text-gray-700 leading-relaxed">-</p>
                        </div>
                    </div>

                    <!-- Alasan Pembatalan Section (for Dibatalkan status) -->
                    <div id="detailCancellationSection" class="space-y-4 hidden">
                        <hr class="border-gray-100 my-1">
                        <h3 class="text-sm font-bold text-gray-900 tracking-wide">Alasan Pembatalan</h3>
                        <p id="detailCancellationReason" class="text-sm text-gray-700 leading-relaxed">-</p>
                    </div>
                </div>
            </div>
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

            // Search filter logic
            const searchInput = document.getElementById('searchHistory');
            const appointmentCards = document.querySelectorAll('[data-history-grid] .appointment-card');

            const applySearch = () => {
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';

                appointmentCards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    const matchesSearch = searchTerm === '' || text.includes(searchTerm);

                    if (matchesSearch) {
                        card.style.display = 'block';
                        card.style.opacity = '1';
                    } else {
                        card.style.opacity = '0';
                        card.style.display = 'none';
                    }
                });
            };

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
            }

            // History Detail Setup
            const showHistoryDetail = (card) => {
                const status = card.getAttribute('data-status'); // e.g. selesai, dibatalkan
                const title = card.getAttribute('data-jenis');
                const doctor = card.getAttribute('data-dokter');
                const doctorFoto = card.getAttribute('data-dokter-foto');
                const date = card.getAttribute('data-tanggal');
                const time = card.getAttribute('data-jam');
                const symptoms = card.getAttribute('data-keluhan');
                const allergy = card.getAttribute('data-alergi');
                const patientName = card.getAttribute('data-patient-name') || "{{ auth()->user()->name ?? auth()->user()->fullname ?? 'Pasien' }}";
                
                const cancellationReason = card.getAttribute('data-alasan-pembatalan');
                const physicalExam = card.getAttribute('data-pemeriksaan-fisik');
                const tempDiagnosis = card.getAttribute('data-diagnosis-sementara');
                const treatmentPlan = card.getAttribute('data-rencana-penanganan');
                const prescription = card.getAttribute('data-resep-obat');

                // Set content
                document.getElementById('detailDoctorPhoto').src = doctorFoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor) + '&background=6aa4ef&color=fff&size=80';
                document.getElementById('detailDoctorName').textContent = doctor;
                document.getElementById('detailDoctorSpecialization').textContent = title;
                document.getElementById('detailAppointmentDate').textContent = date;
                document.getElementById('detailAppointmentTime').textContent = time;
                document.getElementById('detailPatientName').textContent = patientName;
                document.getElementById('detailPatientSymptoms').textContent = (symptoms && symptoms !== '-') ? symptoms : '-';
                document.getElementById('detailPatientAllergy').textContent = (allergy && allergy !== '-') ? allergy : '-';

                // Status Badge Styling
                const statusText = status.charAt(0).toUpperCase() + status.slice(1);
                document.getElementById('detailStatusText').textContent = statusText;

                const statusBadge = document.getElementById('detailStatusBadge');
                const statusDot = document.getElementById('detailStatusDot');
                
                statusBadge.className = 'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold';
                statusDot.className = 'h-1.5 w-1.5 rounded-full';
                
                if (status === 'selesai') {
                    statusBadge.classList.add('bg-[#E8F8F0]', 'text-[#0E7043]');
                    statusDot.classList.add('bg-[#0E7043]');
                    
                    document.getElementById('detailDiagnosisSection').classList.remove('hidden');
                    document.getElementById('detailCancellationSection').classList.add('hidden');

                    // Check if diagnosis details are completely empty
                    const isDiagEmpty = (!physicalExam || physicalExam === '-') && 
                                        (!tempDiagnosis || tempDiagnosis === '-') && 
                                        (!treatmentPlan || treatmentPlan === '-') && 
                                        (!prescription || prescription === '-');

                    const examList = document.getElementById('detailPhysicalExamList');
                    const examText = document.getElementById('detailPhysicalExamText');
                    
                    // Reset elements state
                    examText.classList.remove('italic', 'text-gray-400');
                    document.getElementById('detailTempDiagnosis').classList.remove('italic', 'text-gray-400');
                    document.getElementById('detailTreatmentPlan').classList.remove('italic', 'text-gray-400');
                    document.getElementById('detailPrescription').classList.remove('italic', 'text-gray-400');

                    if (isDiagEmpty) {
                        examList.classList.add('hidden');
                        examText.classList.remove('hidden');
                        examText.textContent = 'Belum ada hasil diagnosa.';
                        examText.classList.add('italic', 'text-gray-400');
                        
                        document.getElementById('detailTempDiagnosis').textContent = 'Belum ada hasil diagnosa.';
                        document.getElementById('detailTempDiagnosis').classList.add('italic', 'text-gray-400');
                        
                        document.getElementById('detailTreatmentPlan').textContent = 'Belum ada hasil diagnosa.';
                        document.getElementById('detailTreatmentPlan').classList.add('italic', 'text-gray-400');
                        
                        document.getElementById('detailPrescription').textContent = 'Belum ada hasil diagnosa.';
                        document.getElementById('detailPrescription').classList.add('italic', 'text-gray-400');
                    } else {
                        examList.innerHTML = '';
                        if (physicalExam && physicalExam !== '-' && physicalExam.trim() !== '') {
                            const lines = physicalExam.split(/[\r\n]+/)
                                .map(line => line.replace(/^[•\-\*\s]+/, '').trim())
                                .filter(line => line !== '');
                            
                            if (lines.length > 0) {
                                examText.classList.add('hidden');
                                examList.classList.remove('hidden');
                                lines.forEach(line => {
                                    const li = document.createElement('li');
                                    li.textContent = line;
                                    examList.appendChild(li);
                                });
                            } else {
                                examList.classList.add('hidden');
                                examText.classList.remove('hidden');
                                examText.textContent = '-';
                            }
                        } else {
                            examList.classList.add('hidden');
                            examText.classList.remove('hidden');
                            examText.textContent = '-';
                        }

                        document.getElementById('detailTempDiagnosis').textContent = tempDiagnosis || '-';
                        document.getElementById('detailTreatmentPlan').textContent = treatmentPlan || '-';
                        document.getElementById('detailPrescription').textContent = prescription || '-';
                    }

                } else if (status === 'dibatalkan') {
                    document.getElementById('detailDiagnosisSection').classList.add('hidden');
                    document.getElementById('detailCancellationSection').classList.remove('hidden');
                    document.getElementById('detailCancellationReason').textContent = (cancellationReason && cancellationReason !== '-') ? cancellationReason : '-';
                    
                    statusBadge.classList.add('bg-[#FEECEB]', 'text-[#BC2218]');
                    statusDot.classList.add('bg-[#BC2218]');
                } else {
                    statusBadge.classList.add('bg-[#FFF9E6]', 'text-[#B7791F]');
                    statusDot.classList.add('bg-[#B7791F]');
                    document.getElementById('detailDiagnosisSection').classList.add('hidden');
                    document.getElementById('detailCancellationSection').classList.add('hidden');
                }

                // Show Detail Panel, Hide Upcoming Panel
                document.getElementById('upcoming-bookings-panel').classList.add('hidden');
                document.getElementById('history-detail-panel').classList.remove('hidden');
                document.getElementById('history-detail-panel').classList.add('flex');
            };

            const hideHistoryDetail = () => {
                document.getElementById('history-detail-panel').classList.add('hidden');
                document.getElementById('history-detail-panel').classList.remove('flex');
                document.getElementById('upcoming-bookings-panel').classList.remove('hidden');
            };

            // Event Listeners for click on cards
            document.querySelectorAll('.appointment-card').forEach(card => {
                card.addEventListener('click', () => showHistoryDetail(card));
            });

            // Close history detail panel button
            const closeHistoryDetailBtn = document.getElementById('closeHistoryDetailBtn');
            if (closeHistoryDetailBtn) {
                closeHistoryDetailBtn.addEventListener('click', hideHistoryDetail);
            }
        });
    </script>
</body>
</html>
