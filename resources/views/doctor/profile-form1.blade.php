<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHub - Profil Dokter</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/doctor/profile-form.css', 'resources/js/doctor/profile-form.js'])
</head>

<body class="doctor-ui-pending">
    <div class="sidebar">
        <div class="illustration-container">
            <img src="{{ asset('images/dokter-auth-illustration.png') }}" alt="Doctor Profile">
        </div>
    </div>

    <main class="main-content">
        <div class="logo" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div>
                <span class="logo-text">MediHub</span>
                <i class="fa-solid fa-magnifying-glass logo-icon"></i>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit"
                    style="background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>

        <h1 class="header-title">Mohon Isi Informasi Data Diri Dokter dengan Lengkap</h1>
        <p class="header-subtitle">Informasi ini akan diverifikasi dan ditampilkan di profil dokter Anda</p>

        @if (session('success'))
            <div class="form-alert form-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="stepper stepper--1">
            <div class="stepper-line"></div>
            <div class="step-item active">
                <div class="step-number">1</div>
                <div class="step-label">Data Diri Dokter</div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-label">Keahlian Dokter</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-label">Dokumen & Sertifikasi</div>
            </div>
        </div>

        <form id="doctorProfileForm" method="POST" action="{{ route('doctor.profile.update') }}">
            @csrf
            <input id="doctor_gender" name="gender" type="hidden"
                value="{{ old('gender', $user['gender'] ?? 'Perempuan') }}">

            <section class="form-section">
                <h2 class="section-title">Data Diri Dokter</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_name">Nama Lengkap*</label>
                        <input id="doctor_name" name="name" type="text" placeholder="Anita Cahyaningrum"
                            value="{{ old('name', $user['name'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="doctor_age">Umur Dokter*</label>
                        <div class="input-wrapper">
                            <input id="doctor_age" name="age" type="number" placeholder="12"
                                value="{{ old('age', $user['age'] ?? '') }}">
                            <span class="input-unit">Tahun</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_email">Email*</label>
                        <input id="doctor_email" name="email" type="email" placeholder="anitacahya@gmail.com"
                            value="{{ old('email', $user['email'] ?? '') }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="doctor_phone">Nomor HP*</label>
                        <input id="doctor_phone" name="phone" type="tel" placeholder="+62 82537681253813"
                            value="{{ old('phone', $user['phone'] ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_weight">Berat Badan*</label>
                        <div class="input-wrapper">
                            <input id="doctor_weight" name="weight" type="number" placeholder="12"
                                value="{{ old('weight', $user['weight'] ?? '') }}">
                            <span class="input-unit">kg</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="doctor_height">Tinggi Badan*</label>
                        <div class="input-wrapper">
                            <input id="doctor_height" name="height" type="number" placeholder="12"
                                value="{{ old('height', $user['height'] ?? '') }}">
                            <span class="input-unit">cm</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin*</label>
                    <div class="gender-container">
                        <button type="button"
                            class="gender-option {{ old('gender', $user['gender'] ?? 'Perempuan') === 'Perempuan' ? 'selected' : '' }}"
                            data-gender="Perempuan">
                            <i class="fa-solid fa-venus gender-icon"></i>
                            <span class="gender-label">Perempuan</span>
                        </button>
                        <button type="button"
                            class="gender-option {{ old('gender', $user['gender'] ?? '') === 'Pria' ? 'selected' : '' }}"
                            data-gender="Pria">
                            <i class="fa-solid fa-mars gender-icon"></i>
                            <span class="gender-label">Pria</span>
                        </button>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="address-header">
                    <h2 class="section-title section-title-inline">Alamat</h2>
                    <span class="edit-btn is-static">Lengkapi alamat dokter</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_country">Negara*</label>
                        <input id="doctor_country" name="country" type="text" placeholder="Indonesia"
                            value="{{ old('country', $user['country'] ?? 'Indonesia') }}">
                    </div>
                    <div class="form-group">
                        <label for="doctor_city">Kota*</label>
                        <input id="doctor_city" name="city" type="text" placeholder="Bandung"
                            value="{{ old('city', $user['city'] ?? '') }}">
                    </div>
                </div>
                <div class="form-row form-row-single">
                    <div class="form-group">
                        <label for="doctor_postal_code">Kode POS*</label>
                        <input id="doctor_postal_code" name="postal_code" type="text" placeholder="40111"
                            value="{{ old('postal_code', $user['postal_code'] ?? '') }}">
                    </div>
                </div>
            </section>

            <div class="footer-action">
                <button type="submit" class="btn-primary">
                    {{ $profileCompleted ? 'Perbarui Data Diri Dokter' : 'Simpan & Lanjut ke Keahlian Dokter' }}
                </button>
            </div>
        </form>
    </main>
</body>

</html>
