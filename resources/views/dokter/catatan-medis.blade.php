@extends('layouts.dokter', ['active' => 'dashboard'])

@section('title', ($catatanMedis ? 'Catatan Medis' : 'Buat Catatan Medis') . ' - MediHub')

@push('head')
    @vite(['resources/css/dokter/catatan-medis.css', 'resources/css/dokter/dashboard.css'])
@endpush

@section('content')
<div class="cm-page">

    {{-- ═══════════════════════════════════════════════
         LEFT PANEL — Info Jadwal Temu & Data Pasien
    ═══════════════════════════════════════════════ --}}
    <aside class="cm-left-panel">
        {{-- Close / Back --}}
        <a href="{{ route('dokter.dashboard') }}" class="cm-left-close">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Kembali
        </a>

        {{-- Header --}}
        <div class="cm-left-header">
            <h2 class="cm-left-title">
                Jadwal Temu Mendatang
                @if($catatanMedis)
                    <span class="cm-badge cm-badge-done">
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Selesai
                    </span>
                @else
                    <span class="cm-badge cm-badge-active">● Mendatang</span>
                @endif
            </h2>
        </div>

        {{-- Antrian --}}
        <div class="cm-queue-section">
            <div>
                <p class="cm-queue-label">Antrian</p>
                <p class="cm-queue-number">{{ str_pad(1, 2, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div>
                <p class="cm-queue-meta">
                    {{ \Carbon\Carbon::parse($appointment->appointment_date ?? now())->translatedFormat('d F') }}
                </p>
                <p class="cm-queue-meta" style="color: #94a3b8; font-size: 12px;">
                    {{ $appointment->appointment_time_start ?? $appointment->appointment_time ?? '' }}
                    @if(!blank($appointment->appointment_time_end ?? null))
                        - {{ $appointment->appointment_time_end }} WIB
                    @endif
                </p>
            </div>
        </div>

        {{-- Data Pasien --}}
        <div class="cm-patient-info">
            <h3 class="cm-section-label">Data Pasien</h3>

            <div class="cm-info-row">
                <span class="cm-info-label">Nama Pasien</span>
                <span class="cm-info-value">{{ $appointment->patient_name ?? 'Tidak tersedia' }}</span>
            </div>

            <div class="cm-info-row">
                <span class="cm-info-label">Keluhan Utama</span>
                <span class="cm-info-value" style="font-style: italic; color: #475569;">
                    {{ $appointment->complaint ?? 'Tidak ada keluhan tercatat' }}
                </span>
            </div>
        </div>

        {{-- Catatan Medis Summary --}}
        <div class="cm-left-catatan">
            <h3 class="cm-section-label">Catatan Medis</h3>

            @php
                $fields = [
                    'Keluhan Utama' => $catatanMedis->keluhan_utama ?? null,
                    'Hasil Observasi' => $catatanMedis->hasil_observasi ?? null,
                    'Hasil Asesmen Psikologis' => $catatanMedis->hasil_asesmen ?? null,
                    'Kesimpulan' => $catatanMedis->kesimpulan ?? null,
                    'Rekomendasi' => $catatanMedis->rekomendasi ?? null,
                ];
            @endphp

            @foreach($fields as $label => $value)
                <div class="cm-catatan-field">
                    <p class="cm-catatan-field-label">{{ $label }}</p>
                    <div class="cm-catatan-field-sep"></div>
                    @if(!blank($value))
                        <p class="cm-catatan-field-value">{{ Str::limit($value, 80) }}</p>
                    @else
                        <p class="cm-catatan-field-empty">—</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Doctor Info --}}
        <div class="cm-doctor-avatar-row">
            @if(!blank($dokter->profile_pict ?? null))
                <img src="{{ asset('storage/' . $dokter->profile_pict) }}" alt="Dr" class="cm-doctor-avatar">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($dokter->name ?? 'Dr') }}&background=6aa4ef&color=fff&size=80" alt="Dr" class="cm-doctor-avatar">
            @endif
            <div>
                <p class="cm-doctor-name">dr {{ $dokter->name ?? 'Dokter' }}</p>
                <p class="cm-doctor-specialty">{{ $dokter->specialty ?? 'Dokter Umum' }}</p>
            </div>
        </div>

        @if(!$catatanMedis)
            <button type="submit" form="catatan-medis-form" class="cm-btn-create">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Catatan Medis
            </button>
        @endif
    </aside>

    {{-- ═══════════════════════════════════════════════
         MAIN CONTENT — Form / Display Catatan Medis
    ═══════════════════════════════════════════════ --}}
    <div class="cm-main">
        {{-- Logo --}}
        <div class="cm-main-logo">
            <span class="cm-main-logo-text">MediHub</span>
            <svg class="cm-main-logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="cm-alert cm-alert-success">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="cm-alert cm-alert-error">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="cm-alert cm-alert-error">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Queue --}}
        <div class="cm-main-queue">
            <span class="cm-main-queue-label">Antrian</span>
        </div>
        <p class="cm-main-queue-number">{{ str_pad(1, 2, '0', STR_PAD_LEFT) }}</p>

        @if($catatanMedis)
            {{-- ══════════ VIEW MODE ══════════ --}}

            {{-- Data Pasien --}}
            <div class="cm-form-section">
                <h2 class="cm-form-section-title">
                    <span class="cm-form-section-title-icon blue">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    Data Pasien
                </h2>

                <div class="cm-patient-card">
                    <svg width="20" height="20" fill="none" stroke="#58a7f5" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <div>
                        <p class="cm-patient-card-name">{{ $appointment->patient_name ?? '-' }}</p>
                        <p class="cm-patient-card-meta">Pasien</p>
                    </div>
                </div>
            </div>

            {{-- Keluhan Utama --}}
            <div class="cm-form-section">
                <h2 class="cm-form-section-title">
                    <span class="cm-form-section-title-icon purple">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </span>
                    Keluhan Utama
                </h2>
                <div class="cm-display-value">{{ $catatanMedis->keluhan_utama ?? '-' }}</div>
            </div>

            {{-- Diagnosa Pasien --}}
            <div class="cm-form-section">
                <h2 class="cm-form-section-title">
                    <span class="cm-form-section-title-icon green">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    Diagnosa Pasien
                </h2>

                <div class="cm-form-group">
                    <label class="cm-form-label">Hasil Observasi</label>
                    @if(!blank($catatanMedis->hasil_observasi ?? ''))
                        <div class="cm-display-value">{{ $catatanMedis->hasil_observasi }}</div>
                    @else
                        <div class="cm-display-empty">Tidak ada data</div>
                    @endif
                </div>

                <div class="cm-form-group">
                    <label class="cm-form-label">Hasil Asesmen Psikologis</label>
                    @if(!blank($catatanMedis->hasil_asesmen ?? ''))
                        <div class="cm-display-value">{{ $catatanMedis->hasil_asesmen }}</div>
                    @else
                        <div class="cm-display-empty">Tidak ada data</div>
                    @endif
                </div>

                <div class="cm-form-group">
                    <label class="cm-form-label">Kesimpulan</label>
                    @if(!blank($catatanMedis->kesimpulan ?? ''))
                        <div class="cm-display-value">{{ $catatanMedis->kesimpulan }}</div>
                    @else
                        <div class="cm-display-empty">Tidak ada data</div>
                    @endif
                </div>

                <div class="cm-form-group">
                    <label class="cm-form-label">Rekomendasi</label>
                    @if(!blank($catatanMedis->rekomendasi ?? ''))
                        <div class="cm-display-value">{{ $catatanMedis->rekomendasi }}</div>
                    @else
                        <div class="cm-display-empty">Tidak ada data</div>
                    @endif
                </div>
            </div>

            {{-- Resep Obat --}}
            <div class="cm-form-section">
                <h2 class="cm-form-section-title">
                    <span class="cm-form-section-title-icon amber">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    Resep Obat
                </h2>
                @if(!blank($catatanMedis->resep_obat ?? ''))
                    <div class="cm-display-value">{{ $catatanMedis->resep_obat }}</div>
                @else
                    <div class="cm-display-empty">Tidak ada resep obat</div>
                @endif
            </div>

            {{-- Back --}}
            <a href="{{ route('dokter.dashboard') }}" class="cm-back-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>

        @else
            {{-- ══════════ CREATE MODE ══════════ --}}
            <form id="catatan-medis-form" action="{{ route('dokter.catatan_medis.store') }}" method="POST">
                @csrf
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                <input type="hidden" name="patient_id" value="{{ $appointment->patient_id ?? $appointment->user_uid ?? '' }}">
                <input type="hidden" name="patient_name" value="{{ $appointment->patient_name ?? '' }}">

                {{-- Data Pasien --}}
                <div class="cm-form-section">
                    <h2 class="cm-form-section-title">
                        <span class="cm-form-section-title-icon blue">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        Data Pasien
                    </h2>

                    <div class="cm-form-group">
                        <label class="cm-form-label">Nama Pasien</label>
                        <div class="cm-patient-card">
                            <svg width="20" height="20" fill="none" stroke="#58a7f5" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <div>
                                <p class="cm-patient-card-name">{{ $appointment->patient_name ?? '-' }}</p>
                                <p class="cm-patient-card-meta">Pasien</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Keluhan Utama --}}
                <div class="cm-form-section">
                    <h2 class="cm-form-section-title">
                        <span class="cm-form-section-title-icon purple">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </span>
                        Keluhan Utama
                    </h2>

                    <div class="cm-form-group">
                        <textarea
                            name="keluhan_utama"
                            id="keluhan_utama"
                            class="cm-form-textarea"
                            placeholder="Tuliskan keluhan utama pasien..."
                            required
                        >{{ old('keluhan_utama', $appointment->complaint ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Diagnosa Pasien --}}
                <div class="cm-form-section">
                    <h2 class="cm-form-section-title">
                        <span class="cm-form-section-title-icon green">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        Diagnosa Pasien
                    </h2>

                    <div class="cm-form-group">
                        <label class="cm-form-label" for="hasil_observasi">Hasil Observasi</label>
                        <textarea
                            name="hasil_observasi"
                            id="hasil_observasi"
                            class="cm-form-textarea tall"
                            placeholder="Pasien tampak kooperatif selama pemeriksaan. Kontak mata cukup baik..."
                        >{{ old('hasil_observasi') }}</textarea>
                    </div>

                    <div class="cm-form-group">
                        <label class="cm-form-label" for="hasil_asesmen">Hasil Asesmen Psikologis</label>
                        <textarea
                            name="hasil_asesmen"
                            id="hasil_asesmen"
                            class="cm-form-textarea"
                            placeholder="1. Tingkat Kecemasan: Sedang&#10;2. Tingkat Stres: Sedang&#10;3. Mood: Cemas terkait rasa sakit..."
                        >{{ old('hasil_asesmen') }}</textarea>
                    </div>

                    <div class="cm-form-group">
                        <label class="cm-form-label" for="kesimpulan">Kesimpulan</label>
                        <textarea
                            name="kesimpulan"
                            id="kesimpulan"
                            class="cm-form-textarea tall"
                            placeholder="Berdasarkan keluhan utama pasien tentang... Disertai hasil observasi dan asesmen..."
                        >{{ old('kesimpulan') }}</textarea>
                    </div>

                    <div class="cm-form-group">
                        <label class="cm-form-label" for="rekomendasi">Rekomendasi</label>
                        <textarea
                            name="rekomendasi"
                            id="rekomendasi"
                            class="cm-form-textarea"
                            placeholder="1. Segera lakukan perawatan...&#10;2. Gunakan obat pereda nyeri sesuai anjuran dokter...&#10;3. Jaga kebersihan mulut..."
                        >{{ old('rekomendasi') }}</textarea>
                    </div>
                </div>

                {{-- Resep Obat --}}
                <div class="cm-form-section">
                    <h2 class="cm-form-section-title">
                        <span class="cm-form-section-title-icon amber">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Resep Obat
                    </h2>

                    <div class="cm-form-group">
                        <textarea
                            name="resep_obat"
                            id="resep_obat"
                            class="cm-form-textarea tall"
                            placeholder="• Obat pereda nyeri dan antibiotik untuk mencegah infeksi.&#10;• Jaga kebersihan mulut dengan menyikat gigi dua kali sehari..."
                        >{{ old('resep_obat') }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="cm-submit-btn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Catatan Medis
                </button>

                <a href="{{ route('dokter.dashboard') }}" class="cm-back-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Batal
                </a>
            </form>
        @endif
    </div>
</div>
@endsection

@section('rightbar')
    <div class="mediq-right-head">
        <h3 class="doctor-rightbar-title">Jadwal Temu Mendatang</h3>
    </div>

    @php
        $grouped = collect($appointments)->groupBy(fn($a) => \Carbon\Carbon::parse($a->appointment_date ?? now())->toDateString());
    @endphp

    @forelse($grouped->take(3) as $date => $items)
        <div class="dash-rightbar-date-group">
            <p class="dash-rightbar-date-label">
                {{ \Carbon\Carbon::parse($date)->isToday() ? 'Hari ini' : \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
            </p>
            @foreach($items->take(3) as $apt)
                @php
                    $statusKey = strtolower(trim($apt->status ?? 'menunggu'));
                    $statusKey = match ($statusKey) {
                        'batal', 'dibatalkan', 'cancelled', 'canceled' => 'dibatalkan',
                        'selesai', 'done', 'completed' => 'selesai',
                        'diperiksa', 'sedang diperiksa', 'in progress' => 'diperiksa',
                        default => 'menunggu',
                    };
                    $statusLabel = match ($statusKey) {
                        'dibatalkan' => 'Dibatalkan',
                        'selesai' => 'Selesai',
                        'diperiksa' => 'Diperiksa',
                        default => 'Menunggu',
                    };
                    $isCurrentAppointment = ($apt->id ?? '') === ($appointment->id ?? '');
                @endphp
                <div class="mediq-appointment-card" style="{{ $isCurrentAppointment ? 'border: 2px solid #58a7f5; background: #f0f7ff;' : '' }}">
                    <div class="mediq-appointment-head">
                        <div class="mediq-app-icon" style="background: {{ $isCurrentAppointment ? '#58a7f5' : '#e8eaef' }}; color: {{ $isCurrentAppointment ? '#fff' : '#475569' }};">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span class="doctor-status-pill doctor-status-{{ $statusKey }}" style="font-size: 10px; padding: 2px 8px; width: fit-content;">
                            {{ $apt->patient_name ?? 'Pasien' }} - {{ $statusLabel }}
                        </span>
                        <p class="mediq-appointment-doctor" style="margin: 2px 0 0; font-size: 13px;">{{ $apt->patient_name ?? 'Pasien' }}</p>
                    </div>
                    <div class="mediq-appointment-body">
                        <div class="mediq-app-queue">
                            <p class="mediq-muted">Antrian</p>
                            <p class="mediq-queue-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="mediq-app-datetime">
                            <p class="mediq-muted">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                                </svg>
                                {{ $apt->appointment_date ?? '-' }}
                            </p>
                            @if(!blank($apt->appointment_time_start ?? null))
                                <p class="mediq-muted">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                                    </svg>
                                    {{ $apt->appointment_time_start }} - {{ $apt->appointment_time_end ?? '' }} WIB
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="doctor-rightbar-empty">Tidak ada jadwal mendatang</p>
    @endforelse
@endsection
