@extends('layouts.dokter', ['active' => 'riwayat'])

@section('title', 'Riwayat Jadwal Temu - MediHub')

@push('head')
    @vite(['resources/css/dokter/dashboard.css'])
    <style>
        .doctor-history-container {
            padding: 8px 0;
        }
        .history-card-details {
            font-size: 13px;
            color: #4b5563;
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 4px;
            border-left: 3px solid #cbd5e1;
        }
        .history-empty-note {
            color: #94a3b8;
            font-style: italic;
            font-size: 13px;
        }
    </style>
@endpush

@section('content')
<div class="doctor-history-container">
    {{-- Header --}}
    <div class="mediq-header-row" style="margin-bottom: 24px;">
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
                <h1 class="mediq-user-name" style="font-size: 24px; font-weight: 700;">
                    Riwayat Jadwal Temu
                </h1>
                <p class="doctor-greeting-subtitle">Daftar pemeriksaan pasien terdahulu dan rekam medis.</p>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div style="background: #fff; border: 1px solid #e8eaef; border-radius: 16px; padding: 20px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <form action="{{ route('dokter.riwayat') }}" method="GET" class="doctor-patient-search-form" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div class="mediq-search-wrap doctor-patient-search-wrap" style="flex: 2; min-width: 250px;">
                <svg class="mediq-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="mediq-search-input"
                    placeholder="Cari nama pasien atau keluhan..."
                    value="{{ $search }}"
                    style="width: 100%;"
                />
            </div>
            <input
                type="date"
                name="date"
                value="{{ $date }}"
                class="doctor-patient-date-filter"
                aria-label="Filter tanggal"
                style="height: 44px; border: 1px solid #e8eaef; border-radius: 20px; padding: 0 16px; color: #4b5563;"
            />
            <button type="submit" class="doctor-patient-search-button" style="height: 44px; padding: 0 24px; background-color: #58a7f5; color: #fff; border: none; border-radius: 20px; font-weight: 600; cursor: pointer;">
                Cari
            </button>
            @if($search !== '' || $date !== '')
                <a href="{{ route('dokter.riwayat') }}" class="doctor-patient-reset-link" style="color: #64748b; font-weight: 600; text-decoration: none;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Tabel Riwayat --}}
    <div style="background: #fff; border: 1px solid #e8eaef; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <div class="doctor-patient-table-wrap">
            <table class="doctor-patient-table">
                <thead>
                    <tr>
                        <th style="padding: 16px 20px;">Pasien</th>
                        <th>Tanggal & Jam</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Catatan Medis & Resep</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        @php
                            $patientId = $appointment->patient_id ?? $appointment->user_uid ?? '';
                            $notesForPatient = $medicalNotes->where('patient_id', $patientId);
                            $latestNote = $notesForPatient->sortByDesc('created_at')->first();
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td class="doctor-patient-name" style="padding: 20px; font-weight: 600; color: #1e293b;">
                                {{ $appointment->patient_name ?? '-' }}
                            </td>
                            <td class="doctor-patient-date" style="color: #475569;">
                                <div>{{ $appointment->display_date ?? '-' }}</div>
                                <div style="font-size: 12px; color: #94a3b8;">{{ $appointment->display_time ?? '-' }}</div>
                            </td>
                            <td class="doctor-patient-complaint" style="color: #475569; max-width: 200px;">
                                {{ $appointment->display_complaint ?? '-' }}
                            </td>
                            <td>
                                <span class="doctor-status-pill doctor-status-{{ $appointment->status_key ?? 'menunggu' }}">
                                    {{ $appointment->display_status ?? 'Menunggu' }}
                                </span>
                            </td>
                            <td style="max-width: 300px;">
                                @if($latestNote)
                                    <div class="history-card-details" style="margin-bottom: 6px;">
                                        <strong>Diagnosa:</strong> {{ Str::limit($latestNote->notes, 80) }}
                                    </div>
                                    @if($latestNote->prescriptions->count() > 0)
                                        @foreach($latestNote->prescriptions as $prescription)
                                            <div class="history-card-details" style="border-left-color: #58a7f5; background-color: #f0f7ff; margin-top: 4px;">
                                                <strong>Resep:</strong> {{ Str::limit($prescription->medications, 80) }}
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="history-empty-note" style="padding-left: 14px;">Tidak ada resep obat</div>
                                    @endif
                                @else
                                    <span class="history-empty-note">Belum dicatat</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <a href="{{ route('dokter.medical_notes.create', ['patient_id' => $patientId]) }}" class="doctor-edit-button" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 6px 12px;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ $latestNote ? 'Update Catatan' : 'Tulis Catatan' }}
                                    </a>
                                    <a href="{{ route('dokter.prescriptions.create', ['patient_id' => $patientId, 'medical_note_id' => $latestNote ? $latestNote->id : '']) }}" class="doctor-edit-button" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 6px 12px; background-color: #f1f5f9; color: #475569; border-color: #cbd5e1;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Tulis Resep
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="doctor-table-empty" style="padding: 40px; text-align: center; color: #94a3b8;">
                                Belum ada riwayat jadwal temu
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
