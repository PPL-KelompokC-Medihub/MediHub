@extends('layouts.dokter', ['active' => 'jadwal'])

@section('title', 'Jadwal Temu - MediHub')

@push('head')
    @vite(['resources/css/dokter/jadwal.css', 'resources/js/dokter/jadwal.js'])
@endpush

@section('content')
    <div
        class="doctor-schedule-root"
        data-doctor-schedule
        data-store-url="{{ route('dokter.jadwal.store') }}"
        data-base-url="/dokter/jadwal"
    >
    <div class="doctor-schedule-header">
        <h1 class="doctor-schedule-page-title">Jadwal Saya</h1>
        <button type="button" data-schedule-modal="create" class="mediq-primary-btn doctor-create-button">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Buat Jadwal Baru
        </button>
    </div>

    {{-- Minggu Ini --}}
    <div>
        <h2 class="doctor-schedule-section-title">Minggu Ini</h2>
        @if(count($mingguIni) > 0)
            <div class="doctor-schedule-grid">
                @foreach($mingguIni as $j)
                    <div class="doctor-schedule-card">
                        <p class="doctor-schedule-eyebrow">Jadwal Tersedia</p>
                        <p class="doctor-schedule-date">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p class="doctor-schedule-time">{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</p>
                        <div class="doctor-schedule-actions">
                            <button type="button" data-schedule-delete="{{ $j->id }}" class="doctor-delete-button">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                </svg>
                            </button>
                            <button type="button" data-schedule-modal="edit" data-schedule-id="{{ $j->id }}" data-tanggal="{{ $j->tanggal }}" data-jam-mulai="{{ $j->jam_mulai }}" data-jam-selesai="{{ $j->jam_selesai }}"
                                class="mediq-primary-btn doctor-edit-button">
                                Edit
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="doctor-schedule-empty">
                Belum ada jadwal minggu ini
            </div>
        @endif
    </div>

    {{-- Minggu Depan --}}
    <div>
        <h2 class="doctor-schedule-section-title">Minggu Depan</h2>
        @if(count($mingguDepan) > 0)
            <div class="doctor-schedule-grid">
                @foreach($mingguDepan as $j)
                    <div class="doctor-schedule-card">
                        <p class="doctor-schedule-eyebrow">Jadwal Tersedia</p>
                        <p class="doctor-schedule-date">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p class="doctor-schedule-time">{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</p>
                        <div class="doctor-schedule-actions">
                            <button type="button" data-schedule-delete="{{ $j->id }}" class="doctor-delete-button">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                </svg>
                            </button>
                            <button type="button" data-schedule-modal="edit" data-schedule-id="{{ $j->id }}" data-tanggal="{{ $j->tanggal }}" data-jam-mulai="{{ $j->jam_mulai }}" data-jam-selesai="{{ $j->jam_selesai }}"
                                class="mediq-primary-btn doctor-edit-button">
                                Edit
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 5H5a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="doctor-schedule-empty">
                Belum ada jadwal minggu depan
            </div>
        @endif
    </div>

    {{-- MODAL --}}
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
@endsection

@section('rightbar')
    <h3 class="doctor-rightbar-title">Jadwal Temu Mendatang</h3>
    <p class="doctor-rightbar-empty">Tidak ada jadwal mendatang</p>
@endsection
