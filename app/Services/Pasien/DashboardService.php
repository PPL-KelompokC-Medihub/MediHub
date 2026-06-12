<?php

namespace App\Services\Pasien;

use App\Models\MedicalNote;
use App\Services\FirestoreService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';

    private const DOCTOR_COLLECTION = 'Dokter';

    private const DOCTOR_SPECIALIZATION_COLLECTION = 'Dokter_spesialisasi';

    private const USERS_COLLECTION = 'Users';

    private const PATIENT_COLLECTION = 'Pasien';
    private const REVIEW_COLLECTION = 'Ulasan';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        $categories = $this->categories();
        $doctors = $this->doctors();
        $facilities = $this->facilities();
        $appointments = $this->appointments();
        $patient = $this->patient();
        $notifications = $this->notifications();

        return compact('categories', 'doctors', 'facilities', 'appointments', 'patient', 'notifications');
    }

    private function patient(): array
    {
        $userId = (string) Auth::id();

        $patients = $this->firestore->all(self::PATIENT_COLLECTION);

        $patient = collect($patients)->first(function (array $patient) use ($userId): bool {
            return (string) ($patient['user_id'] ?? '') === $userId
                || (string) ($patient['id'] ?? '') === $userId;
        });

        if (! $patient) {
            return [
                'fullname' => Auth::user()?->name ?? 'Pasien',
                'profile_pict' => asset('images/default-avatar.svg'),
            ];
        }

        $profilePict = $patient['profile_pict'] ?? null;

        $patient['profile_pict'] = $profilePict
            ? asset('storage/'.$profilePict)
            : asset('images/default-avatar.svg');

        return $patient;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function appointments(): array
    {
        $patientId = (string) Auth::id();
        $patientEmail = (string) (Auth::user()?->email ?? '');
        $doctorSummaries = $this->doctorSummariesById();

        $appointments = array_values(array_filter(
            $this->firestore->all(self::APPOINTMENT_COLLECTION),
            function (array $appointment) use ($patientId, $patientEmail): bool {
                $belongsToPatient = in_array($patientId, [
                    (string) ($appointment['patient_id'] ?? ''),
                    (string) ($appointment['user_uid'] ?? ''),
                ], true) || (
                    $patientEmail !== '' &&
                    $patientEmail === (string) ($appointment['patient_email'] ?? '')
                );

                if (! $belongsToPatient) {
                    return false;
                }

                $status = strtolower((string) ($appointment['status'] ?? ''));

                return ! in_array($status, ['batal', 'dibatalkan', 'selesai'], true);
            },
        ));

        usort($appointments, fn (array $a, array $b): int => strcmp(
            trim((string) ($a['appointment_date'] ?? '').' '.(string) ($a['appointment_time'] ?? '')),
            trim((string) ($b['appointment_date'] ?? '').' '.(string) ($b['appointment_time'] ?? '')),
        ));

        return array_map(function (array $appointment) use ($doctorSummaries): array {
            $date = (string) ($appointment['appointment_date'] ?? '');
            $doctorId = (string) ($appointment['doctor_id'] ?? $appointment['dokterid'] ?? '');
            $doctor = $doctorSummaries[$doctorId] ?? ['name' => 'Dokter', 'specialization' => 'Jadwal Temu'];
            $timeStart = (string) ($appointment['appointment_time_start'] ?? $appointment['appointment_time'] ?? '');
            $timeEnd = (string) ($appointment['appointment_time_end'] ?? '');

            return [

                'appointment_id' => $appointment['id'],

                'hari' => $this->appointmentDayLabel($date),
                'jenis' => $doctor['specialization'].' - '.$doctor['name'],
                'rs' => 'RS Medic Center - Bandung',

                'antrian' => (string) ($appointment['queue_number'] ?? '-'),

                'tanggal' => $this->appointmentDateLabel($date),
                'jam' => trim($timeStart.($timeEnd !== '' ? ' - '.$timeEnd : '')),
            ];
        }, $appointments);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function notifications(): array
    {
        $patientId = (string) Auth::id();

        $notifications = array_values(array_filter(
            $this->firestore->all('Notifications'),
            function (array $notification) use ($patientId): bool {
                return (string) ($notification['patient_id'] ?? '') === $patientId;
            }
        ));

        usort($notifications, function ($a, $b) {
            return strcmp(
                (string) ($b['created_at'] ?? ''),
                (string) ($a['created_at'] ?? '')
            );
        });

        return array_map(function (array $notification): array {
            return [
                'title' => $notification['title'] ?? 'Notifikasi',
                'message' => $notification['message'] ?? '',
                'date' => $notification['created_at'] ?? now()->toDateString(),
                'type' => $notification['type'] ?? 'info',
            ];
        }, $notifications);
    }

    /**
     * @return array<string, array{name: string, specialization: string}>
     */
    private function doctorSummariesById(): array
    {
        $users = [];
        foreach ($this->firestore->all(self::USERS_COLLECTION) as $user) {
            $users[(string) ($user['id'] ?? '')] = $user;
        }

        $specializations = [];
        foreach ($this->firestore->all(self::DOCTOR_SPECIALIZATION_COLLECTION) as $specialization) {
            $doctorId = (string) ($specialization['dokterid'] ?? '');

            if ($doctorId !== '') {
                $specializations[$doctorId] = $specialization['service']
                    ?? $specialization['main_specialization']
                    ?? 'Jadwal Temu';
            }
        }

        $documentDocuments = $this->firestore->all('Dokter_dokumen');
        $documents = [];
        foreach ($documentDocuments as $doc) {
            $docId = (string) ($doc['dokterid'] ?? '');
            if ($docId !== '') {
                $documents[$docId] = $doc;
            }
        }

        $doctorImages = [
            asset('images/dr-clara.png'),
            asset('images/dr-ariel.png'),
            asset('images/dr-ratna.png'),
            asset('images/dr-andini.png'),
        ];
        $fallbackIndex = 0;

        $summaries = [];
        foreach ($this->firestore->all(self::DOCTOR_COLLECTION) as $doctor) {
            $doctorId = (string) ($doctor['id'] ?? '');
            $userId = (string) ($doctor['usersId'] ?? '');
            $user = $users[$userId] ?? [];

            if ($doctorId !== '') {
                $foto = isset($documents[$doctorId]['profile_pict'])
                    ? asset('storage/'.$documents[$doctorId]['profile_pict'])
                    : $doctorImages[$fallbackIndex % count($doctorImages)];
                $fallbackIndex++;

                $summaries[$doctorId] = [
                    'name' => 'dr. '.($user['fullname'] ?? $doctor['email'] ?? 'Dokter'),
                    'specialization' => $specializations[$doctorId] ?? 'Jadwal Temu',
                    'foto' => $foto,
                ];
            }
        }

        return $summaries;
    }

    private function appointmentDayLabel(string $date): string
    {
        if ($date === '') {
            return 'Jadwal Temu';
        }

        $appointmentDate = Carbon::parse($date)->startOfDay();

        if ($appointmentDate->isToday()) {
            return 'Hari ini';
        }

        if ($appointmentDate->isTomorrow()) {
            return 'Besok';
        }

        return $appointmentDate->translatedFormat('l');
    }

    private function appointmentDateLabel(string $date): string
    {
        return $date === ''
            ? '-'
            : Carbon::parse($date)->translatedFormat('d F Y');
    }

    /**
     * Check if appointment belongs to currently logged-in patient
     */
    private function belongsToCurrentPatient(array $appointment, string $patientId, string $patientEmail): bool
    {
        return in_array($patientId, [
            (string) ($appointment['patient_id'] ?? ''),
            (string) ($appointment['user_uid'] ?? ''),
        ], true) || (
            $patientEmail !== ''
            && $patientEmail === (string) ($appointment['patient_email'] ?? '')
        );
    }

    /**
     * Format single appointment for history display
     */
    private function formatHistoryAppointment(array $appointment, array $doctorSummaries, $medicalNotes = null, array $doctorUserUidMap = [], array $catatanMedisMap = []): array
    {
        $date = (string) ($appointment['appointment_date'] ?? '');
        $doctorId = (string) ($appointment['doctor_id'] ?? $appointment['dokterid'] ?? '');
        $doctor = $doctorSummaries[$doctorId] ?? [
            'name' => 'Dokter',
            'specialization' => 'Jadwal Temu',
            'foto' => asset('images/default-avatar.svg')
        ];
        $timeStart = (string) ($appointment['appointment_time_start'] ?? $appointment['appointment_time'] ?? '');
        $timeEnd = (string) ($appointment['appointment_time_end'] ?? '');
        $scheduleTime = trim((string) ($appointment['schedule_time_range'] ?? ''));

        $statusKey = $this->normalizeStatusForGrouping((string) ($appointment['status'] ?? ''));
        $displayStatus = match ($statusKey) {
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => trim((string) ($appointment['status'] ?? '')) !== ''
                ? ucfirst((string) ($appointment['status'] ?? ''))
                : 'Mendatang',
        };

        $formattedTime = $timeStart !== ''
            ? trim($timeStart.($timeEnd !== '' ? ' - '.$timeEnd : ''))
            : $scheduleTime;

        $appointmentId = (string) ($appointment['id'] ?? '');
        $catatan = $catatanMedisMap[$appointmentId] ?? null;

        $dbDiagnosa = $catatan['hasil_asesmen'] ?? null;
        $dbCatatan = $catatan['rekomendasi'] ?? null;
        $dbResep = $catatan['resep_obat'] ?? null;
        $pemeriksaanFisik = $catatan['hasil_observasi'] ?? null;
        $rencanaPenanganan = $catatan['kesimpulan'] ?? null;

        if ($medicalNotes && !$catatan) {
            $doctorUserUid = $doctorUserUidMap[$doctorId] ?? '';
            $matchingNote = $medicalNotes->first(function ($note) use ($doctorId, $doctorUserUid, $date) {
                $noteDocId = (string) $note->doctor_id;
                $isDoctorMatch = $noteDocId === $doctorId || ($doctorUserUid !== '' && $noteDocId === $doctorUserUid);
                return $isDoctorMatch && $note->created_at->toDateString() === $date;
            });

            if ($matchingNote) {
                $dbDiagnosa = $matchingNote->notes;
                $dbCatatan = $matchingNote->notes;
                $dbResep = $matchingNote->prescriptions->pluck('medications')->implode(', ');
            }
        }

        $diagnosa = $dbDiagnosa ?? $this->firstFilled($appointment, ['diagnosis', 'diagnosa', 'hasil_diagnosa', 'medical_diagnosis']);
        $catatanDokter = $dbCatatan ?? $this->firstFilled($appointment, ['medical_note', 'catatan_medis', 'doctor_note', 'notes']);
        $resepObat = $dbResep ?? $this->firstFilled($appointment, ['prescription', 'resep', 'resep_obat', 'medicine']);

        return [
            'id' => (string) ($appointment['id'] ?? ''),
            'jenis' => $doctor['specialization'] ?? 'Jadwal Temu',
            'rs' => 'RS Medic Center',
            'dokter' => $doctor['name'] ?? 'Dokter',
            'dokter_foto' => $doctor['foto'] ?? asset('images/default-avatar.svg'),
            'tanggal' => $date === '' ? '-' : Carbon::parse($date)->translatedFormat('d F Y'),
            'jam' => $formattedTime === '' ? '-' : $formattedTime,
            'antrian' => (string) ($appointment['queue_number'] ?? '-'),
            'status' => $displayStatus,
            'status_key' => $statusKey,
            'hari' => $this->appointmentDayLabel($date),
            'date_sort' => $date,
            'time_sort' => $timeStart,
            'keluhan' => $this->firstFilled($appointment, ['complaint', 'keluhan']),
            'alergi' => $appointment['allergy_history'] ?? null,
            'alasan_pembatalan' => $appointment['cancellation_reason'] ?? null,
            'pemeriksaan_fisik' => $pemeriksaanFisik,
            'diagnosa' => $diagnosa,
            'diagnosis_sementara' => $diagnosa,
            'rencana_penanganan' => $rencanaPenanganan,
            'catatan_medis' => $catatanDokter,
            'catatan_dokter' => $catatanDokter,
            'resep_obat' => $resepObat,
        ];
    }

    /**
     * Normalize status for grouping into history/upcoming
     */
    private function normalizeStatusForGrouping(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match (true) {
            in_array($normalized, ['selesai', 'done', 'completed'], true) => 'selesai',
            in_array($normalized, ['dibatalkan', 'batal', 'canceled', 'cancelled'], true) => 'dibatalkan',
            default => 'pending',
        };
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function isHistoricalAppointment(array $appointment): bool
    {
        $status = $this->normalizeStatusForGrouping((string) ($appointment['status'] ?? ''));

        return in_array($status, ['selesai', 'dibatalkan'], true);
    }

    /**
     * @param  array<string, mixed>  $appointment
     * @param  array<int, string>  $fields
     */
    private function firstFilled(array $appointment, array $fields): ?string
    {
        foreach ($fields as $field) {
            $value = trim((string) ($appointment[$field] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get history page data with appointments grouped into history and upcoming
     *
     * @return array<string, mixed>
     */
    public function historyPageData(): array
    {
        $patient = $this->patient();
        $patientId = (string) Auth::id();
        $patientEmail = (string) (Auth::user()?->email ?? '');
        $doctorSummaries = $this->doctorSummariesById();

        $appointments = array_values(array_filter(
            $this->firestore->all(self::APPOINTMENT_COLLECTION),
            fn (array $appointment): bool => $this->belongsToCurrentPatient($appointment, $patientId, $patientEmail),
        ));


        $doctorUserUidMap = [];
        foreach ($this->firestore->all(self::DOCTOR_COLLECTION) as $doc) {
            $docId = (string) ($doc['id'] ?? '');
            $userUid = (string) ($doc['usersId'] ?? '');
            if ($docId !== '' && $userUid !== '') {
                $doctorUserUidMap[$docId] = $userUid;
            }
        }

        $catatanMedisMap = [];
        try {
            foreach ($this->firestore->all('CatatanMedis') as $catatan) {
                $aptId = (string) ($catatan['appointment_id'] ?? '');
                if ($aptId !== '') {
                    $catatanMedisMap[$aptId] = $catatan;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to load CatatanMedis collection', ['message' => $e->getMessage()]);
        }

        $history = [];
        $upcoming = [];

        foreach ($appointments as $appointment) {
            $formatted = $this->formatHistoryAppointment($appointment, $doctorSummaries, null, $doctorUserUidMap, $catatanMedisMap);

            $status = $this->normalizeStatusForGrouping((string) ($appointment['status'] ?? ''));
            if ($status === 'selesai') {
                $history[] = $formatted;
            } else {
                $upcoming[] = $formatted;
            }
        }

        usort($history, fn (array $left, array $right): int => strcmp(
            trim((string) ($right['date_sort'] ?? '').' '.(string) ($right['time_sort'] ?? '')),
            trim((string) ($left['date_sort'] ?? '').' '.(string) ($left['time_sort'] ?? '')),
        ));
        usort($upcoming, fn (array $left, array $right): int => strcmp(
            trim((string) ($left['date_sort'] ?? '').' '.(string) ($left['time_sort'] ?? '')),
            trim((string) ($right['date_sort'] ?? '').' '.(string) ($right['time_sort'] ?? '')),
        ));

        return [
            'patient' => $patient,
            'historyBookings' => $history,
            'upcomingBookings' => $upcoming,
        ];
    }

    /**
     * Get diagnosis page data for patient — Hasil Diagnosa & Catatan Medis
     *
     * @return array<string, mixed>
     */
    public function diagnosisPageData(): array
    {
        $patient = $this->patient();
        $patientId = (string) Auth::id();
        $patientEmail = (string) (Auth::user()?->email ?? '');
        $doctorSummaries = $this->doctorSummariesById();

        $appointments = array_values(array_filter(
            $this->firestore->all(self::APPOINTMENT_COLLECTION),
            fn (array $appointment): bool => $this->belongsToCurrentPatient($appointment, $patientId, $patientEmail),
        ));

        // Only completed appointments (status = selesai)
        $completed = array_filter($appointments, function (array $appointment): bool {
            $status = $this->normalizeStatusForGrouping((string) ($appointment['status'] ?? ''));

            return $status === 'selesai';
        });

        $medicalNotes = $this->medicalNotesForPatient($patientId);

        $doctorUserUidMap = [];
        foreach ($this->firestore->all(self::DOCTOR_COLLECTION) as $doc) {
            $docId = (string) ($doc['id'] ?? '');
            $userUid = (string) ($doc['usersId'] ?? '');
            if ($docId !== '' && $userUid !== '') {
                $doctorUserUidMap[$docId] = $userUid;
            }
        }

        $catatanMedisMap = [];
        try {
            foreach ($this->firestore->all('CatatanMedis') as $catatan) {
                $aptId = (string) ($catatan['appointment_id'] ?? '');
                if ($aptId !== '') {
                    $catatanMedisMap[$aptId] = $catatan;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to load CatatanMedis collection', ['message' => $e->getMessage()]);
        }

        $now = Carbon::now();

        $diagnosisList = array_map(function (array $appointment) use ($doctorSummaries, $medicalNotes, $doctorUserUidMap, $catatanMedisMap, $now): array {
            $formatted = $this->formatHistoryAppointment($appointment, $doctorSummaries, $medicalNotes, $doctorUserUidMap, $catatanMedisMap);

            // Determine period key for front-end filtering
            $dateStr = trim((string) ($appointment['appointment_date'] ?? ''));
            $periodKey = 'lainnya';

            if ($dateStr !== '') {
                try {
                    $appointmentDate = Carbon::parse($dateStr);
                    $diffMonths = $appointmentDate->diffInMonths($now);

                    if ($appointmentDate->isSameMonth($now) && $appointmentDate->isSameYear($now)) {
                        $periodKey = 'bulan-ini';
                    } elseif ($diffMonths <= 3) {
                        $periodKey = '3-bulan';
                    } elseif ($diffMonths <= 6) {
                        $periodKey = '6-bulan';
                    }
                } catch (\Throwable) {
                    // keep default
                }
            }

            $formatted['period_key'] = $periodKey;

            return $formatted;
        }, array_values($completed));

        // Sort by date descending (newest first)
        usort($diagnosisList, fn (array $left, array $right): int => strcmp(
            trim((string) ($right['date_sort'] ?? '').' '.(string) ($right['time_sort'] ?? '')),
            trim((string) ($left['date_sort'] ?? '').' '.(string) ($left['time_sort'] ?? '')),
        ));

        // Stats
        $totalDiagnosa = count(array_filter($diagnosisList, fn (array $item): bool => ! empty($item['diagnosa'])));
        $totalResep = count(array_filter($diagnosisList, fn (array $item): bool => ! empty($item['resep_obat'])));

        // Recent 5 for sidebar timeline
        $recentDiagnosis = array_slice($diagnosisList, 0, 5);

        return [
            'patient' => $patient,
            'diagnosisList' => $diagnosisList,
            'recentDiagnosis' => $recentDiagnosis,
            'stats' => [
                'total_diagnosa' => $totalDiagnosa,
                'kunjungan_selesai' => count($diagnosisList),
                'total_resep' => $totalResep,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function servicePageData(): array
    {
        return array_merge($this->dashboardData(), [
            'hospital' => [
                'type' => 'Rumah Sakit',
                'name' => 'Medic Center',
                'operational_hour' => '07.00 - 21:00',
                'emergency_hour' => '24 JAM',
                'phone' => '(022) 5678 999',
                'address' => 'Jl. Merdeka No. 123, Bandung, Jawa Barat',
                'description' => 'modern dengan pelayanan kesehatan komprehensif yang berfokus pada kenyamanan pasien.',
                'description_support' => 'dan didukung oleh dokter spesialis berpengalaman di berbagai bidang serta tenaga medis profesional.',
                'vision' => 'Visi kami adalah menjadi mitra terpercaya dalam menjaga kesehatan keluarga dengan layanan yang cepat dan aman.',
            ],
            'featuredFacilities' => [
                [
                    'name' => 'IGD 24 Jam',
                    'description' => 'Layanan gawat darurat yang siap menerima pasien setiap saat.',
                    'icon' => 'fa-truck-medical',
                ],
                [
                    'name' => 'ICU',
                    'description' => 'Ruang perawatan intensif dengan pemantauan kondisi pasien secara ketat.',
                    'icon' => 'fa-heart-pulse',
                ],
                [
                    'name' => 'Laboratorium',
                    'description' => 'Pemeriksaan sampel darah, urin, dan penunjang diagnosis lainnya.',
                    'icon' => 'fa-flask-vial',
                ],
                [
                    'name' => 'Farmasi',
                    'description' => 'Penyediaan obat dan konsultasi penggunaan resep pasien.',
                    'icon' => 'fa-prescription-bottle-medical',
                ],
                [
                    'name' => 'Radiologi',
                    'description' => 'Pemeriksaan pencitraan medis untuk menunjang diagnosis dokter.',
                    'icon' => 'fa-x-ray',
                ],
                [
                    'name' => 'Rawat Inap',
                    'description' => 'Kamar perawatan nyaman untuk pasien yang membutuhkan observasi lanjutan.',
                    'icon' => 'fa-bed-pulse',
                ],
            ],
            'simpleFacilities' => [
                'IGD 24 Jam',
                'ICU',
                'Laboratorium',
                'Farmasi',
                'Radiologi',
            ],
            'facilityGallery' => [
                [
                    'title' => 'Gedung Utama',
                    'image' => asset('images/auth-hospital.jpg'),
                ],
                [
                    'title' => 'Area Layanan Digital',
                    'image' => asset('images/dashboard-mockup.png'),
                ],
                [
                    'title' => 'Ruang Konsultasi',
                    'image' => asset('images/hero-landing.png'),
                ],
            ],
            'reviews' => $this->reviews(),
        ]);
    }

    private function medicalNotesForPatient(string $patientId): Collection
    {
        if (! $this->canUseSqlMedicalRecords()) {
            return collect();
        }

        try {
            return MedicalNote::where('patient_id', $patientId)
                ->with('prescriptions')
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Skipping SQL medical notes for patient dashboard.', [
                'patient_id' => $patientId,
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    private function canUseSqlMedicalRecords(): bool
    {
        $connection = config('database.default');

        if ($connection !== 'sqlite') {
            return is_string($connection) && $connection !== '';
        }

        $database = config('database.connections.sqlite.database');

        return $database === ':memory:'
            || (is_string($database) && $database !== '' && file_exists($database));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function categories(): array
    {
        return [
            ['nama' => 'Umum', 'icon' => 'Umum.png', 'image' => 'service-umum.png', 'deskripsi' => 'Konsultasi keluhan umum dan pemeriksaan awal.'],
            ['nama' => 'Anak', 'icon' => 'Anak.png', 'image' => 'service-anak.png', 'deskripsi' => 'Layanan kesehatan anak, tumbuh kembang, dan imunisasi.'],
            ['nama' => 'Penyakit Dalam', 'icon' => 'Penyakit-dalam.png', 'image' => 'service-umum.png', 'deskripsi' => 'Penanganan penyakit organ dalam dan kondisi kronis.'],
            ['nama' => 'Bedah', 'icon' => 'Bedah.png', 'image' => 'service-umum.png', 'deskripsi' => 'Konsultasi tindakan bedah dan pemeriksaan lanjutan.'],
            ['nama' => 'Gigi & Mulut', 'icon' => 'Gigi & Mulut.png', 'image' => 'service-gigi.png', 'deskripsi' => 'Pemeriksaan gigi, mulut, dan perawatan dasar.'],
            ['nama' => 'Kandungan', 'icon' => 'Kandungan.png', 'image' => 'service-kandungan.png', 'deskripsi' => 'Layanan kehamilan, kandungan, dan kesehatan reproduksi.'],
            ['nama' => 'Jantung', 'icon' => 'Jantung.png', 'image' => 'service-umum.png', 'deskripsi' => 'Konsultasi kesehatan jantung dan pembuluh darah.'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function doctors(): array
    {
        $userDocuments = $this->firestore->all('Users');
        $doctorDocuments = $this->firestore->all('Dokter');
        $specializationDocuments = $this->firestore->all('Dokter_spesialisasi');
        $documentDocuments = $this->firestore->all('Dokter_dokumen');

        $users = [];
        foreach ($userDocuments as $user) {
            if (($user['role'] ?? null) === 'dokter') {
                $users[$user['id']] = $user;
            }
        }

        $specializations = [];
        foreach ($specializationDocuments as $specialization) {
            if (isset($specialization['dokterid'])) {
                $specializations[$specialization['dokterid']] = $specialization;
            }
        }

        $documents = [];
        foreach ($documentDocuments as $doc) {
            if (isset($doc['dokterid'])) {
                $documents[$doc['dokterid']] = $doc;
            }
        }

        $doctors = [];
        $doctorImages = [
            asset('images/dr-clara.png'),
            asset('images/dr-ariel.png'),
            asset('images/dr-ratna.png'),
            asset('images/dr-andini.png'),
        ];

        foreach ($doctorDocuments as $doctor) {
            $doctorId = $doctor['id'] ?? null;
            $userId = $doctor['usersId'] ?? null;

            if (! $doctorId || ! $userId) {
                continue;
            }

            $user = $users[$userId] ?? null;

            if (! $user) {
                continue;
            }

            $specialist = $specializations[$doctorId] ?? null;

            $doctors[] = [
                'id' => $doctorId,
                'nama' => 'dr. '.($user['fullname'] ?? $user['name'] ?? 'Tidak Diketahui'),
                'spesialis' => $specialist['service'] ?? 'Tidak Diketahui',
                'spesialis_key' => strtolower($specialist['service'] ?? 'Tidak Diketahui'),
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => isset($documents[$doctorId]['profile_pict'])
                    ? asset('storage/'.$documents[$doctorId]['profile_pict'])
                    : $doctorImages[count($doctors) % count($doctorImages)],
            ];
        }

        return $doctors ?: $this->fallbackDoctors();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackDoctors(): array
    {
        return [
            [
                'id' => null,
                'nama' => 'dr. Clara Wulandari, M.Ked',
                'spesialis' => 'Dokter Umum',
                'spesialis_key' => 'umum',
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => asset('images/dr-clara.png'),
            ],
            [
                'id' => null,
                'nama' => 'dr. Ratna Dewi Sp.A',
                'spesialis' => 'Spesialis Anak',
                'spesialis_key' => 'anak',
                'rating' => '4.9',
                'pasien' => '450+ Total Pasien',
                'foto' => asset('images/dr-ratna.png'),
            ],
            [
                'id' => null,
                'nama' => 'dr. Andini Pratama, Sp.PD',
                'spesialis' => 'Spesialis Penyakit Dalam',
                'spesialis_key' => 'penyakit dalam',
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => asset('images/dr-andini.png'),
            ],
            [
                'id' => null,
                'nama' => 'dr. Arief Nugroho, Sp.JP',
                'spesialis' => 'Spesialis Jantung dan Pembuluh Darah',
                'spesialis_key' => 'jantung',
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => asset('images/dr-ariel.png'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function facilities(): array
    {
        $facilities = $this->firestore->all('facilities');

        return $facilities ?: [
            [
                'name' => 'RS Medic Center',
                'type' => 'Rumah Sakit',
                'location' => 'Jl. Merdeka No. 123, Bandung',
                'image' => asset('images/auth-hospital.jpg'),
            ],
            [
                'name' => 'Laboratorium Terpadu',
                'type' => 'Fasilitas Pemeriksaan',
                'location' => 'Gedung utama lantai 2',
                'image' => asset('images/auth-hospital.png'),
            ],
            [
                'name' => 'Radiologi & Diagnostik',
                'type' => 'Fasilitas Penunjang',
                'location' => 'Area penunjang medis',
                'image' => asset('images/dashboard-mockup.png'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reviews(): array
    {
        $reviews = $this->firestore->all(self::REVIEW_COLLECTION);

        if ($reviews === []) {
            return [];
        }

        $reviews = array_values(array_filter($reviews, function (array $review): bool {
            return trim((string) ($review['text'] ?? $review['review'] ?? $review['comment'] ?? '')) !== '';
        }));

        $users = collect($this->firestore->all(self::USERS_COLLECTION))
            ->keyBy(fn (array $user): string => (string) ($user['id'] ?? ''));
        $patients = collect();

        foreach ($this->firestore->all(self::PATIENT_COLLECTION) as $patient) {
            foreach (['id', 'user_id'] as $key) {
                $patientKey = (string) ($patient[$key] ?? '');

                if ($patientKey !== '') {
                    $patients->put($patientKey, $patient);
                }
            }
        }

        usort($reviews, fn (array $a, array $b): int => strcmp(
            (string) ($b['created_at'] ?? $b['updated_at'] ?? $b['update_at'] ?? ''),
            (string) ($a['created_at'] ?? $a['updated_at'] ?? $a['update_at'] ?? ''),
        ));

        return array_map(function (array $review) use ($users, $patients): array {
            $patientId = (string) ($review['patient_id'] ?? $review['user_id'] ?? '');
            $user = $users->get($patientId, []);
            $patient = $patients->get($patientId, []);
            $profilePict = $patient['profile_pict'] ?? null;
            $createdAt = (string) ($review['created_at'] ?? $review['updated_at'] ?? $review['update_at'] ?? '');

            return [
                'id' => $review['id'] ?? null,
                'patient_id' => $patientId,
                'name' => (string) ($review['patient_name'] ?? $user['fullname'] ?? $user['name'] ?? 'Pasien'),
                'rating' => number_format((float) ($review['rating'] ?? 0), 1),
                'date' => $createdAt !== ''
                    ? Carbon::parse($createdAt)->translatedFormat('d F | H:i')
                    : '-',
                'text' => (string) ($review['text'] ?? $review['review'] ?? $review['comment'] ?? ''),
                'likes' => (int) ($review['likes'] ?? 0),
                'avatar' => $profilePict
                    ? asset('storage/' . $profilePict)
                    : asset('images/default-avatar.svg'),
            ];
        }, $reviews);
    }
}
