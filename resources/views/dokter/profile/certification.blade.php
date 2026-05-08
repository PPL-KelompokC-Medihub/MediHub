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
    <style>
        .field-error {
            color: #dc2626;
            font-size: 0.875rem;
            display: block;
            margin-bottom: 8px;
            margin-top: -5px;
        }
        .field-error i {
            margin-right: 4px;
        }
        .file-preview-box {
            margin-bottom: 15px;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
        }
        .file-preview-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .file-preview-icon {
            font-size: 1.5rem;
            color: #10b981;
        }
        .file-preview-info {
            flex: 1;
        }
        .file-preview-name {
            font-weight: 600;
            color: #065f46;
            font-size: 0.95rem;
            margin-bottom: 3px;
        }
        .file-preview-status {
            font-size: 0.85rem;
            color: #059669;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .file-preview-action {
            text-align: right;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .file-preview-link {
            display: inline-block;
            padding: 6px 12px;
            background: #10b981;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .file-preview-link:hover {
            background: #059669;
        }
        .file-update-toggle {
            display: inline-block;
            padding: 6px 12px;
            background: #e5e7eb;
            color: #374151;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .file-update-toggle:hover {
            background: #d1d5db;
        }
        .file-update-section {
            display: none;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
        }
        .file-update-section.show {
            display: block;
        }
    </style>
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

        @if ($errors->has('files'))
            <div class="form-alert form-alert-error">{{ $errors->first('files') }}</div>
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

            <div id="uploadSizeWarning" class="form-alert form-alert-error" hidden></div>

            <section class="form-section">
                <h2 class="section-title">Dokumen</h2>

                <div class="upload-grid">
                    {{-- STR --}}
                    <div class="upload-field">
                        <label for="str_document">STR (Surat Tanda Registrasi)*</label>
                        @error('str_document')
                            <span class="field-error">
                                <i class="fa-solid fa-exclamation-circle"></i>{{ $message }}
                            </span>
                        @enderror
                        @if(!blank($user['STR'] ?? null))
                            <div class="file-preview-box">
                                <div class="file-preview-header">
                                    <div class="file-preview-icon">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="file-preview-info">
                                        <div class="file-preview-name">STR Document</div>
                                        <div class="file-preview-status">
                                            <i class="fa-solid fa-check-circle"></i>
                                            Sudah ter-upload
                                        </div>
                                    </div>
                                    <div class="file-preview-action">
                                        <a href="{{ asset('storage/' . $user['STR']) }}" target="_blank" class="file-preview-link">
                                            <i class="fa-solid fa-eye"></i> Lihat
                                        </a>
                                        <button type="button" class="file-update-toggle" onclick="toggleUpdate(event, 'str_update')">
                                            Ganti File
                                        </button>
                                    </div>
                                </div>
                                <div class="file-update-section" id="str_update">
                                    <label class="upload-box" for="str_document" style="margin: 0;">
                                        <input id="str_document" name="str_document" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Pilih File Baru <strong>browse</strong></span>
                                        <small>PDF atau PNG, Max 2 MB</small>
                                    </label>
                                </div>
                            </div>
                        @else
                            <label class="upload-box" for="str_document">
                                <input id="str_document" name="str_document" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah STR <strong>browse</strong></span>
                                <small>PDF atau PNG, Max 2 MB</small>
                            </label>
                        @endif
                    </div>

                    {{-- SIP --}}
                    <div class="upload-field">
                        <label for="sip_document">SIP (Surat Izin Praktik)*</label>
                        @error('sip_document')
                            <span class="field-error">
                                <i class="fa-solid fa-exclamation-circle"></i>{{ $message }}
                            </span>
                        @enderror
                        @if(!blank($user['SIP'] ?? null))
                            <div class="file-preview-box">
                                <div class="file-preview-header">
                                    <div class="file-preview-icon">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="file-preview-info">
                                        <div class="file-preview-name">SIP Document</div>
                                        <div class="file-preview-status">
                                            <i class="fa-solid fa-check-circle"></i>
                                            Sudah ter-upload
                                        </div>
                                    </div>
                                    <div class="file-preview-action">
                                        <a href="{{ asset('storage/' . $user['SIP']) }}" target="_blank" class="file-preview-link">
                                            <i class="fa-solid fa-eye"></i> Lihat
                                        </a>
                                        <button type="button" class="file-update-toggle" onclick="toggleUpdate(event, 'sip_update')">
                                            Ganti File
                                        </button>
                                    </div>
                                </div>
                                <div class="file-update-section" id="sip_update">
                                    <label class="upload-box" for="sip_document" style="margin: 0;">
                                        <input id="sip_document" name="sip_document" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Pilih File Baru <strong>browse</strong></span>
                                        <small>PDF atau PNG, Max 2 MB</small>
                                    </label>
                                </div>
                            </div>
                        @else
                            <label class="upload-box" for="sip_document">
                                <input id="sip_document" name="sip_document" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah SIP <strong>browse</strong></span>
                                <small>PDF atau PNG, Max 2 MB</small>
                            </label>
                        @endif
                    </div>

                    {{-- Ijazah Dokter --}}
                    <div class="upload-field">
                        <label for="ijazah_doctor">Ijazah Dokter*</label>
                        @error('ijazah_doctor')
                            <span class="field-error">
                                <i class="fa-solid fa-exclamation-circle"></i>{{ $message }}
                            </span>
                        @enderror
                        @if(!blank($user['ijazah_doctor'] ?? null))
                            <div class="file-preview-box">
                                <div class="file-preview-header">
                                    <div class="file-preview-icon">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="file-preview-info">
                                        <div class="file-preview-name">Ijazah Dokter</div>
                                        <div class="file-preview-status">
                                            <i class="fa-solid fa-check-circle"></i>
                                            Sudah ter-upload
                                        </div>
                                    </div>
                                    <div class="file-preview-action">
                                        <a href="{{ asset('storage/' . $user['ijazah_doctor']) }}" target="_blank" class="file-preview-link">
                                            <i class="fa-solid fa-eye"></i> Lihat
                                        </a>
                                        <button type="button" class="file-update-toggle" onclick="toggleUpdate(event, 'ijazah_update')">
                                            Ganti File
                                        </button>
                                    </div>
                                </div>
                                <div class="file-update-section" id="ijazah_update">
                                    <label class="upload-box" for="ijazah_doctor" style="margin: 0;">
                                        <input id="ijazah_doctor" name="ijazah_doctor" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Pilih File Baru <strong>browse</strong></span>
                                        <small>PDF atau PNG, Max 2 MB</small>
                                    </label>
                                </div>
                            </div>
                        @else
                            <label class="upload-box" for="ijazah_doctor">
                                <input id="ijazah_doctor" name="ijazah_doctor" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah File <strong>browse</strong></span>
                                <small>PDF atau PNG, Max 2 MB</small>
                            </label>
                        @endif
                    </div>

                    {{-- KTP / Identitas Resmi --}}
                    <div class="upload-field">
                        <label for="ktp_document">KTP / Identitas Resmi*</label>
                        @error('ktp_document')
                            <span class="field-error">
                                <i class="fa-solid fa-exclamation-circle"></i>{{ $message }}
                            </span>
                        @enderror
                        @if(!blank($user['KTP'] ?? null))
                            <div class="file-preview-box">
                                <div class="file-preview-header">
                                    <div class="file-preview-icon">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="file-preview-info">
                                        <div class="file-preview-name">KTP / Identitas Resmi</div>
                                        <div class="file-preview-status">
                                            <i class="fa-solid fa-check-circle"></i>
                                            Sudah ter-upload
                                        </div>
                                    </div>
                                    <div class="file-preview-action">
                                        <a href="{{ asset('storage/' . $user['KTP']) }}" target="_blank" class="file-preview-link">
                                            <i class="fa-solid fa-eye"></i> Lihat
                                        </a>
                                        <button type="button" class="file-update-toggle" onclick="toggleUpdate(event, 'ktp_update')">
                                            Ganti File
                                        </button>
                                    </div>
                                </div>
                                <div class="file-update-section" id="ktp_update">
                                    <label class="upload-box" for="ktp_document" style="margin: 0;">
                                        <input id="ktp_document" name="ktp_document" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Pilih File Baru <strong>browse</strong></span>
                                        <small>PDF atau PNG, Max 2 MB</small>
                                    </label>
                                </div>
                            </div>
                        @else
                            <label class="upload-box" for="ktp_document">
                                <input id="ktp_document" name="ktp_document" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah File <strong>browse</strong></span>
                                <small>PDF atau PNG, Max 2 MB</small>
                            </label>
                        @endif
                    </div>

                    {{-- Foto Profil Profesional --}}
                    <div class="upload-field">
                        <label for="profile_pict">Foto Profil Profesional*</label>
                        @error('profile_pict')
                            <span class="field-error">
                                <i class="fa-solid fa-exclamation-circle"></i>{{ $message }}
                            </span>
                        @enderror
                        @if(!blank($user['profile_pict'] ?? null))
                            <div class="file-preview-box">
                                <div class="file-preview-header">
                                    <div class="file-preview-icon">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                    <div class="file-preview-info">
                                        <div class="file-preview-name">Foto Profil</div>
                                        <div class="file-preview-status">
                                            <i class="fa-solid fa-check-circle"></i>
                                            Sudah ter-upload
                                        </div>
                                    </div>
                                    <div class="file-preview-action">
                                        <a href="{{ asset('storage/' . $user['profile_pict']) }}" target="_blank" class="file-preview-link">
                                            <i class="fa-solid fa-eye"></i> Lihat
                                        </a>
                                        <button type="button" class="file-update-toggle" onclick="toggleUpdate(event, 'profile_update')">
                                            Ganti File
                                        </button>
                                    </div>
                                </div>
                                <div class="file-update-section" id="profile_update">
                                    <label class="upload-box" for="profile_pict" style="margin: 0;">
                                        <input id="profile_pict" name="profile_pict" type="file" accept=".png,.jpg,.jpeg,.webp">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Pilih File Baru <strong>browse</strong></span>
                                        <small>JPG, PNG atau WebP, Max 2 MB</small>
                                    </label>
                                </div>
                            </div>
                        @else
                            <label class="upload-box" for="profile_pict">
                                <input id="profile_pict" name="profile_pict" type="file" accept=".png,.jpg,.jpeg,.webp">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah File <strong>browse</strong></span>
                                <small>JPG, PNG atau WebP, Max 2 MB</small>
                            </label>
                        @endif
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h2 class="section-title">Sertifikasi (Min. 1, Maks. 6)</h2>

                @error('certifications')
                    <span class="field-error" style="margin-bottom: 15px;">
                        <i class="fa-solid fa-exclamation-circle"></i>{{ $message }}
                    </span>
                @enderror

                <div class="upload-grid">
                    @for ($index = 1; $index <= 6; $index++)
                        @php
                            $certField = 'certification' . $index;
                            $hasCert = !blank($user[$certField] ?? null);
                            $certIndex = $index - 1; 
                        @endphp
                        <div class="upload-field">
                            <label for="certification_{{ $index }}">Sertifikat {{ $index }}{{ $index === 1 ? '*' : '' }}</label>
                            @if($hasCert)
                                <div class="file-preview-box">
                                    <div class="file-preview-header">
                                        <div class="file-preview-icon">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div class="file-preview-info">
                                            <div class="file-preview-name">Sertifikat {{ $index }}</div>
                                            <div class="file-preview-status">
                                                <i class="fa-solid fa-check-circle"></i>
                                                Sudah ter-upload
                                            </div>
                                        </div>
                                        <div class="file-preview-action">
                                            <a href="{{ asset('storage/' . $user[$certField]) }}" target="_blank" class="file-preview-link">
                                                <i class="fa-solid fa-eye"></i> Lihat
                                            </a>
                                            <button type="button" class="file-update-toggle" onclick="toggleUpdate(event, 'cert_update_{{ $index }}')">
                                                Ganti File
                                            </button>
                                        </div>
                                    </div>
                                    <div class="file-update-section" id="cert_update_{{ $index }}">
                                        <label class="upload-box" for="certification_{{ $index }}" style="margin: 0;">
                                            {{-- Pakai index eksplisit agar posisi slot diketahui controller --}}
                                            <input id="certification_{{ $index }}" name="certifications[{{ $certIndex }}]" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                            <span>Pilih File Baru <strong>browse</strong></span>
                                            <small>PDF atau PNG, Max 2 MB</small>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <label class="upload-box" for="certification_{{ $index }}">
                                    {{-- Slot baru juga pakai index eksplisit --}}
                                    <input id="certification_{{ $index }}" name="certifications[{{ $certIndex }}]" type="file" accept=".pdf,.png,.jpg,.jpeg">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Unggah File <strong>browse</strong></span>
                                    <small>PDF atau PNG, Max 2 MB</small>
                                </label>
                            @endif
                        </div>
                    @endfor
                </div>
            </section>

            <div class="footer-action">
                <button type="submit" class="btn-primary">Selanjutnya</button>
            </div>
        </form>
    </main>

    <script>
        const certificationForm = document.getElementById('doctorCertificationForm');
        const uploadSizeWarning = document.getElementById('uploadSizeWarning');
        const maxFileSize = 2 * 1024 * 1024;
        const maxTotalSize = 8 * 1024 * 1024;

        function formatFileSize(bytes) {
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }

        function showUploadWarning(message) {
            if (uploadSizeWarning) {
                uploadSizeWarning.textContent = message;
                uploadSizeWarning.hidden = false;
                uploadSizeWarning.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            alert(message);
        }

        function toggleUpdate(event, sectionId) {
            event.preventDefault();
            const section = document.getElementById(sectionId);
            section.classList.toggle('show');
            const button = event.target.closest('.file-update-toggle');
            button.textContent = section.classList.contains('show') ? 'Batal' : 'Ganti File';
        }

        certificationForm?.addEventListener('submit', (event) => {
            const selectedFiles = Array.from(certificationForm.querySelectorAll('input[type="file"]'))
                .flatMap((input) => Array.from(input.files || []));

            const oversizedFile = selectedFiles.find((file) => file.size > maxFileSize);
            if (oversizedFile) {
                event.preventDefault();
                showUploadWarning(
                    `File "${oversizedFile.name}" berukuran ${formatFileSize(oversizedFile.size)}, melebihi batas 2 MB. Kompres atau pilih file yang lebih kecil.`
                );
                return;
            }

            const totalSize = selectedFiles.reduce((total, file) => total + file.size, 0);
            if (totalSize > maxTotalSize) {
                event.preventDefault();
                showUploadWarning(
                    `Total ukuran dokumen ${formatFileSize(totalSize)}, melebihi batas 8 MB. Kurangi jumlah file atau kompres dokumen terlebih dahulu.`
                );
                return;
            }

            if (uploadSizeWarning) {
                uploadSizeWarning.hidden = true;
                uploadSizeWarning.textContent = '';
            }
        });
    </script>
</body>

</html>
