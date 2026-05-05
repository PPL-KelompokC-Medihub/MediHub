{{--
    PBI-13: Halaman Profil Dokter (read-only view).

    Menampilkan seluruh informasi pribadi dan profesional dokter:
    data diri, alamat, keahlian, dokumen, dan sertifikasi.
--}}
@extends('layouts.dokter', ['active' => 'profil'])

@section('title', 'Profil Dokter - MediHub')

@push('head')
    @vite('resources/css/dokter/profil.css')
@endpush

@section('content')
    {{-- Profile Header --}}
    <div class="profil-header">
        <img src="https://ui-avatars.com/api/?name={{ urlencode($user['name'] ?? 'Dokter') }}&background=6aa4ef&color=fff&size=176&font-size=0.38"
             alt="Foto Profil" class="profil-avatar" />
        <div class="profil-header-info">
            <h1 class="profil-header-name">dr. {{ $user['name'] ?? '-' }}</h1>
            <p class="profil-header-specialty">{{ $user['specialty'] ?? 'Spesialisasi belum diisi' }}</p>
            <p class="profil-header-email">{{ $user['email'] ?? '-' }}</p>
        </div>
        <a href="{{ route('dokter.profile.personal') }}" class="profil-edit-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit Profil
        </a>
    </div>

    {{-- Section: Data Diri Dokter --}}
    <div class="profil-section" id="section-data-diri">
        <div class="profil-section-header">
            <h2 class="profil-section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                Data Diri Dokter
            </h2>
            <a href="{{ route('dokter.profile.personal') }}" class="profil-section-edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
        <div class="profil-section-body">
            <div class="profil-info-grid">
                <div class="profil-info-item">
                    <span class="profil-info-label">Nama Lengkap</span>
                    <span class="profil-info-value {{ blank($user['name'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['name'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Umur</span>
                    <span class="profil-info-value {{ blank($user['age'] ?? null) ? 'is-empty' : '' }}">
                        {{ isset($user['age']) ? $user['age'] . ' Tahun' : 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Email</span>
                    <span class="profil-info-value {{ blank($user['email'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['email'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Nomor HP</span>
                    <span class="profil-info-value {{ blank($user['phone'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['phone'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Berat Badan</span>
                    <span class="profil-info-value {{ blank($user['weight'] ?? null) ? 'is-empty' : '' }}">
                        {{ isset($user['weight']) ? $user['weight'] . ' kg' : 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Tinggi Badan</span>
                    <span class="profil-info-value {{ blank($user['height'] ?? null) ? 'is-empty' : '' }}">
                        {{ isset($user['height']) ? $user['height'] . ' cm' : 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Jenis Kelamin</span>
                    @if(!blank($user['gender'] ?? null))
                        <span class="profil-gender-badge {{ ($user['gender'] ?? '') === 'Pria' ? 'is-pria' : 'is-perempuan' }}">
                            @if(($user['gender'] ?? '') === 'Pria')
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M15.5 1h5v5l-1.79-1.79-4.36 4.36a6.5 6.5 0 11-1.41-1.41l4.36-4.36L15.5 1zM9.5 21a4.5 4.5 0 100-9 4.5 4.5 0 000 9z"/></svg>
                            @else
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a6 6 0 016 6c0 2.97-2.16 5.44-5 5.92V16h2v2h-2v2h-2v-2H9v-2h2v-2.08C8.16 13.44 6 10.97 6 8a6 6 0 016-6zm0 2a4 4 0 100 8 4 4 0 000-8z"/></svg>
                            @endif
                            {{ $user['gender'] }}
                        </span>
                    @else
                        <span class="profil-info-value is-empty">Belum diisi</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Alamat --}}
    <div class="profil-section" id="section-alamat">
        <div class="profil-section-header">
            <h2 class="profil-section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                Alamat
            </h2>
            <a href="{{ route('dokter.profile.personal') }}" class="profil-section-edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
        <div class="profil-section-body">
            <div class="profil-info-grid">
                <div class="profil-info-item">
                    <span class="profil-info-label">Negara</span>
                    <span class="profil-info-value {{ blank($user['country'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['country'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Kota</span>
                    <span class="profil-info-value {{ blank($user['city'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['city'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Kode POS</span>
                    <span class="profil-info-value {{ blank($user['postal_code'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['postal_code'] ?? 'Belum diisi' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Keahlian Dokter --}}
    <div class="profil-section" id="section-keahlian">
        <div class="profil-section-header">
            <h2 class="profil-section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                Keahlian Dokter
            </h2>
            <a href="{{ route('dokter.profile.expertise') }}" class="profil-section-edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
        <div class="profil-section-body">
            <div class="profil-info-grid">
                <div class="profil-info-item">
                    <span class="profil-info-label">Spesialisasi Utama</span>
                    <span class="profil-info-value {{ blank($user['specialty'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['specialty'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Sub-spesialisasi</span>
                    <span class="profil-info-value {{ blank($user['sub_specialty'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['sub_specialty'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Tahun Mulai Praktik</span>
                    <span class="profil-info-value {{ blank($user['started_practice_year'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['started_practice_year'] ?? 'Belum diisi' }}
                    </span>
                </div>
                <div class="profil-info-item">
                    <span class="profil-info-label">Institusi Pendidikan</span>
                    <span class="profil-info-value {{ blank($user['education_institution'] ?? null) ? 'is-empty' : '' }}">
                        {{ $user['education_institution'] ?? 'Belum diisi' }}
                    </span>
                </div>
            </div>

            {{-- Layanan Yang Ditawarkan --}}
            <div style="margin-top: 20px;">
                <span class="profil-info-label" style="margin-bottom: 10px; display: block;">Layanan Yang Ditawarkan</span>
                <div class="profil-service-pills">
                    @php
                        $userServices = $user['services'] ?? [];
                        if (!is_array($userServices)) $userServices = [];
                    @endphp
                    @foreach($services as $service)
                        <span class="profil-service-pill {{ in_array($service, $userServices, true) ? '' : 'is-inactive' }}">
                            {{ $service }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Biografi Singkat --}}
            @if(!blank($user['bio'] ?? null))
                <div class="profil-bio">
                    <span class="profil-info-label" style="margin-bottom: 8px; display: block;">Biografi Singkat</span>
                    {{ $user['bio'] }}
                </div>
            @endif
        </div>
    </div>

    {{-- Section: Dokumen --}}
    <div class="profil-section" id="section-dokumen">
        <div class="profil-section-header">
            <h2 class="profil-section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
                Dokumen
            </h2>
            <a href="{{ route('dokter.profile.certification') }}" class="profil-section-edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
        <div class="profil-section-body">
            @php
                $documents = [
                    ['key' => 'STR', 'label' => 'Surat Tanda Registrasi (STR)'],
                    ['key' => 'SIP', 'label' => 'Surat Izin Praktik (SIP)'],
                    ['key' => 'ijazah_doctor', 'label' => 'Ijazah Dokter'],
                    ['key' => 'KTP', 'label' => 'KTP / Identitas Resmi'],
                    ['key' => 'profile_pict', 'label' => 'Foto Profil Profesional'],
                ];
            @endphp
            <div class="profil-doc-grid">
                @foreach($documents as $doc)
                    @php $hasDoc = !blank($user[$doc['key']] ?? null); @endphp
                    <div class="profil-doc-item">
                        <div class="profil-doc-icon {{ $hasDoc ? 'is-uploaded' : 'is-missing' }}">
                            @if($hasDoc)
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                            @else
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                            @endif
                        </div>
                        <div class="profil-doc-info">
                            <div class="profil-doc-name">{{ $doc['label'] }}</div>
                            <div class="profil-doc-status {{ $hasDoc ? 'is-uploaded' : 'is-missing' }}">
                                {{ $hasDoc ? 'Sudah diunggah' : 'Belum diunggah' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Section: Sertifikasi --}}
    <div class="profil-section" id="section-sertifikasi">
        <div class="profil-section-header">
            <h2 class="profil-section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                Sertifikasi
            </h2>
            <a href="{{ route('dokter.profile.certification') }}" class="profil-section-edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
        <div class="profil-section-body">
            @php
                $certifications = [];
                for ($i = 1; $i <= 6; $i++) {
                    $val = $user["certification{$i}"] ?? null;
                    if (!blank($val)) {
                        $certifications[] = ['index' => $i, 'value' => $val];
                    }
                }
            @endphp

            @if(count($certifications) > 0)
                <div class="profil-cert-grid">
                    @foreach($certifications as $cert)
                        <div class="profil-cert-item">
                            <div class="profil-cert-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14,2 14,8 20,8"/>
                                    <path d="M9 15l2 2 4-4"/>
                                </svg>
                            </div>
                            <span class="profil-cert-label">Sertifikasi {{ $cert['index'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 24px; color: var(--muted); font-size: 14px;">
                    Belum ada sertifikasi yang diunggah.
                </div>
            @endif
        </div>
    </div>
@endsection

@section('rightbar')
    {{-- Pengaturan Akun --}}
    <div class="profil-account-section">
        <div class="profil-account-header">
            <div class="profil-account-avatar">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="profil-account-header-text">
                <h3 class="profil-account-header-title">Pusat Akun</h3>
                <p class="profil-account-header-sub">Kata sandi, keamanan, dan detail pribadi.</p>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="9,18 15,12 9,6"/>
            </svg>
        </div>

        <p class="profil-account-menu-title">Informasi & Layanan</p>
        <ul class="profil-account-menu">
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    Aksesibilitas
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    Notifikasi
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                    Bahasa & Tampilan
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Privasi
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Bantuan
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Izin Aplikasi & Website
                </a>
            </li>
        </ul>

        <p class="profil-account-menu-title">Login</p>
        <ul class="profil-account-menu">
            <li>
                <a href="#" class="profil-menu-add">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Tambah Akun
                </a>
            </li>
            <li>
                <a href="#" class="profil-menu-delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Hapus Akun
                </a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="profil-menu-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Keluar
                    </button>
                </form>
            </li>
        </ul>
    </div>
@endsection
