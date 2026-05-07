@extends('layouts.dokter', ['active' => 'dashboard'])

@section('title', 'Dashboard - MediHub')

@push('head')
    @vite(['resources/css/dokter/dashboard.css', 'resources/js/dokter/jadwal.js'])
@endpush

@section('content')
    {{-- Header --}}
    <div class="mediq-header-row">
        <div class="mediq-user-chip">
            @if(!blank($dokter->profile_pict ?? null))
                <img src="{{ asset('storage/' . $dokter->profile_pict) }}"
                     alt="Avatar"
                     class="mediq-avatar" />
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($dokter->name ?? 'Dokter') }}&background=6aa4ef&color=fff&size=96"
                     alt="Avatar"
                     class="mediq-avatar" />
            @endif
            <div>
                <p class="mediq-user-name">
                    Halo, dr {{ $dokter->name ?? Auth::user()->name ?? 'Dokter' }}
                </p>
                <p class="doctor-greeting-subtitle">Bagaimana kabarmu?</p>
            </div>
        </div>
        <div class="mediq-header-actions">
            <div class="mediq-search-wrap">
                <input type="text" class="mediq-search-input" placeholder="Cari..." />
                <svg class="mediq-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
            </div>
            <button class="mediq-icon-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Stats + Donut Chart Row --}}
    <div class="dash-stats-row">
        {{-- Donut Card --}}
        <div class="dash-donut-card">
            <div class="dash-donut-wrap">
                <svg viewBox="0 0 120 120" class="dash-donut-svg">
                    <circle cx="60" cy="60" r="48" fill="none" stroke="#eef4ff" stroke-width="14"/>
                    <circle cx="60" cy="60" r="48" fill="none" stroke="#6aa4ef" stroke-width="14"
                        stroke-dasharray="188 113"
                        stroke-dashoffset="0"
                        stroke-linecap="round"
                        transform="rotate(-90 60 60)"/>
                    <circle cx="60" cy="60" r="48" fill="none" stroke="#c8dff9" stroke-width="14"
                        stroke-dasharray="62 239"
                        stroke-dashoffset="-188"
                        stroke-linecap="round"
                        transform="rotate(-90 60 60)"/>
                </svg>
                <div class="dash-donut-center">
                    <span class="dash-donut-num">{{ $jadwalHariIni }}</span>
                    <span class="dash-donut-label">Jadwal Temu<br>Hari Ini</span>
                </div>
            </div>
            <div class="dash-donut-legend">
                <div class="dash-legend-item">
                    <span class="dash-legend-dot" style="background:#6aa4ef"></span>
                    <span class="dash-legend-text">Selesai</span>
                    <span class="dash-legend-val">0/{{ $jadwalHariIni ?: 4 }}</span>
                </div>
                <div class="dash-legend-item">
                    <span class="dash-legend-dot" style="background:#c8dff9"></span>
                    <span class="dash-legend-text">Batal</span>
                    <span class="dash-legend-val">0/{{ $jadwalHariIni ?: 4 }}</span>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="doctor-stats-grid">
            <div class="doctor-stat-card-1">
                <p class="doctor-stat-value">{{ $jadwalHariIni }}</p>
                <p class="doctor-stat-label">Pasien Minggu Ini</p>
                <p class="doctor-stat-location">RS Medic Center - Bandung</p>
                <div class="doctor-stat-icon">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            </div>
            <div class="doctor-stat-card-2">
                <p class="doctor-stat-value">{{ count($jadwalSaya) }}</p>
                <p class="doctor-stat-label">Jadwal Tersedia</p>
                <p class="doctor-stat-location">RS Medic Center - Bandung</p>
                <div class="doctor-stat-icon">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </div>
            </div>
            <div class="doctor-stat-card-3">
                <p class="doctor-stat-value">{{ $totalPasien }}</p>
                <p class="doctor-stat-label">Pasien Aktif</p>
                <p class="doctor-stat-location">RS Medic Center - Bandung</p>
                <div class="doctor-stat-icon">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            </div>
            <div class="doctor-stat-card-4">
                <p class="doctor-stat-value">{{ $totalPasien }}</p>
                <p class="doctor-stat-label">Sesi Selesai Bulan Ini</p>
                <p class="doctor-stat-location">RS Medic Center - Bandung</p>
                <div class="doctor-stat-icon">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Jadwal Saya --}}
    {{-- Root wrapper dengan data attributes supaya JS jadwal.js bisa bekerja --}}
    <div
        class="doctor-schedule-root"
        data-doctor-schedule
        data-store-url="{{ route('dokter.jadwal.store') }}"
        data-base-url="/dokter/jadwal"
    >
        <div class="doctor-section-head">
            <h2 class="doctor-section-title">Jadwal Saya</h2>
            <button type="button" data-schedule-modal="create" class="mediq-primary-btn doctor-add-schedule-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Jadwal Baru
            </button>
        </div>

        @if(count($jadwalSaya) > 0)
            <div class="doctor-schedule-strip">
                {{-- Limit to 4 schedules in preview --}}
                @foreach(array_slice($jadwalSaya, 0, 4) as $jadwal)
                    {{-- Pakai class & data attributes sama persis dengan halaman jadwal --}}
                    <div class="doctor-schedule-card">
                        <p class="doctor-schedule-eyebrow">Jadwal Tersedia</p>
                        <p class="doctor-schedule-date">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p class="doctor-schedule-time">{{ $jadwal->jam_mulai ?? '-' }} - {{ $jadwal->jam_selesai ?? '-' }}</p>
                        <div class="doctor-schedule-actions">
                            <button
                                type="button"
                                data-schedule-delete="{{ $jadwal->id }}"
                                class="doctor-schedule-btn-icon doctor-schedule-btn-delete"
                            >
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                data-schedule-modal="edit"
                                data-schedule-id="{{ $jadwal->id }}"
                                data-tanggal="{{ $jadwal->tanggal }}"
                                data-jam-mulai="{{ $jadwal->jam_mulai }}"
                                data-jam-selesai="{{ $jadwal->jam_selesai }}"
                                class="doctor-edit-button"
                            >
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Lihat Selengkapnya --}}
            <div class="doctor-schedule-footer">
                <a href="{{ route('dokter.jadwal.index') }}" class="doctor-schedule-btn-more">
                    Lihat Selengkapnya
                </a>
            </div>
        @else
            <div class="doctor-schedule-empty">
                <p>Belum ada jadwal tersedia. Buat jadwal baru!</p>
            </div>
        @endif

        {{-- MODAL (sama persis dengan jadwal.blade.php) --}}
        <div id="modal-overlay" class="doctor-schedule-modal">
            <div class="doctor-schedule-dialog">
                <h2 id="modal-title" class="doctor-schedule-modal-title"></h2>

                <div class="doctor-schedule-form-fields">
                    <div>
                        <label class="mediq-label">Tanggal</label>
                        <input type="date" id="input-tanggal" class="mediq-input" />
                    </div>
                    <div>
                        <label class="mediq-label">Jam Mulai</label>
                        <input type="time" id="input-jam-mulai" class="mediq-input" />
                    </div>
                    <div>
                        <label class="mediq-label">Jam Berakhir</label>
                        <input type="time" id="input-jam-selesai" class="mediq-input" />
                    </div>
                </div>

                <div class="doctor-modal-actions">
                    <button type="button" data-schedule-close class="doctor-modal-cancel">
                        Batal
                    </button>
                    <button id="modal-submit" type="button" data-schedule-submit class="mediq-primary-btn doctor-modal-submit">
                        Simpan Jadwal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Pasien --}}
    <div>
        <h2 class="doctor-section-title">Daftar Pasien</h2>
        <div class="doctor-patient-table-wrap">
            <table class="doctor-patient-table">
                <thead>
                    <tr>
                        <th>Pasien</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td class="doctor-patient-name">{{ $appointment->patient_name ?? '-' }}</td>
                            <td class="doctor-patient-date">{{ $appointment->appointment_date ?? '-' }}</td>
                            <td>
                                <span class="doctor-status-pill">Aktif</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="doctor-table-empty">Belum ada pasien</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
            @foreach($items->take(3) as $appointment)
                <div class="mediq-appointment-card">
                    <div class="mediq-appointment-head">
                        <div class="mediq-app-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="mediq-appointment-doctor">{{ $appointment->patient_name ?? 'Pasien' }}</p>
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
                                {{ $appointment->appointment_date ?? '-' }}
                            </p>
                            @if(!blank($appointment->appointment_time_start ?? null))
                            <p class="mediq-muted">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                                </svg>
                                {{ $appointment->appointment_time_start }} - {{ $appointment->appointment_time_end ?? '' }}
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