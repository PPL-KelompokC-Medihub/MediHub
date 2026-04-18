@extends('layouts.auth')

@section('title', 'Login Dokter - MediHub')
@section('auth_page_class', 'mediq-signin-page')

@section('content')
    <div class="mediq-auth-body">
        <h1 class="mediq-auth-heading">Selamat Datang Kembali, Dokter!</h1>
        <p class="mediq-auth-subtitle">Masuk ke akun Anda untuk mengelola jadwal dan pasien.</p>

        <p class="mediq-auth-switch">Belum memiliki akun? <a href="{{ route('register-dokter') }}">Daftar sekarang</a></p>

        <form id="firebase-sign-in-form" class="mediq-auth-form" method="POST" action="#">
            @csrf

            <div class="mediq-field">
                <label class="mediq-label" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="mediq-input"
                    placeholder="dokter@medihub.com" required autofocus>
            </div>

            <div class="mediq-field">
                <label class="mediq-label" for="password">Kata Sandi</label>
                <div class="mediq-input-wrap">
                    <input id="password" name="password" type="password" class="mediq-input"
                        placeholder="Masukkan kata sandi" required>
                    <button type="button" class="mediq-eye-btn" id="toggle-password" aria-label="Tampilkan kata sandi">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mediq-form-row-between">
                <label class="mediq-remember">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>
                <a href="#" class="mediq-forgot-link">Lupa kata sandi?</a>
            </div>

            @if ($errors->any())
                <div class="mediq-error">{{ $errors->first() }}</div>
            @endif

            <p id="firebase-auth-error" class="mediq-error" hidden></p>

            <button type="submit" id="sign-in-submit" class="mediq-primary-btn" style="margin-top: 14px;">
                <span class="btn-text">Masuk</span>
                <span class="btn-loading" hidden>Memproses...</span>
            </button>

            <p class="mediq-divider">Atau masuk dengan</p>

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
                <button type="button" id="facebook-sign-in-btn" class="mediq-social-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                        <path
                            d="M24 12c0-6.6-5.4-12-12-12S0 5.4 0 12c0 6 4.4 11 10.1 11.9v-8.4H7.1V12h3V9.4c0-3 1.8-4.6 4.5-4.6 1.3 0 2.7.2 2.7.2v2.9h-1.5c-1.5 0-2 .9-2 1.9V12h3.3l-.5 3.5h-2.8v8.4C19.6 23 24 18 24 12z" />
                    </svg>
                    Facebook
                </button>
            </div>

        </form>
    </div>
@endsection

@section('page-scripts')
    <script>
        // Toggle password visibility
        document.getElementById('toggle-password')?.addEventListener('click', function() {
            const input = document.getElementById('password');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            this.querySelector('svg').innerHTML = isPassword
                ? '<path d="M2.5 13c1.5 3 4.5 5 8.5 5s7-2 8.5-5M5 14l-1.5 2M9 16l-.5 2.5M13 16l.5 2.5M17 14l1.5 2" />'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        });
    </script>
@endsection
