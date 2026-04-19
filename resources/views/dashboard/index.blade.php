@extends('layouts.landing')

@section('title', 'Dashboard - MediHub')

@section('content')
    <div style="background-color: var(--mediq-bg); min-height: 100vh; padding: 120px 0 60px;">
        <div class="mediq-container">
            <!-- Header Dashboard -->
            <div style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <h1 class="mediq-section-title" style="margin-bottom: 8px;">Dashboard</h1>
                    <p class="mediq-section-subtitle">Selamat datang kembali! Ini adalah kilasan data internal sistem.</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mediq-primary-btn" style="background-color: #ef4444; color: white;">
                        <span class="btn-text">Keluar (Logout)</span>
                    </button>
                </form>
            </div>

            <!-- Kartu Info -->
            <div class="mediq-features-grid">
                <!-- Data Dokter -->
                <div class="mediq-feature-card">
                    <div class="mediq-feature-icon" style="background-color: var(--mediq-primary); color: white;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="mediq-feature-title">Dokter Aktif</h3>
                    <p class="mediq-feature-desc">Mendapatkan <strong>{{ count($doctors) }}</strong> profil dokter teratas dari koleksi Firestore.</p>
                </div>

                <!-- Data Fasilitas -->
                <div class="mediq-feature-card">
                    <div class="mediq-feature-icon" style="background-color: var(--mediq-accent); color: white;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <h3 class="mediq-feature-title">Fasilitas Medis</h3>
                    <p class="mediq-feature-desc">Mendapatkan <strong>{{ count($facilities) }}</strong> unit fasilitas medis dari koleksi Firestore.</p>
                </div>
            </div>

        </div>
    </div>
@endsection
