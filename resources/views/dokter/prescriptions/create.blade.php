@extends('layouts.dokter', ['active' => 'riwayat'])

@section('title', 'Buat Resep Obat - MediHub')

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
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
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
            min-height: 120px;
            transition: border-color 0.2s;
        }
        .form-group textarea:focus {
            border-color: #58a7f5;
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
        .footer-action {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
    </style>
@endpush

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div class="logo">
        <span class="logo-text">MediHub</span>
    </div>

    <h1 class="header-title">Buat Resep Obat</h1>
    <p class="header-subtitle">Buat resep obat digital untuk pasien berdasarkan catatan pemeriksaan medis.</p>

    @if(session('success'))
        <div class="form-alert form-alert-success is-visible" style="margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="form-alert form-alert-error is-visible" style="margin-bottom: 24px;">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dokter.prescriptions.store') }}" method="POST">
        @csrf
        <div class="form-section">
            <h2 class="section-title">Detail Resep</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="medical_note_id">ID Catatan Medis (opsional)</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            name="medical_note_id"
                            id="medical_note_id"
                            placeholder="Terisi otomatis jika dari Catatan Medis"
                            value="{{ old('medical_note_id', request('medical_note_id')) }}"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="patient_id">ID Pasien (opsional)</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            name="patient_id"
                            id="patient_id"
                            placeholder="Terisi otomatis jika dari dashboard"
                            value="{{ old('patient_id', request('patient_id')) }}"
                        >
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px; margin-bottom: 20px;">
                <label for="medications">Obat-obatan</label>
                <textarea
                    name="medications"
                    id="medications"
                    placeholder="Tulis nama obat, dosis, dan jumlah (misal: Paracetamol 500mg - 10 tablet)..."
                    required
                >{{ old('medications') }}</textarea>
            </div>

            <div class="form-group">
                <label for="instructions">Aturan Pakai & Instruksi (opsional)</label>
                <textarea
                    name="instructions"
                    id="instructions"
                    placeholder="Tulis instruksi penggunaan (misal: Diminum 3 kali sehari setelah makan)..."
                >{{ old('instructions') }}</textarea>
            </div>
        </div>

        <div class="footer-action">
            <div style="display: flex; gap: 16px; align-items: center;">
                <button class="btn-primary" type="submit" style="flex: 2; height: 50px; padding: 0;">Simpan Resep Obat</button>
                <a href="{{ route('dokter.dashboard') }}" class="edit-btn" style="flex: 1; text-decoration: none; height: 50px; border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); background: var(--white); color: var(--text-gray);">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
