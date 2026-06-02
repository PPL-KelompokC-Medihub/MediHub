@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Buat Resep Dokter</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ url('dokter/prescriptions') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="medical_note_id" class="form-label">ID Catatan Medis (opsional)</label>
            <input type="text" class="form-control" name="medical_note_id" id="medical_note_id">
        </div>

        <div class="mb-3">
            <label for="patient_id" class="form-label">ID Pasien (opsional)</label>
            <input type="text" class="form-control" name="patient_id" id="patient_id">
        </div>

        <div class="mb-3">
            <label for="medications" class="form-label">Obat-obatan (pisahkan baris untuk tiap obat)</label>
            <textarea name="medications" id="medications" class="form-control" rows="6" required></textarea>
        </div>

        <div class="mb-3">
            <label for="instructions" class="form-label">Instruksi (opsional)</label>
            <textarea name="instructions" id="instructions" class="form-control" rows="3"></textarea>
        </div>

        <button class="btn btn-primary" type="submit">Simpan Resep</button>
    </form>
</div>
@endsection
