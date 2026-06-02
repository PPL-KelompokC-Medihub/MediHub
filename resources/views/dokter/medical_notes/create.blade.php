@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Buat Catatan Medis</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ url('dokter/medical-notes') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="patient_id" class="form-label">ID Pasien (opsional)</label>
            <input type="text" class="form-control" name="patient_id" id="patient_id">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Catatan</label>
            <textarea name="notes" id="notes" class="form-control" rows="6" required></textarea>
        </div>

        <button class="btn btn-primary" type="submit">Simpan</button>
    </form>
</div>
@endsection
