<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosa & Catatan Medis - MediHub</title>
    <meta name="description" content="Lihat hasil diagnosa dan catatan medis Anda di MediHub">

    @vite(['resources/css/app.css', 'resources/css/pasien/diagnosa.css', 'resources/js/pasien/diagnosa.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <div class="grid h-screen grid-cols-[220px_1fr_390px] overflow-hidden">
        <x-pasien.sidebar active="diagnosa" />

        {{-- ═══════════════════════════════════════════════
             MAIN CONTENT
        ═══════════════════════════════════════════════ --}}
        <main class="h-screen overflow-y-auto bg-[#fbfbfb] px-8 py-8">

            {{-- Header --}}
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
                            Hasil diagnosa & catatan medis kamu
                        </p>
                    </div>
                </a>

                <div class="flex items-center gap-3 no-print">
                    <div class="flex w-[330px] items-center rounded-xl border border-gray-200 bg-white px-4 py-3">
                        <input
                            id="searchDiagnosa"
                            type="text"
                            placeholder="Cari diagnosa, dokter, catatan..."
                            class="w-full bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>

                    <button
                        id="printDiagnosa"
                        class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:bg-blue-50 hover:text-blue-500 hover:border-blue-200"
                        title="Cetak"
                    >
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-xl bg-green-50 px-5 py-4 border border-green-200">
                    <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                    <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-green-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            {{-- ── Stat Cards ── --}}
            <section class="mb-8 grid grid-cols-3 gap-5">
                {{-- Total Diagnosa --}}
                <div class="stat-card rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100/50 p-5 shadow-sm border border-blue-100">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="stat-icon flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500 text-white shadow-md shadow-blue-200">
                            <i class="fa-solid fa-stethoscope text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-blue-400 bg-blue-50 px-2.5 py-1 rounded-full">Total</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_diagnosa'] }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Hasil Diagnosa</p>
                </div>

                {{-- Kunjungan Selesai --}}
                <div class="stat-card rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-5 shadow-sm border border-emerald-100">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="stat-icon flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-200">
                            <i class="fa-solid fa-calendar-check text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-emerald-400 bg-emerald-50 px-2.5 py-1 rounded-full">Selesai</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['kunjungan_selesai'] }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Kunjungan Selesai</p>
                </div>

                {{-- Resep Obat --}}
                <div class="stat-card rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100/50 p-5 shadow-sm border border-amber-100">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="stat-icon flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500 text-white shadow-md shadow-amber-200">
                            <i class="fa-solid fa-prescription-bottle-medical text-lg"></i>
                        </div>
                        <span class="text-xs font-medium text-amber-400 bg-amber-50 px-2.5 py-1 rounded-full">Resep</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_resep'] }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Resep Obat</p>
                </div>
            </section>

            {{-- ── Section Title & Filter ── --}}
            <section class="mb-8">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold">Hasil Diagnosa & Catatan Medis</h2>
                </div>

                {{-- Filter Tabs --}}
                <div class="mb-6 flex gap-4 border-b border-gray-200 pb-4 no-print">
                    <button
                        data-filter="semua"
                        class="filter-tab-diagnosa px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:text-gray-900 active"
                    >
                        Semua
                    </button>
                    <button
                        data-filter="bulan-ini"
                        class="filter-tab-diagnosa px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:text-gray-900"
                    >
                        Bulan Ini
                    </button>
                    <button
                        data-filter="3-bulan"
                        class="filter-tab-diagnosa px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:text-gray-900"
                    >
                        3 Bulan Terakhir
                    </button>
                    <button
                        data-filter="6-bulan"
                        class="filter-tab-diagnosa px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:text-gray-900"
                    >
                        6 Bulan Terakhir
                    </button>
                </div>

                {{-- Diagnosis Cards --}}
                @if (count($diagnosisList) > 0)
                    <div class="space-y-4" data-diagnosis-grid>
                        @foreach ($diagnosisList as $index => $item)
                            @php
                                $hasDiagnosa = !empty($item['diagnosa']);
                                $hasCatatan  = !empty($item['catatan_medis']);
                                $hasResep    = !empty($item['resep_obat']);
                            @endphp
                            <div
                                class="diagnosis-card fade-in-up overflow-hidden rounded-2xl bg-white shadow-sm"
                                data-period="{{ $item['period_key'] ?? 'semua' }}"
                                data-dokter="{{ $item['dokter'] }}"
                                data-spesialis="{{ $item['jenis'] }}"
                                data-tanggal="{{ $item['tanggal'] }}"
                                data-jam="{{ $item['jam'] }}"
                                data-keluhan="{{ $item['keluhan'] ?? '' }}"
                                data-diagnosa="{{ $item['diagnosa'] ?? '' }}"
                                data-catatan="{{ $item['catatan_medis'] ?? '' }}"
                                data-resep="{{ $item['resep_obat'] ?? '' }}"
                                data-rs="{{ $item['rs'] ?? 'RS Medic Center' }}"
                            >
                                {{-- Card Header --}}
                                <div class="flex items-start justify-between p-5 pb-4">
                                    <div class="flex items-start gap-4">
                                        {{-- Doctor icon --}}
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                                            {{ $hasDiagnosa ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400' }}">
                                            <i class="fa-solid fa-user-doctor text-xl"></i>
                                        </div>

                                        <div>
                                            <h3 class="text-[15px] font-semibold text-gray-900 leading-snug">
                                                {{ $item['dokter'] }}
                                            </h3>
                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $item['jenis'] }}
                                                <span class="sep-dot"></span>
                                                {{ $item['rs'] }}
                                            </p>
                                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                                                <span>
                                                    <i class="fa-regular fa-calendar mr-1"></i>
                                                    {{ $item['tanggal'] }}
                                                </span>
                                                <span>
                                                    <i class="fa-regular fa-clock mr-1"></i>
                                                    {{ $item['jam'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        {{-- Tags --}}
                                        <div class="flex items-center gap-1.5">
                                            @if ($hasDiagnosa)
                                                <span class="tag-badge bg-blue-50 text-blue-600">
                                                    <i class="fa-solid fa-circle-check text-[9px]"></i>
                                                    Diagnosa
                                                </span>
                                            @endif
                                            @if ($hasResep)
                                                <span class="tag-badge bg-amber-50 text-amber-600">
                                                    <i class="fa-solid fa-pills text-[9px]"></i>
                                                    Resep
                                                </span>
                                            @endif
                                            @if ($hasCatatan)
                                                <span class="tag-badge bg-emerald-50 text-emerald-600">
                                                    <i class="fa-solid fa-notes-medical text-[9px]"></i>
                                                    Catatan
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Expand toggle --}}
                                        <button
                                            data-toggle-detail
                                            class="ml-2 flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all duration-200"
                                            title="Lihat detail"
                                        >
                                            <i class="fa-solid fa-chevron-down expand-icon text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Quick preview (always visible) --}}
                                @if ($hasDiagnosa)
                                    <div class="mx-5 mb-4 rounded-xl bg-gradient-to-r from-blue-50/80 to-indigo-50/40 px-4 py-3 border border-blue-100/60">
                                        <p class="text-xs font-medium text-blue-500 mb-1">
                                            <i class="fa-solid fa-stethoscope mr-1"></i> Hasil Diagnosa
                                        </p>
                                        <p class="text-sm leading-relaxed text-gray-800 line-clamp-2">{{ $item['diagnosa'] }}</p>
                                    </div>
                                @else
                                    <div class="mx-5 mb-4 rounded-xl bg-gray-50 px-4 py-3 border border-gray-100">
                                        <p class="text-xs text-gray-400 italic">
                                            <i class="fa-solid fa-circle-info mr-1"></i>
                                            Belum ada hasil diagnosa untuk kunjungan ini.
                                        </p>
                                    </div>
                                @endif

                                {{-- Expandable detail --}}
                                <div class="diagnosis-detail">
                                    <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-5 space-y-4">
                                        {{-- Keluhan --}}
                                        @if ($item['keluhan'] ?? null)
                                            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                                                <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                                    <i class="fa-solid fa-comment-medical text-rose-400"></i>
                                                    Keluhan Pasien
                                                </p>
                                                <p class="text-sm leading-relaxed text-gray-800">{{ $item['keluhan'] }}</p>
                                            </div>
                                        @endif

                                        {{-- Diagnosa (full) --}}
                                        @if ($hasDiagnosa)
                                            <div class="rounded-xl bg-white p-4 shadow-sm border border-blue-100">
                                                <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-blue-500 uppercase tracking-wide">
                                                    <i class="fa-solid fa-stethoscope text-blue-400"></i>
                                                    Hasil Diagnosa Lengkap
                                                </p>
                                                <p class="text-sm leading-relaxed text-gray-800">{{ $item['diagnosa'] }}</p>
                                            </div>
                                        @endif

                                        {{-- Catatan Medis --}}
                                        @if ($hasCatatan)
                                            <div class="rounded-xl bg-white p-4 shadow-sm border border-emerald-100">
                                                <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-emerald-500 uppercase tracking-wide">
                                                    <i class="fa-solid fa-notes-medical text-emerald-400"></i>
                                                    Catatan Medis
                                                </p>
                                                <p class="text-sm leading-relaxed text-gray-800">{{ $item['catatan_medis'] }}</p>
                                            </div>
                                        @endif

                                        {{-- Resep Obat --}}
                                        @if ($hasResep)
                                            <div class="rounded-xl bg-white p-4 shadow-sm border border-amber-100">
                                                <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-amber-500 uppercase tracking-wide">
                                                    <i class="fa-solid fa-prescription-bottle-medical text-amber-400"></i>
                                                    Resep Obat
                                                </p>
                                                <p class="text-sm leading-relaxed text-gray-800">{{ $item['resep_obat'] }}</p>
                                            </div>
                                        @endif

                                        {{-- Action button --}}
                                        <div class="flex justify-end pt-1">
                                            <button
                                                data-open-modal
                                                class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2.5 text-xs font-medium text-white shadow-sm transition-all duration-200 hover:bg-blue-600 hover:shadow-md hover:-translate-y-[1px]"
                                            >
                                                <i class="fa-solid fa-expand"></i>
                                                Lihat Detail Lengkap
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Empty filter state (hidden by default) --}}
                    <div id="emptyFilterState" class="hidden flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-16 text-center mt-4">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <i class="fa-solid fa-filter-circle-xmark text-2xl"></i>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-gray-700">Tidak ada hasil</h3>
                        <p class="text-sm text-gray-400">Coba ubah filter atau kata kunci pencarian.</p>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-20 text-center">
                        <div class="float-anim mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <i class="fa-solid fa-file-medical text-4xl"></i>
                        </div>

                        <h3 class="mb-2 text-xl font-semibold text-gray-700">
                            Belum Ada Catatan Medis
                        </h3>

                        <p class="mb-6 max-w-sm text-sm leading-relaxed text-gray-400">
                            Hasil diagnosa dan catatan medis akan muncul di sini setelah kunjungan kamu selesai.
                        </p>

                        <a
                            href="{{ route('pasien.booking.create') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-5 py-3 text-sm font-medium text-white shadow-md transition-all duration-300 hover:bg-blue-600 hover:-translate-y-[2px] hover:shadow-lg"
                        >
                            <i class="fa-solid fa-calendar-plus"></i>
                            Buat Jadwal Temu
                        </a>
                    </div>
                @endif
            </section>
        </main>

        {{-- ═══════════════════════════════════════════════
             RIGHT PANEL — Ringkasan Medis
        ═══════════════════════════════════════════════ --}}
        <aside class="sticky top-0 flex h-screen flex-col border-l border-gray-200 bg-white px-6 py-8 overflow-y-auto">

            {{-- Panel Header --}}
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Medis</h2>
                <p class="mt-1 text-xs text-gray-400">Riwayat kunjungan terakhir</p>
            </div>

            {{-- Health Summary Card --}}
            <div class="mb-6 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-lg shadow-blue-200/40">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                        <i class="fa-solid fa-heart-pulse text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-blue-100">Status Rekam Medis</p>
                        <p class="text-sm font-semibold">
                            {{ $stats['total_diagnosa'] > 0 ? 'Aktif' : 'Belum Ada Data' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm px-3 py-2.5">
                        <p class="text-[22px] font-bold">{{ $stats['total_diagnosa'] }}</p>
                        <p class="text-[10px] text-blue-100">Total Diagnosa</p>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm px-3 py-2.5">
                        <p class="text-[22px] font-bold">{{ $stats['total_resep'] }}</p>
                        <p class="text-[10px] text-blue-100">Resep Obat</p>
                    </div>
                </div>
            </div>

            {{-- Recent Timeline --}}
            <div class="mb-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Kunjungan Terakhir</h3>

                @if (count($recentDiagnosis) > 0)
                    <div class="space-y-4">
                        @foreach ($recentDiagnosis as $recent)
                            <div class="timeline-item">
                                <div class="rounded-xl bg-gray-50 p-3.5 border border-gray-100 transition-all duration-200 hover:shadow-sm hover:border-blue-100">
                                    <p class="text-xs font-semibold text-gray-800 leading-snug">
                                        {{ $recent['dokter'] }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-gray-400">{{ $recent['jenis'] }}</p>
                                    <div class="mt-2 flex items-center gap-2 text-[11px] text-gray-400">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>{{ $recent['tanggal'] }}</span>
                                    </div>
                                    @if ($recent['diagnosa'] ?? null)
                                        <p class="mt-2 text-[11px] leading-relaxed text-gray-500 line-clamp-2 border-t border-gray-100 pt-2">
                                            <i class="fa-solid fa-quote-left text-[8px] text-blue-300 mr-1"></i>
                                            {{ Str::limit($recent['diagnosa'], 80) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-10 text-center">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <i class="fa-solid fa-clipboard-list text-lg"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-500">Belum ada kunjungan</p>
                        <p class="mt-1 text-[11px] text-gray-400">Data akan muncul setelah kunjungan pertama.</p>
                    </div>
                @endif
            </div>

            {{-- Tips Kesehatan --}}
            <div class="mt-auto rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 p-5 border border-emerald-100">
                <div class="mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-emerald-500"></i>
                    <p class="text-xs font-semibold text-emerald-700">Tips Kesehatan</p>
                </div>
                <p class="text-xs leading-relaxed text-emerald-600">
                    Simpan selalu catatan medis Anda. Informasi ini penting untuk konsultasi lanjutan
                    dan membantu dokter memberikan penanganan yang lebih baik.
                </p>
            </div>

            {{-- CTA --}}
            <a href="{{ route('pasien.booking.create') }}"
               class="group relative mt-5 flex items-center justify-between overflow-hidden rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium shadow-md transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_10px_24px_rgba(96,165,250,0.45)]">
                <span class="relative z-10 text-white">Buat Jadwal Temu</span>
                <i class="fa-solid fa-circle-plus relative z-10 text-white text-[18px]"></i>
                <span class="pointer-events-none absolute left-[10%] top-[8%] h-[42%] w-[80%] rounded-full bg-white/20 blur-md"></span>
                <span class="pointer-events-none absolute left-[-120%] top-[-40%] h-[220%] w-[35%] rotate-[22deg] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.32),transparent)] blur-md transition-all duration-1000 group-hover:left-[160%]"></span>
            </a>
        </aside>
    </div>

    {{-- ═══════════════════════════════════════════════
         DETAIL MODAL
    ═══════════════════════════════════════════════ --}}
    <div id="diagnosisModalOverlay" class="modal-overlay"></div>
    <div id="diagnosisModal" class="modal-diagnosa">
        <div class="p-7">
            {{-- Modal Header --}}
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Catatan Medis</h2>
                    <p class="mt-1 text-sm text-gray-400">Informasi lengkap hasil kunjungan</p>
                </div>
                <button
                    id="closeModalDiagnosa"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Doctor Info --}}
            <div class="mb-6 flex items-center gap-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border border-blue-100">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-md shadow-blue-200">
                    <i class="fa-solid fa-user-doctor text-2xl"></i>
                </div>
                <div>
                    <h3 id="modalDokter" class="text-base font-semibold text-gray-900">-</h3>
                    <p id="modalSpesialis" class="text-xs text-gray-500">-</p>
                    <p id="modalRS" class="mt-0.5 text-[11px] text-gray-400">
                        <i class="fa-solid fa-hospital mr-1"></i> -
                    </p>
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="mb-6 grid grid-cols-2 gap-4">
                <div class="rounded-xl bg-gray-50 p-3.5 border border-gray-100">
                    <p class="mb-1 text-[11px] font-medium text-gray-400 uppercase tracking-wide">
                        <i class="fa-regular fa-calendar mr-1"></i> Tanggal
                    </p>
                    <p id="modalTanggal" class="text-sm font-semibold text-gray-800">-</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3.5 border border-gray-100">
                    <p class="mb-1 text-[11px] font-medium text-gray-400 uppercase tracking-wide">
                        <i class="fa-regular fa-clock mr-1"></i> Waktu
                    </p>
                    <p id="modalJam" class="text-sm font-semibold text-gray-800">-</p>
                </div>
            </div>

            {{-- Medical Details --}}
            <div class="space-y-4">
                {{-- Keluhan --}}
                <div class="rounded-xl border border-gray-100 p-4">
                    <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-rose-500 uppercase tracking-wide">
                        <i class="fa-solid fa-comment-medical text-rose-400"></i>
                        Keluhan Pasien
                    </p>
                    <p id="modalKeluhan" class="text-sm leading-relaxed text-gray-700">-</p>
                </div>

                {{-- Diagnosa --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50/30 p-4">
                    <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-blue-600 uppercase tracking-wide">
                        <i class="fa-solid fa-stethoscope text-blue-500"></i>
                        Hasil Diagnosa
                    </p>
                    <p id="modalDiagnosa" class="text-sm leading-relaxed text-gray-700">-</p>
                </div>

                {{-- Catatan Medis --}}
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/30 p-4">
                    <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-emerald-600 uppercase tracking-wide">
                        <i class="fa-solid fa-notes-medical text-emerald-500"></i>
                        Catatan Medis
                    </p>
                    <p id="modalCatatan" class="text-sm leading-relaxed text-gray-700">-</p>
                </div>

                {{-- Resep Obat --}}
                <div class="rounded-xl border border-amber-100 bg-amber-50/30 p-4">
                    <p class="mb-2 flex items-center gap-2 text-xs font-semibold text-amber-600 uppercase tracking-wide">
                        <i class="fa-solid fa-prescription-bottle-medical text-amber-500"></i>
                        Resep Obat
                    </p>
                    <p id="modalResep" class="text-sm leading-relaxed text-gray-700">-</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
