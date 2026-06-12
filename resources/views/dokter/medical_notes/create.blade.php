@extends('layouts.dokter', ['active' => 'riwayat'])

@section('title', 'Catatan & Resep Medis - MediHub')

@push('head')
    <style>
        .form-section {
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        .form-group:last-child {
            margin-bottom: 0;
        }
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            font-size: 14px;
            color: #1a1a1a;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-wrapper input:focus {
            border-color: #58a7f5;
        }
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e6e6e6;
            border-radius: 12px;
            font-size: 14px;
            color: #1a1a1a;
            outline: none;
            resize: vertical;
            min-height: 100px;
            transition: border-color 0.2s;
        }
        .form-group textarea:focus {
            border-color: #58a7f5;
        }
        .patient-context-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #58a7f5;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }
        .context-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 600px) {
            .context-grid {
                grid-template-columns: 1fr;
            }
        }
        .context-item-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        .context-item-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            display: block;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            padding: 12px 16px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-top: 10px;
        }
        .checkbox-container input {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #16a34a;
        }
        .checkbox-label {
            font-size: 14px;
            font-weight: 600;
            color: #14532d;
        }
        .btn-primary {
            background-color: #58a7f5;
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .btn-primary:active {
            transform: scale(0.98);
        }
        .edit-btn {
            border: 1px solid #e6e6e6;
            background: #fff;
            color: #666;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .edit-btn:hover {
            background: #f8f9fa;
        }
    </style>
@endpush

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h1 class="header-title" style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">Konsultasi: Catatan & Resep</h1>
    <p class="header-subtitle" style="color: #64748b; margin-bottom: 24px;">Tulis catatan medis, diagnosa, dan resep obat pasien dalam satu halaman.</p>

    @if(session('success'))
        <div class="form-alert form-alert-success is-visible" style="margin-bottom: 24px; padding: 12px 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; color: #15803d;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="form-alert form-alert-error is-visible" style="margin-bottom: 24px; padding: 12px 16px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #b91c1c;">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Detail Pasien (Context Card) --}}
    @if($appointment)
        <div class="patient-context-card">
            <div class="context-grid">
                <div>
                    <span class="context-item-label">Nama Pasien</span>
                    <span class="context-item-value">{{ $appointment->patient_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="context-item-label">Tanggal Janji Temu</span>
                    <span class="context-item-value">
                        {{ $appointment->appointment_date ?? '-' }}
                        @if(!empty($appointment->appointment_time_start))
                            ({{ $appointment->appointment_time_start }} - {{ $appointment->appointment_time_end ?? '' }})
                        @endif
                    </span>
                </div>
                <div style="grid-column: span 2;">
                    <span class="context-item-label">Keluhan Utama</span>
                    <span class="context-item-value" style="font-style: italic;">"{{ $appointment->complaint ?? '-' }}"</span>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('dokter.medical_notes.store') }}" method="POST">
        @csrf
        <input type="hidden" name="appointment_id" value="{{ $appointmentId }}">
        <input type="hidden" name="patient_id" value="{{ $patientId }}">

        {{-- Section 1: Catatan Medis --}}
        <div class="form-section">
            <h2 class="section-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Catatan & Diagnosa Medis
            </h2>

            <div class="form-group">
                <label for="notes">Hasil Pemeriksaan / Diagnosa</label>
                <textarea
                    name="notes"
                    id="notes"
                    placeholder="Tulis keluhan pasien, diagnosis medis, tindakan/terapi, dan catatan lainnya..."
                    required
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Section 2: Resep Obat (Opsional) --}}
        <div class="form-section">
            <h2 class="section-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Resep Obat (Opsional)
            </h2>

            <div class="form-group">
                <label for="medications">Daftar Obat-obatan</label>
                <textarea
                    name="medications"
                    id="medications"
                    placeholder="Contoh: Paracetamol 500mg (10 tablet), Amoxicillin 500mg (15 tablet)..."
                >{{ old('medications') }}</textarea>
            </div>

            <div class="form-group">
                <label for="instructions">Aturan Pakai & Instruksi Khusus</label>
                <textarea
                    name="instructions"
                    id="instructions"
                    placeholder="Contoh: Paracetamol: 3x1 sehari sesudah makan (jika demam). Amoxicillin: 3x1 sehari sesudah makan (habiskan)..."
                >{{ old('instructions') }}</textarea>
            </div>
        </div>

        {{-- Section 3: Status & Aksi --}}
        @if($appointment && $appointment->status !== 'selesai')
            <div style="margin-bottom: 24px;">
                <label class="checkbox-container">
                    <input type="checkbox" name="complete_appointment" value="1" checked>
                    <span class="checkbox-label">Tandai Selesai Konsultasi & Ubah Status Janji Temu</span>
                </label>
                <p style="font-size: 12px; color: #64748b; margin-top: 6px; margin-left: 28px;">
                    Mengaktifkan pilihan ini akan mengubah status antrean menjadi "Selesai" dan mengirimkan notifikasi rekam medis digital ke pasien.
                </p>
            </div>
        @endif

        <div class="footer-action">
            <div style="display: flex; gap: 16px; align-items: center;">
                <button class="btn-primary" type="submit" style="flex: 2; height: 50px; padding: 0;">Simpan Hasil Konsultasi</button>
                <a href="{{ route('dokter.dashboard') }}" class="edit-btn" style="flex: 1; text-decoration: none; height: 50px; border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center;">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

