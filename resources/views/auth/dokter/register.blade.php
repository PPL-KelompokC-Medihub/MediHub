@extends('layouts.auth')

@section('title', 'Sign Up - MediHub')
@section('auth_page_class', 'mediq-signup-page')

@section('content')
    <div class="mediq-auth-body">
        <h1 class="mediq-auth-heading">Mulai Perjalanan Sehatmu Bersama MediHub</h1>
        <p class="mediq-auth-subtitle">Daftarkan diri Anda dan nikmati kemudahan akses layanan kesehatan tanpa antre.</p>

        <p class="mediq-auth-switch">Sudah memiliki akun? <a href="{{ route('login-dokter') }}">Sign in</a></p>

        <form id="firebase-sign-up-form" class="mediq-auth-form" data-sign-in-url="{{ route('login-dokter') }}"
            data-register-url="{{ route('firebase.register') }}"
            data-firebase-api-key="{{ config('services.firebase.api_key') }}">
            @csrf
            <input type="hidden" name="role" value="dokter">

            <label class="mediq-label" for="name">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" class="mediq-input" placeholder="John Doe"
                required autofocus>

            <div class="mediq-field-block">
                <p class="mediq-label">Jenis Akun</p>
                <div class="mediq-role-fixed" aria-label="Role akun">Dokter</div>
            </div>

            <div class="mediq-field">
                <label class="mediq-label" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="mediq-input"
                    placeholder="medihub@gmail.com" required>
            </div>

            <div class="mediq-field">
                <label class="mediq-label" for="password">Kata Sandi</label>
                <div class="mediq-input-wrap">
                    <input id="password" name="password" type="password" class="mediq-input" placeholder="Buat kata sandi"
                        required>
                    <button type="button" class="mediq-eye-btn" id="toggle-password" aria-label="Tampilkan kata sandi">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M2.5 13c1.5 3 4.5 5 8.5 5s7-2 8.5-5M5 14l-1.5 2M9 16l-.5 2.5M13 16l.5 2.5M17 14l1.5 2" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mediq-password-strength" aria-live="polite">
                <div class="mediq-password-strength-bar">
                    <span id="password-strength-fill"></span>
                </div>
                <p id="password-strength-text">Kekuatan kata sandi: lemah</p>
            </div>

            <div class="mediq-password-rules">
                <label><input type="checkbox" id="rule-length" tabindex="-1" disabled> Kata sandi minimal 8 karakter</label>
                <label><input type="checkbox" id="rule-number" tabindex="-1" disabled> Kata sandi harus mengandung angka
                    (0-9)</label>
            </div>

            <div class="mediq-field">
                <label class="mediq-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                <div class="mediq-input-wrap">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="mediq-input"
                        placeholder="Masukkan kata sandi" required>
                    <button type="button" class="mediq-eye-btn" id="toggle-confirm"
                        aria-label="Tampilkan konfirmasi kata sandi">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M2.5 13c1.5 3 4.5 5 8.5 5s7-2 8.5-5M5 14l-1.5 2M9 16l-.5 2.5M13 16l.5 2.5M17 14l1.5 2" />
                        </svg>
                    </button>
                </div>
                <p id="password-match-hint" class="mediq-password-match-hint" hidden>Kata sandi belum sama.</p>
            </div>

            @if ($errors->any())
                <div class="mediq-error">{{ $errors->first() }}</div>
            @endif

            <p id="firebase-auth-error" class="mediq-error" hidden></p>

            <button type="submit" id="sign-up-submit" class="mediq-primary-btn mediq-signup-submit">
                <span class="btn-text">Buat Akun</span>
                <span class="btn-loading" hidden>Memproses...</span>
            </button>

            <p class="mediq-divider">Atau daftar dengan</p>

            <div class="mediq-social-row">
                <button type="button" id="google-sign-in-btn" class="mediq-social-btn">
                    <svg width="18" height="18" viewBox="0 0 48 48">
                        <path fill="#EA4335"
                            d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.9 2.5 30.4 0 24 0 14.6 0 6.7 5.5 2.7 13.5l7.9 6.2C12.7 13.3 17.9 9.5 24 9.5z" />
                        <path fill="#4285F4"
                            d="M46.1 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.4c-.5 2.8-2.2 5.2-4.6 6.8l7.2 5.6c4.2-3.9 6.6-9.6 6.6-16.4z" />
                        <path fill="#FBBC05"
                            d="M10.6 28.3a14.5 14.5 0 0 1 0-9.2L2.7 13C.5 17.5-.5 22.5.2 27.5c.5 3.3 1.7 6.4 3.5 9.1l6.9-5.3z" />
                        <path fill="#34A853"
                            d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.2-5.6c-2.2 1.5-5 2.4-8.2 2.4-6.1 0-11.3-3.8-13.3-9.3l-7.9 6.1C6.7 42.5 14.6 48 24 48z" />
                    </svg>
                    Google
                </button>
                <button type="button" id="facebook-sign-up-btn" class="mediq-social-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                        <path
                            d="M24 12c0-6.6-5.4-12-12-12S0 5.4 0 12c0 6 4.4 11 10.1 11.9v-8.4H7.1V12h3V9.4c0-3 1.8-4.6 4.5-4.6 1.3 0 2.7.2 2.7.2v2.9h-1.5c-1.5 0-2 .9-2 1.9V12h3.3l-.5 3.5h-2.8v8.4C19.6 23 24 18 24 12z" />
                    </svg>
                    Facebook
                </button>
            </div>

        </form>
    </div>

    <div id="sign-up-success-modal" class="mediq-success-overlay" hidden>
        <div class="mediq-success-card" role="dialog" aria-modal="true" aria-labelledby="sign-up-success-title">
            <div class="mediq-success-badge">
                <span class="mediq-success-check"></span>
            </div>
            <h2 id="sign-up-success-title" class="mediq-success-title">Akun berhasil dibuat!</h2>
            <button id="sign-up-success-login-btn" type="button" class="mediq-success-login-btn">Masuk</button>
        </div>
    </div>
@endsection

@section('page-scripts')
    @vite('resources/js/auth/dokter/sign-up.js')
@endsection
