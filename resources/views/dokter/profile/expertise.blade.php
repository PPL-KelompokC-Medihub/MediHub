<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHub - Keahlian Dokter</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/dokter/profile-form.css', 'resources/js/dokter/profile-expertise.js'])
</head>

<body class="doctor-ui-pending">
    <div class="sidebar">
        <div class="illustration-container">
            <img src="{{ asset('images/dokter-auth-illustration.png') }}" alt="Doctor Expertise">
        </div>
    </div>

    <main class="main-content">
        <div class="logo profile-logo-row">
            <div>
                <img src="{{ asset('images/medihub-logo.png') }}" alt="MediHub" class="profile-logo-image" />
            </div>
        </div>

        <h1 class="header-title">Mohon Isi Informasi Data Keahlian Anda</h1>
        <p class="header-subtitle">Spesialisasi dan pengalaman klinis</p>

        @if (session('success'))
            <div class="form-alert form-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="stepper stepper--2">
            <div class="stepper-line"></div>
            <div class="step-item active">
                <div class="step-number">1</div>
                <div class="step-label">Data Diri Dokter</div>
            </div>
            <div class="step-item active">
                <div class="step-number">2</div>
                <div class="step-label">Keahlian Dokter</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-label">Dokumen & Sertifikasi</div>
            </div>
        </div>

        <form id="doctorExpertiseForm" method="POST" action="{{ route('dokter.profile.expertise.update') }}">
            @csrf

            <section class="form-section">
                <h2 class="section-title">Keahlian Dokter</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="specialty">Spesialisasi Utama*</label>
                        <input id="specialty" name="specialty" type="text" placeholder="Pilih spesialisasi"
                            value="{{ old('specialty', $user['specialty'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="sub_specialty">Sub-spesialisasi</label>
                        <input id="sub_specialty" name="sub_specialty" type="text" placeholder="Contoh: Neonatologi"
                            value="{{ old('sub_specialty', $user['sub_specialty'] ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="started_practice_year">Tahun mulai praktik*</label>
                        <input id="started_practice_year" name="started_practice_year" type="number" placeholder="2020"
                            value="{{ old('started_practice_year', $user['started_practice_year'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="education_institution">Institusi Pendidikan*</label>
                        <input id="education_institution" name="education_institution" type="text"
                            placeholder="FKUI, Universitas Indonesia"
                            value="{{ old('education_institution', $user['education_institution'] ?? '') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Layanan Yang Ditawarkan*</label>
                    <div class="service-pill-grid">
                        @php
                            $selectedServices = old('services', $user['services'] ?? []);
                        @endphp
                        @foreach ($services as $service)
                            <label class="service-pill {{ in_array($service, $selectedServices, true) ? 'selected' : '' }}">
                                <input type="checkbox" name="services[]" value="{{ $service }}"
                                    {{ in_array($service, $selectedServices, true) ? 'checked' : '' }}>
                                <span>{{ $service }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Biografi Singkat*</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Tulis pengalaman klinis, fokus layanan, dan pendekatan medis Anda...">{{ old('bio', $user['bio'] ?? '') }}</textarea>
                </div>
            </section>

            <div class="footer-action">
                <button type="submit" class="btn-primary">Simpan & Lanjut ke Sertifikasi</button>
            </div>
        </form>
    </main>
</body>

</html>
