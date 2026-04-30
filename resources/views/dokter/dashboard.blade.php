@extends('layouts.dokter', ['active' => 'dashboard'])

@section('title', 'Dashboard - MediHub')

@push('head')
    @vite('resources/css/dokter/dashboard.css')
@endpush

@section('content')
    {{-- Header --}}
    <div class="mediq-header-row">
        <div class="mediq-user-chip">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($dokter->name ?? 'Dokter') }}&background=6aa4ef&color=fff&size=96"
                 alt="Avatar" class="mediq-avatar" />
            <div>
                <p class="mediq-user-name">Halo, dr {{ $dokter->name ?? Auth::user()->name ?? 'Dokter' }}</p>
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

    {{-- Stats Cards --}}
    <div class="doctor-stats-grid">
        <div class="doctor-stat-card">
            <p class="doctor-stat-value">{{ $jadwalHariIni }}</p>
            <p class="doctor-stat-label">Pasien Minggu Ini</p>
            <p class="doctor-stat-location">RS Medic Center - Bandung</p>
            <div class="doctor-stat-icon">
                <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
        </div>
        <div class="doctor-stat-card">
            <p class="doctor-stat-value">{{ count($jadwalSaya) }}</p>
            <p class="doctor-stat-label">Jadwal Tersedia</p>
            <p class="doctor-stat-location">RS Medic Center - Bandung</p>
            <div class="doctor-stat-icon">
                <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
        </div>
        <div class="doctor-stat-card">
            <p class="doctor-stat-value">{{ $totalPasien }}</p>
            <p class="doctor-stat-label">Pasien Aktif</p>
            <p class="doctor-stat-location">RS Medic Center - Bandung</p>
            <div class="doctor-stat-icon">
                <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
        </div>
        <div class="doctor-stat-card">
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

    {{-- Jadwal Saya --}}
    <div>
        <div class="doctor-section-head">
            <h2 class="doctor-section-title">Jadwal Saya</h2>
            <a href="{{ route('dokter.jadwal.index') }}" class="mediq-primary-btn doctor-add-schedule-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Jadwal Baru
            </a>
        </div>
        @if(count($jadwalSaya) > 0)
            <div class="doctor-schedule-strip">
                @foreach($jadwalSaya as $jadwal)
                    <div class="doctor-schedule-card">
                        <p class="doctor-card-eyebrow">Jadwal Tersedia</p>
                        <p class="doctor-card-title">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p class="doctor-card-muted">{{ $jadwal->jam_mulai ?? '-' }} - {{ $jadwal->jam_selesai ?? '-' }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="doctor-empty-state">
                <p>Belum ada jadwal tersedia. Buat jadwal baru!</p>
            </div>
        @endif
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

    <div class="doctor-rightbar-list">
        @forelse(array_slice($appointments, 0, 5) as $appointment)
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
                    </div>
                </div>
            </div>
        @empty
            <p class="doctor-rightbar-empty">Tidak ada jadwal mendatang</p>
        @endforelse
    </div>
@endsection
