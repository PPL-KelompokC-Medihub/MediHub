@extends('layouts.dokter', ['active' => 'riwayat'])

@section('title', 'Riwayat Jadwal Temu - MediHub')

@push('head')
    @vite(['resources/css/dokter/dashboard.css'])
    <style>
        .doctor-history-container {
            padding: 8px 0;
        }
        
        /* ── GRID & CARD LAYOUT ── */
        .riwayat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
            margin-top: 8px;
        }

        .riwayat-card {
            background: #ffffff;
            border: 1px solid #e8eaef;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.006);
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .riwayat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(106, 164, 239, 0.08), 0 4px 6px -2px rgba(106, 164, 239, 0.03);
            border-color: #c8dff9;
        }

        .riwayat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .patient-icon-wrap {
            width: 40px;
            height: 40px;
            background-color: #f1f5f9;
            color: #6aa4ef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .service-type {
            font-size: 12px;
            font-weight: 500;
            color: #828793;
            margin: 0 0 2px;
        }

        .patient-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .riwayat-card-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .meta-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
        }
        
        .meta-icon {
            color: #aab0bc;
        }

        .riwayat-card-footer {
            margin-top: auto;
        }

        .btn-lihat-catatan {
            width: 100%;
            background-color: #58a7f5;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            text-align: center;
            display: block;
        }

        .btn-lihat-catatan:hover {
            background-color: #3b93ee;
        }

        .riwayat-empty-state {
            grid-column: 1 / -1;
            padding: 60px 20px;
            text-align: center;
            color: #94a3b8;
            background: #fff;
            border: 1px dashed #e2e8f0;
            border-radius: 16px;
            font-size: 14px;
        }
        
        /* ── STATUS PILL ── */
        .doctor-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
            text-align: center;
            justify-content: center;
        }

        .doctor-status-pill::before {
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .doctor-status-menunggu::before,
        .doctor-status-diperiksa::before,
        .doctor-status-ongoing::before {
            background-color: #58a7f5;
        }

        .doctor-status-selesai::before {
            background-color: #10b981;
        }

        .doctor-status-dibatalkan::before {
            background-color: #ef4444;
        }

        /* ── DETAILS DRAWER ── */
        .details-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            display: none;
            align-items: stretch;
            justify-content: flex-end;
        }
        
        .details-drawer.is-open {
            display: flex;
        }
        
        .drawer-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            transition: opacity 0.3s;
        }
        
        .drawer-content {
            position: relative;
            width: min(500px, 90%);
            background: #ffffff;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            animation: slideLeft 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            height: 100%;
        }
        
        @keyframes slideLeft {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        .drawer-header {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .btn-close-drawer {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 8px;
            margin-left: -8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        
        .btn-close-drawer:hover {
            background-color: #f1f5f9;
            color: #475569;
        }
        
        .drawer-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        
        .drawer-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .drawer-meta-grid {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        
        .drawer-meta-grid label {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 4px;
        }
        
        .meta-val {
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin: 0;
        }
        
        .meta-val.highlight {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.1;
        }
        
        .drawer-divider {
            border: 0;
            border-top: 1px solid #f1f5f9;
            margin: 0;
        }
        
        .drawer-section {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        
        .drawer-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        
        .drawer-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .drawer-field label {
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
        }
        
        .patient-name-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        
        .field-val-bold {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .btn-akhiri-sesi {
            background-color: #58a7f5;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-akhiri-sesi:hover {
            background-color: #3b93ee;
        }
        
        .field-val {
            font-size: 13px;
            color: #475569;
            margin: 0;
            line-height: 1.5;
            white-space: pre-line;
        }
        
        .drawer-doctor-footer {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }
        
        .drawer-doctor-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .drawer-doctor-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .drawer-doctor-specialty {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
    </style>
@endpush

@section('content')
<div class="doctor-history-container">
    @include('dokter.header')

    {{-- Judul Halaman dan Informasi Filter --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; margin-top: 10px;">
        <h2 class="doctor-section-title" style="font-size: 18px; font-weight: 700; margin: 0;">Riwayat</h2>
        @if($search !== '' || $date !== '')
            <div style="display: flex; align-items: center; gap: 10px;">
                @if($search !== '')
                    <span style="font-size: 13px; color: #475569; background: #e2e8f0; padding: 4px 12px; border-radius: 999px;">
                        Pencarian: "{{ $search }}"
                    </span>
                @endif
                <a href="{{ route('dokter.riwayat') }}" style="color: #ef4444; font-size: 13px; font-weight: 600; text-decoration: none;">
                    Clear Filter
                </a>
            </div>
        @endif
    </div>

    {{-- Grid Card Riwayat ── Menggantikan format tabel lama --}}
    <div class="riwayat-grid">
        @forelse($appointments as $appointment)
            @php
                $patientId = $appointment->patient_id ?? $appointment->user_uid ?? '';
                $appointmentId = $appointment->id ?? '';
                $notesForAppointment = $medicalNotes->where('appointment_id', $appointmentId);
                $latestNote = $notesForAppointment->sortByDesc('created_at')->first();
                if (!$latestNote && $patientId !== '') {
                    $notesForPatient = $medicalNotes->where('patient_id', $patientId);
                    $latestNote = $notesForPatient->sortByDesc('created_at')->first();
                }
            @endphp
            
            <div class="riwayat-card" id="appointment-card-{{ $appointmentId }}" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                <!-- Header: Profile & Status -->
                <div class="riwayat-card-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 4px;">
                    <div class="patient-info" style="display: flex; align-items: center; gap: 12px;">
                        <!-- Solid blue avatar icon container -->
                        <div class="patient-avatar-wrap" style="color: #0275d8; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #eef5ff; border-radius: 50%; flex-shrink: 0;">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span class="service-type" style="font-size: 14px; font-weight: 600; color: #287ae6; margin: 0; line-height: 1.2;">
                                Pasien - {{ $dokter->specialty ?? 'Konseling Rutin' }}
                            </span>
                            <span class="patient-name" style="font-size: 14px; font-weight: 500; color: #1e293b; margin: 0; line-height: 1.2;">
                                {{ $appointment->patient_name ?? '-' }}
                            </span>
                        </div>
                    </div>
                    @php
                        $statusKeyMapped = $appointment->status_key ?? 'menunggu';
                        $statusTextMapped = $appointment->display_status ?? 'Menunggu';
                        if ($statusKeyMapped === 'menunggu' || $statusKeyMapped === 'diperiksa') {
                            $statusKeyMapped = 'ongoing';
                            $statusTextMapped = 'On Going';
                        }
                    @endphp
                    <span class="doctor-status-pill doctor-status-{{ $statusKeyMapped }}" id="status-pill-{{ $appointmentId }}" style="align-self: flex-start;">
                        {{ $statusTextMapped }}
                    </span>
                </div>
                
                <!-- Metadata: Date and Time divided by vertical line -->
                <div class="riwayat-card-meta-grid" style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 16px; margin-top: 4px;">
                    <!-- Date -->
                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                        <svg width="20" height="20" fill="none" stroke="#64748b" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span style="font-size: 13px; color: #64748b; font-weight: 500;">
                            {{ \Carbon\Carbon::parse($appointment->display_date)->translatedFormat('d F Y') }}
                        </span>
                    </div>
                    
                    <!-- Vertical Divider Line -->
                    <div style="width: 1px; height: 36px; background-color: #e2e8f0; align-self: flex-end;"></div>
                    
                    <!-- Time -->
                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                        <svg width="20" height="20" fill="none" stroke="#64748b" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span style="font-size: 13px; color: #64748b; font-weight: 500;">
                            {{ $appointment->display_time ?? '-' }}
                        </span>
                    </div>
                </div>
                
                <div class="riwayat-card-footer" style="margin-top: auto; width: 100%;">
                    <button type="button" class="btn-lihat-catatan"
                        data-appointment-id="{{ $appointmentId }}"
                        data-patient-name="{{ $appointment->patient_name ?? '-' }}"
                        data-status-key="{{ $appointment->status_key ?? 'menunggu' }}"
                        data-display-status="{{ $appointment->display_status ?? 'Menunggu' }}"
                        data-date="{{ \Carbon\Carbon::parse($appointment->display_date)->translatedFormat('d F Y') }}"
                        data-time="{{ $appointment->display_time ?? '-' }}"
                        data-queue="{{ str_pad($appointment->queue_number ?? '1', 2, '0', STR_PAD_LEFT) }}"
                        data-complaint="{{ $appointment->display_complaint ?? '-' }}"
                        data-observasi="{{ $latestNote ? ($latestNote->hasil_observasi ?? '') : '' }}"
                        data-asesmen="{{ $latestNote ? ($latestNote->hasil_asesmen ?? '') : '' }}"
                        data-kesimpulan="{{ $latestNote ? ($latestNote->kesimpulan ?? $latestNote->notes ?? '') : '' }}"
                        data-rekomendasi="{{ $latestNote ? ($latestNote->rekomendasi ?? '') : '' }}"
                        data-resep="{{ $latestNote ? ($latestNote->resep_obat ?? '') : '' }}"
                        data-update-url="{{ route('dokter.appointment.update-status', $appointmentId) }}"
                        onclick="openMedicalNoteDrawer(this)"
                    >
                        Lihat Catatan Medis
                    </button>
                </div>
            </div>
        @empty
            <div class="riwayat-empty-state">
                Belum ada riwayat jadwal temu
            </div>
        @endforelse
    </div>
</div>

{{-- ── DETAILS DRAWER ── --}}
<div class="details-drawer" id="details-drawer">
    <div class="drawer-overlay" onclick="closeMedicalNoteDrawer()"></div>
    <div class="drawer-content">
        <div class="drawer-header">
            <button type="button" class="btn-close-drawer" onclick="closeMedicalNoteDrawer()" aria-label="Tutup Detail">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="drawer-title-row">
                <h2 class="drawer-title">Catatan Medis</h2>
                <span class="doctor-status-pill" id="drawer-status-pill">On Going</span>
            </div>
        </div>
        
        <div class="drawer-body">
            <!-- Antrean, Tanggal, Jam -->
            <div class="drawer-meta-grid">
                <!-- Antrian -->
                <div style="display: flex; flex-direction: column; align-items: flex-start;">
                    <label>Antrian</label>
                    <p class="meta-val highlight" id="drawer-queue" style="margin: 0; font-size: 28px; line-height: 1;">01</p>
                </div>
                
                <!-- Tanggal -->
                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px; margin-left: 12px;">
                    <svg width="20" height="20" fill="none" stroke="#64748b" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span id="drawer-date" class="meta-val">02 September 2025</span>
                </div>
                
                <!-- Divider -->
                <div style="width: 1px; height: 36px; background-color: #e2e8f0; align-self: flex-end;"></div>
                
                <!-- Waktu -->
                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
                    <svg width="20" height="20" fill="none" stroke="#64748b" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span id="drawer-time" class="meta-val">13:00 WIB - 13:15 WIB</span>
                </div>
            </div>
            
            <hr class="drawer-divider" />
            
            <!-- Data Pasien -->
            <div class="drawer-section">
                <h3 class="drawer-section-title">Data Pasien</h3>
                
                <div class="drawer-field">
                    <label>Nama Pasien</label>
                    <div class="patient-name-row">
                        <p class="field-val-bold" id="drawer-patient-name">Naswa Gyna Sahira</p>
                        <button type="button" class="btn-akhiri-sesi" id="btn-akhiri-sesi" onclick="akhiriSesiCurrent()">Akhiri Sesi</button>
                    </div>
                </div>
                
                <div class="drawer-field">
                    <label>Keluhan Utama</label>
                    <p class="field-val" id="drawer-complaint">Keluhan...</p>
                </div>
            </div>
            
            <hr class="drawer-divider" />
            
            <!-- Diagnosa Pasien -->
            <div class="drawer-section">
                <h3 class="drawer-section-title">Diagnosa Pasien</h3>
                
                <div class="drawer-field">
                    <label>Hasil Observasi</label>
                    <p class="field-val" id="drawer-observasi">-</p>
                </div>
                
                <div class="drawer-field">
                    <label>Hasil Asesmen Psikologis</label>
                    <p class="field-val" id="drawer-asesmen">-</p>
                </div>
                
                <div class="drawer-field">
                    <label>Kesimpulan</label>
                    <p class="field-val" id="drawer-kesimpulan">-</p>
                </div>
                
                <div class="drawer-field">
                    <label>Rekomendasi</label>
                    <p class="field-val" id="drawer-rekomendasi">-</p>
                </div>
                
                <div class="drawer-field">
                    <label>Resep Obat</label>
                    <p class="field-val" id="drawer-resep">-</p>
                </div>
            </div>
            
            <!-- Doctor Info and Actions at Bottom -->
            <div class="drawer-doctor-footer">
                @if(!blank($dokter->profile_pict ?? null))
                    <img src="{{ asset('storage/' . $dokter->profile_pict) }}" alt="Avatar" class="drawer-doctor-avatar" />
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($dokter->name ?? 'Dokter') }}&background=6aa4ef&color=fff&size=96" alt="Avatar" class="drawer-doctor-avatar" />
                @endif
                <div style="flex: 1;">
                    <p class="drawer-doctor-name">dr. {{ $dokter->name ?? Auth::user()->name ?? 'Dokter' }}</p>
                    <p class="drawer-doctor-specialty">{{ $dokter->specialty ?? 'Spesialis' }}</p>
                </div>
            </div>
            
            <a href="#" class="btn-lihat-catatan" id="drawer-btn-create-note" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px; padding: 12px; font-size: 14px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Catatan Medis
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentDrawerTarget = null;

    window.openMedicalNoteDrawer = function (button) {
        currentDrawerTarget = button;
        
        const appointmentId = button.dataset.appointmentId;
        const patientName = button.dataset.patientName;
        const statusKey = button.dataset.statusKey;
        const displayStatus = button.dataset.displayStatus;
        const date = button.dataset.date;
        const time = button.dataset.time;
        const queue = button.dataset.queue;
        const complaint = button.dataset.complaint;
        const observasi = button.dataset.observasi;
        const asesmen = button.dataset.asesmen;
        const kesimpulan = button.dataset.kesimpulan;
        const rekomendasi = button.dataset.rekomendasi;
        const resep = button.dataset.resep;
        const updateUrl = button.dataset.updateUrl;
        
        // Set drawer fields
        document.getElementById('drawer-patient-name').textContent = patientName;
        document.getElementById('drawer-date').textContent = date;
        document.getElementById('drawer-time').textContent = time;
        document.getElementById('drawer-queue').textContent = queue;
        document.getElementById('drawer-complaint').textContent = complaint || '-';
        document.getElementById('drawer-observasi').textContent = observasi || '-';
        document.getElementById('drawer-asesmen').textContent = asesmen || '-';
        document.getElementById('drawer-kesimpulan').textContent = kesimpulan || '-';
        document.getElementById('drawer-rekomendasi').textContent = rekomendasi || '-';
        document.getElementById('drawer-resep').textContent = resep || '-';
        
        // Set status pill classes
        const statusPill = document.getElementById('drawer-status-pill');
        let mappedStatusClass = statusKey;
        let mappedStatusText = displayStatus;
        
        if (statusKey === 'menunggu' || statusKey === 'diperiksa') {
            mappedStatusClass = 'ongoing';
            mappedStatusText = 'On Going';
        }
        
        statusPill.textContent = mappedStatusText;
        
        // Reset classes
        statusPill.className = 'doctor-status-pill';
        statusPill.classList.add(`doctor-status-${mappedStatusClass}`);
        
        // Manage "Akhiri Sesi" button visibility
        const btnAkhiri = document.getElementById('btn-akhiri-sesi');
        btnAkhiri.dataset.appointmentId = appointmentId;
        btnAkhiri.dataset.updateUrl = updateUrl;
        
        if (statusKey === 'selesai' || statusKey === 'dibatalkan') {
            btnAkhiri.style.display = 'none';
        } else {
            btnAkhiri.style.display = 'block';
        }

        // Manage creation / view page link
        const btnCreateNote = document.getElementById('drawer-btn-create-note');
        const hasNotes = (observasi && observasi !== '-' && observasi !== '') ||
                         (asesmen && asesmen !== '-' && asesmen !== '') ||
                         (kesimpulan && kesimpulan !== '-' && kesimpulan !== '') ||
                         (rekomendasi && rekomendasi !== '-' && rekomendasi !== '') ||
                         (resep && resep !== '-' && resep !== '');

        if (hasNotes) {
            btnCreateNote.style.display = 'none'; // Hide if already filled (matches image 2 and 3)
        } else {
            btnCreateNote.innerHTML = `
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Buat Catatan Medis
            `;
            btnCreateNote.href = `/dokter/catatan-medis/${appointmentId}/create`;
            btnCreateNote.style.display = 'flex'; // Show if empty (matches image 1)
        }
        
        // Open drawer
        document.getElementById('details-drawer').classList.add('is-open');
    };

    window.closeMedicalNoteDrawer = function () {
        document.getElementById('details-drawer').classList.remove('is-open');
        currentDrawerTarget = null;
    };

    window.akhiriSesiCurrent = function () {
        const btn = document.getElementById('btn-akhiri-sesi');
        const appointmentId = btn.dataset.appointmentId;
        const updateUrl = btn.dataset.updateUrl;
        
        btn.disabled = true;
        btn.textContent = 'Memproses...';
        
        fetch(updateUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: 'selesai' }),
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                // Update drawer status pill
                const statusPill = document.getElementById('drawer-status-pill');
                let displayStatusLabel = data.status_label;
                let statusKeyClass = data.new_status;
                if (statusKeyClass === 'menunggu' || statusKeyClass === 'diperiksa') {
                    displayStatusLabel = 'On Going';
                    statusKeyClass = 'ongoing';
                }
                statusPill.textContent = displayStatusLabel;
                statusPill.className = `doctor-status-pill doctor-status-${statusKeyClass}`;
                
                // Hide "Akhiri Sesi" button
                btn.style.display = 'none';
                
                // Update card status pill in grid
                const cardPill = document.getElementById(`status-pill-${appointmentId}`);
                if (cardPill) {
                    cardPill.textContent = displayStatusLabel;
                    cardPill.className = `doctor-status-pill doctor-status-${statusKeyClass}`;
                }
                
                // Update the dataset status on the button that opened the drawer
                if (currentDrawerTarget) {
                    currentDrawerTarget.dataset.statusKey = data.new_status;
                    currentDrawerTarget.dataset.displayStatus = data.status_label;
                }
                
                // Show toast message
                if (window.showToast) {
                    window.showToast(data.message);
                } else if (typeof showToast === 'function') {
                    showToast(data.message);
                } else {
                    alert(data.message);
                }
            } else {
                alert(data.message ?? 'Gagal mengakhiri sesi.');
            }
        })
        .catch((err) => {
            console.error(err);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Akhiri Sesi';
        });
    };
</script>
@endpush

