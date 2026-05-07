<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHub - Sertifikasi Dokter</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/dokter/profile-form.css', 'resources/js/dokter/profile-form.js'])
</head>

<body class="doctor-ui-pending">
    <div class="sidebar">
        <div class="illustration-container">
            <img src="{{ asset('images/dokter-auth-illustration.png') }}" alt="Doctor Certification">
        </div>
    </div>

    <main class="main-content">
        <div class="logo profile-logo-row">
            <div>
                <img src="{{ asset('images/medihub-logo.png') }}" alt="MediHub" class="profile-logo-image" />
            </div>
            <form method="POST" action="{{ route('logout') }}" class="profile-logout-form">
                @csrf
                <button type="submit" class="profile-logout-button">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>

        <h1 class="header-title">Mohon Unggah Dokumen dan Sertifikasi yang Dibutuhkan</h1>
        <p class="header-subtitle">Wajib di isi sebagai syarat verifikasi keaslian data dokter</p>

        @if (session('success'))
            <div class="form-alert form-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="stepper stepper--3">
            <div class="stepper-line"></div>
            <div class="step-item active">
                <div class="step-number">1</div>
                <div class="step-label">Data Diri Dokter</div>
            </div>
            <div class="step-item active">
                <div class="step-number">2</div>
                <div class="step-label">Keahlian Dokter</div>
            </div>
            <div class="step-item active">
                <div class="step-number">3</div>
                <div class="step-label">Dokumen & Sertifikasi</div>
            </div>
        </div>

        <form id="doctorCertificationForm" method="POST" action="{{ route('dokter.profile.certification.update') }}"
            enctype="multipart/form-data">
            @csrf

            <section class="form-section">
                <h2 class="section-title">Dokumen</h2>

                <div class="upload-grid">
                    <div class="upload-field">
                        <label for="str_document">STR (Surat Tanda Registrasi)*</label>
                        <label class="upload-box" for="str_document">
                            <input id="str_document" name="str_document" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Unggah STR <strong>browse</strong></span>
                            <small>PDF atau PNG, Max 10 MB</small>
                        </label>
                    </div>

                    <div class="upload-field">
                        <label for="sip_document">SIP (Surat Izin Praktik)*</label>
                        <label class="upload-box" for="sip_document">
                            <input id="sip_document" name="sip_document" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Unggah SIP <strong>browse</strong></span>
                            <small>PDF atau PNG, Max 10 MB</small>
                        </label>
                    </div>

                    <div class="upload-field">
                        <label for="ijazah_doctor">Ijazah Dokter*</label>
                        <label class="upload-box" for="ijazah_doctor">
                            <input id="ijazah_doctor" name="ijazah_doctor" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Unggah File <strong>browse</strong></span>
                            <small>PDF atau PNG, Max 10 MB</small>
                        </label>
                    </div>

                    <div class="upload-field">
                        <label for="ktp_document">KTP / Identitas Resmi*</label>
                        <label class="upload-box" for="ktp_document">
                            <input id="ktp_document" name="ktp_document" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Unggah File <strong>browse</strong></span>
                            <small>PDF atau PNG, Max 10 MB</small>
                        </label>
                    </div>

                    <div class="upload-field">
                        <label for="profile_pict">Foto Profil Profesional*</label>
                        <label class="upload-box" for="profile_pict">
                            <input id="profile_pict" name="profile_pict" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Unggah File <strong>browse</strong></span>
                            <small>PDF atau PNG, Max 10 MB</small>
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h2 class="section-title">Sertifikasi (Min. 1, Maks. 6)</h2>

                <div class="upload-grid">
                    @for ($index = 1; $index <= 6; $index++)
                        <div class="upload-field">
                            <label for="certification_{{ $index }}">Sertifikat {{ $index }}{{ $index === 1 ? '*' : '' }}</label>
                            <label class="upload-box" for="certification_{{ $index }}">
                                <input id="certification_{{ $index }}" name="certifications[]" type="file"
                                    accept=".pdf,.png,.jpg,.jpeg" {{ $index === 1 ? 'required' : '' }}>
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah File <strong>browse</strong></span>
                                <small>PDF atau PNG, Max 10 MB</small>
                            </label>
                        </div>
                    @endfor
                </div>
            </section>

            <div class="footer-action">
                <button type="submit" class="btn-primary">Selanjutnya</button>
            </div>
        </form>
    </main>
</body>

</html>
