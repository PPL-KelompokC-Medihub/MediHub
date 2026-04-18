<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHub - Profil Dokter</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/doctor/profile-form.css', 'resources/js/doctor/profile-form.js'])
</head>
<body>
    <div class="sidebar">
        <div class="illustration-container">
            <svg width="300" height="300" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="50" y="250" width="300" height="100" rx="20" transform="rotate(-15 50 250)" fill="#E0E0E0"/>
                <circle cx="200" cy="180" r="80" fill="#58A7F5" fill-opacity="0.2"/>
                <path d="M160 200 L240 200 L220 350 L180 350 Z" fill="#58A7F5"/>
                <circle cx="200" cy="150" r="40" fill="#FFDBAC"/>
                <rect x="180" y="190" width="40" height="100" fill="#FFFFFF"/>
                <path d="M170 190 Q200 180 230 190" stroke="#58A7F5" stroke-width="4"/>
            </svg>
            <p class="illustration-caption">[Ilustrasi Dokter MediHub]</p>
        </div>
    </div>

    <main class="main-content">
        <div class="logo">
            <span class="logo-text">MediHub</span>
            <i class="fa-solid fa-magnifying-glass logo-icon"></i>
        </div>

        <h1 class="header-title">Mohon Isi Informasi Data Diri Dokter dengan Lengkap</h1>
        <p class="header-subtitle">Informasi ini akan diverifikasi dan ditampilkan di profil dokter Anda</p>

        <div class="stepper">
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

        <form id="doctorProfileForm">
            <section class="form-section">
                <h2 class="section-title">Data Diri Dokter</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_name">Nama Lengkap*</label>
                        <input id="doctor_name" type="text" placeholder="Anita Cahyaningrum" value="Anita Cahyaningrum">
                    </div>
                    <div class="form-group">
                        <label for="doctor_age">Umur Dokter*</label>
                        <div class="input-wrapper">
                            <input id="doctor_age" type="number" placeholder="12">
                            <span class="input-unit">Tahun</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_email">Email*</label>
                        <input id="doctor_email" type="email" placeholder="anitacahya@gmail.com" value="anitacahya@gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="doctor_phone">Nomor HP*</label>
                        <input id="doctor_phone" type="tel" placeholder="+62 82537681253813" value="+62 82537681253813">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="doctor_weight">Berat Badan*</label>
                        <div class="input-wrapper">
                            <input id="doctor_weight" type="number" placeholder="12">
                            <span class="input-unit">kg</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="doctor_height">Tinggi Badan*</label>
                        <div class="input-wrapper">
                            <input id="doctor_height" type="number" placeholder="12">
                            <span class="input-unit">cm</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin*</label>
                    <div class="gender-container">
                        <button type="button" class="gender-option selected" data-gender="Perempuan">
                            <i class="fa-solid fa-venus gender-icon"></i>
                            <span class="gender-label">Perempuan</span>
                        </button>
                        <button type="button" class="gender-option" data-gender="Pria">
                            <i class="fa-solid fa-mars gender-icon"></i>
                            <span class="gender-label">Pria</span>
                        </button>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="address-header">
                    <h2 class="section-title section-title-inline">Alamat</h2>
                    <button type="button" class="edit-btn">
                        Edit <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                </div>

                <div class="address-grid">
                    <div class="address-info">
                        <span class="info-label">Negara</span>
                        <span class="info-value">Indonesia</span>
                    </div>
                    <div class="address-info">
                        <span class="info-label">Kota</span>
                        <span class="info-value">Bandung</span>
                    </div>
                    <div class="address-info">
                        <span class="info-label">Kode POS</span>
                        <span class="info-value">40111</span>
                    </div>
                </div>
            </section>

            <div class="footer-action">
                <button type="submit" class="btn-primary">Selanjutnya</button>
            </div>
        </form>
    </main>
</body>
</html>
