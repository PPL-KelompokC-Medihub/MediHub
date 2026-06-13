<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class SeedFirebaseDemo extends Command
{
    protected $signature = 'firebase:seed-demo
        {--skip-auth : Lewati pembuatan/update akun Firebase Auth}
        {--dry-run : Tampilkan rencana seed tanpa menyentuh Firebase}
        {--delete-demo : Hapus dokumen demo yang dikenal sebelum menulis ulang}';

    protected $description = 'Seed akun Firebase Auth dan data Firestore demo MediHub untuk presentasi';

    private const PASSWORD = 'Password123!';

    public function handle(FirestoreService $firestore): int
    {
        $this->info('Menyiapkan data demo Firebase MediHub...');

        $users = $this->users();
        if (! $this->option('dry-run') && ! $this->option('skip-auth')) {
            $this->seedAuthUsers($firestore->auth(), $users);
        }

        $data = $this->demoData($users);

        if ($this->option('dry-run')) {
            $this->describeSeedPlan($data);

            return self::SUCCESS;
        }

        if ($this->option('delete-demo')) {
            $this->deleteKnownDemoDocuments($firestore, $data);
        }

        $this->seedFirestore($firestore, $data);

        $this->newLine();
        $this->info('Data demo Firebase siap.');
        $this->line('Akun pasien : pasien.dummy@medihub.test / '.self::PASSWORD);
        $this->line('Akun dokter : dokter.dummy@medihub.test / '.self::PASSWORD);
        $this->line('Project     : '.(config('services.firebase.project_id') ?: 'lihat project_id di service account'));

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{uid: string, email: string, name: string, role: string, auth: bool}>
     */
    private function users(): array
    {
        return [
            'patient' => [
                'uid' => 'demo-pasien-dummy',
                'email' => 'pasien.dummy@medihub.test',
                'name' => 'Pasien Dummy',
                'role' => 'pasien',
                'auth' => true,
            ],
            'doctor' => [
                'uid' => 'demo-dokter-dummy',
                'email' => 'dokter.dummy@medihub.test',
                'name' => 'Ratna Dewi',
                'role' => 'dokter',
                'auth' => true,
            ],
        ];
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $data
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function withoutExtraDemoDoctors(array $data): array
    {
        foreach ($this->extraDoctorDemoDocuments() as $collection => $ids) {
            foreach ($ids as $id) {
                unset($data[$collection][$id]);
            }
        }

        return $data;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function extraDoctorDemoDocuments(): array
    {
        return [
            'Users' => ['demo-dokter-arief', 'demo-dokter-andini'],
            'Dokter' => ['demo-doctor-arief', 'demo-doctor-andini'],
            'Dokter_spesialisasi' => ['demo-spec-arief', 'demo-spec-andini'],
            'Dokter_sertifikasi' => ['demo-cert-arief', 'demo-cert-andini'],
            'Dokter_dokumen' => ['demo-doc-arief', 'demo-doc-andini'],
            'JadwalDokter' => ['demo-schedule-arief-next-week', 'demo-schedule-andini-next-week'],
            'BuatJadwalTemu' => ['demo-appointment-cancelled'],
            'Ulasan' => ['demo-review-arief'],
        ];
    }

    /**
     * @param array<string, array{uid: string, email: string, name: string, role: string, auth: bool}> $users
     */
    private function seedAuthUsers(FirebaseAuth $auth, array &$users): void
    {
        $this->info('Menyiapkan akun Firebase Auth...');

        foreach ($users as $key => $user) {
            if (! $user['auth']) {
                continue;
            }

            try {
                $record = $auth->getUserByEmail($user['email']);
                $auth->updateUser($record->uid, [
                    'displayName' => $user['name'],
                    'emailVerified' => true,
                    'password' => self::PASSWORD,
                    'disabled' => false,
                ]);

                $users[$key]['uid'] = $record->uid;
                $this->line('  update '.$user['email']);
            } catch (UserNotFound) {
                $record = $auth->createUser([
                    'uid' => $user['uid'],
                    'email' => $user['email'],
                    'emailVerified' => true,
                    'password' => self::PASSWORD,
                    'displayName' => $user['name'],
                    'disabled' => false,
                ]);

                $users[$key]['uid'] = $record->uid;
                $this->line('  create '.$user['email']);
            }
        }
    }

    /**
     * @param array<string, array{uid: string, email: string, name: string, role: string, auth: bool}> $users
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function demoData(array $users): array
    {
        $now = now();
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();
        $nextWeek = $now->copy()->addDays(7)->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $lastWeek = $now->copy()->subDays(7)->toDateString();

        $patientUid = $users['patient']['uid'];
        $doctorUid = $users['doctor']['uid'];

        $data = [
            'Users' => [
                $patientUid => [
                    'fullname' => 'Pasien Dummy',
                    'name' => 'Pasien Dummy',
                    'role' => 'pasien',
                    'email' => 'pasien.dummy@medihub.test',
                    'password' => null,
                    'email_verified' => true,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                $doctorUid => [
                    'fullname' => 'Ratna Dewi',
                    'name' => 'Ratna Dewi',
                    'role' => 'dokter',
                    'email' => 'dokter.dummy@medihub.test',
                    'password' => null,
                    'email_verified' => true,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-dokter-arief' => [
                    'fullname' => 'Arief Nugroho',
                    'name' => 'Arief Nugroho',
                    'role' => 'dokter',
                    'email' => 'arief@medihub.test',
                    'password' => null,
                    'email_verified' => true,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-dokter-andini' => [
                    'fullname' => 'Andini Pratama',
                    'name' => 'Andini Pratama',
                    'role' => 'dokter',
                    'email' => 'andini@medihub.test',
                    'password' => null,
                    'email_verified' => true,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'Pasien' => [
                $patientUid => [
                    'user_id' => $patientUid,
                    'fullname' => 'Pasien Dummy',
                    'email' => 'pasien.dummy@medihub.test',
                    'umur' => 24,
                    'age' => 24,
                    'gender' => 'Perempuan',
                    'weight' => '52',
                    'height' => '162',
                    'blood_type' => 'O',
                    'allergy_history' => 'Tidak ada alergi obat yang diketahui',
                    'numPhone' => '081234567890',
                    'city' => 'Bandung',
                    'country' => 'Indonesia',
                    'codePos' => '40123',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'Dokter' => [
                'demo-doctor-ratna' => [
                    'usersId' => $doctorUid,
                    'email' => 'dokter.dummy@medihub.test',
                    'umur' => 39,
                    'numPhone' => 81222001122,
                    'weight' => '58',
                    'height' => '164',
                    'gender' => 'Perempuan',
                    'country' => 'Indonesia',
                    'city' => 'Bandung',
                    'codePos' => '40115',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-doctor-arief' => [
                    'usersId' => 'demo-dokter-arief',
                    'email' => 'arief@medihub.test',
                    'umur' => 42,
                    'numPhone' => 81233004455,
                    'weight' => '70',
                    'height' => '172',
                    'gender' => 'Laki-laki',
                    'country' => 'Indonesia',
                    'city' => 'Bandung',
                    'codePos' => '40116',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-doctor-andini' => [
                    'usersId' => 'demo-dokter-andini',
                    'email' => 'andini@medihub.test',
                    'umur' => 35,
                    'numPhone' => 81244006677,
                    'weight' => '55',
                    'height' => '160',
                    'gender' => 'Perempuan',
                    'country' => 'Indonesia',
                    'city' => 'Bandung',
                    'codePos' => '40117',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'Dokter_spesialisasi' => [
                'demo-spec-ratna' => [
                    'dokterid' => 'demo-doctor-ratna',
                    'main_specialization' => 'Psikiater',
                    'sub_specialization' => 'Kesehatan Mental Dewasa',
                    'practice_year' => '2012',
                    'academy' => 'Universitas Padjadjaran',
                    'service' => 'Konsultasi Psikiatri, Terapi Kecemasan, Manajemen Stres',
                    'services' => ['Konsultasi Psikiatri', 'Terapi Kecemasan', 'Manajemen Stres'],
                    'short_biography' => 'Berpengalaman menangani gangguan kecemasan, depresi ringan, dan konseling keluarga.',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-spec-arief' => [
                    'dokterid' => 'demo-doctor-arief',
                    'main_specialization' => 'Psikolog',
                    'sub_specialization' => 'Psikologi Klinis',
                    'practice_year' => '2015',
                    'academy' => 'Universitas Indonesia',
                    'service' => 'Konseling Individu, Psikotes, Terapi Perilaku',
                    'services' => ['Konseling Individu', 'Psikotes', 'Terapi Perilaku'],
                    'short_biography' => 'Fokus pada konseling individu dan terapi perilaku kognitif untuk pasien dewasa muda.',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-spec-andini' => [
                    'dokterid' => 'demo-doctor-andini',
                    'main_specialization' => 'Psikolog Anak',
                    'sub_specialization' => 'Tumbuh Kembang Anak',
                    'practice_year' => '2017',
                    'academy' => 'Universitas Gadjah Mada',
                    'service' => 'Konseling Anak, Konsultasi Orang Tua, Asesmen Tumbuh Kembang',
                    'services' => ['Konseling Anak', 'Konsultasi Orang Tua', 'Asesmen Tumbuh Kembang'],
                    'short_biography' => 'Mendampingi anak dan orang tua dalam isu emosi, belajar, dan tumbuh kembang.',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'Dokter_sertifikasi' => [
                'demo-cert-ratna' => [
                    'dokterid' => 'demo-doctor-ratna',
                    'sertification1' => 'STR Psikiater Aktif 2026',
                    'sertification2' => 'Sertifikat Pelatihan CBT',
                    'sertification3' => 'Sertifikat Manajemen Krisis Pasien',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-cert-arief' => [
                    'dokterid' => 'demo-doctor-arief',
                    'sertification1' => 'SIPP Psikolog Klinis Aktif 2026',
                    'sertification2' => 'Sertifikat Psikoterapi Dasar',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-cert-andini' => [
                    'dokterid' => 'demo-doctor-andini',
                    'sertification1' => 'SIPP Psikolog Anak Aktif 2026',
                    'sertification2' => 'Sertifikat Asesmen Tumbuh Kembang',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'Dokter_dokumen' => [
                'demo-doc-ratna' => [
                    'dokterid' => 'demo-doctor-ratna',
                    'STR' => 'demo/str-ratna.pdf',
                    'SIP' => 'demo/sip-ratna.pdf',
                    'ijazah_doctor' => 'demo/ijazah-ratna.pdf',
                    'KTP' => 'demo/ktp-ratna.pdf',
                    'profile_pict' => null,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-doc-arief' => [
                    'dokterid' => 'demo-doctor-arief',
                    'STR' => 'demo/str-arief.pdf',
                    'SIP' => 'demo/sip-arief.pdf',
                    'ijazah_doctor' => 'demo/ijazah-arief.pdf',
                    'KTP' => 'demo/ktp-arief.pdf',
                    'profile_pict' => null,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-doc-andini' => [
                    'dokterid' => 'demo-doctor-andini',
                    'STR' => 'demo/str-andini.pdf',
                    'SIP' => 'demo/sip-andini.pdf',
                    'ijazah_doctor' => 'demo/ijazah-andini.pdf',
                    'KTP' => 'demo/ktp-andini.pdf',
                    'profile_pict' => null,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'facilities' => [
                'demo-facility-medic-center' => [
                    'name' => 'RS Medic Center',
                    'type' => 'Rumah Sakit',
                    'location' => 'Jl. Merdeka No. 123, Bandung',
                    'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=400&h=300&fit=crop',
                ],
                'demo-facility-harmoni' => [
                    'name' => 'Klinik Sehat Harmoni',
                    'type' => 'Klinik',
                    'location' => 'Jl. Sudirman No. 45, Bandung',
                    'image' => 'https://images.unsplash.com/photo-1631217314831-c6227db76b6e?w=400&h=300&fit=crop',
                ],
                'demo-facility-dental' => [
                    'name' => 'Harmony Dental Care',
                    'type' => 'Klinik Gigi',
                    'location' => 'Jl. Gatot Subroto No. 99, Bandung',
                    'image' => 'https://images.unsplash.com/photo-1606811971618-4486d14f3f99?w=400&h=300&fit=crop',
                ],
            ],
            'JadwalDokter' => [
                'demo-schedule-ratna-today' => [
                    'dokterid' => 'demo-doctor-ratna',
                    'tanggal' => $today,
                    'jam_mulai' => '09:00',
                    'jam_selesai' => '12:00',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-schedule-ratna-tomorrow' => [
                    'dokterid' => 'demo-doctor-ratna',
                    'tanggal' => $tomorrow,
                    'jam_mulai' => '13:00',
                    'jam_selesai' => '16:00',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-schedule-arief-next-week' => [
                    'dokterid' => 'demo-doctor-arief',
                    'tanggal' => $nextWeek,
                    'jam_mulai' => '10:00',
                    'jam_selesai' => '14:00',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-schedule-andini-next-week' => [
                    'dokterid' => 'demo-doctor-andini',
                    'tanggal' => $nextWeek,
                    'jam_mulai' => '15:00',
                    'jam_selesai' => '18:00',
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
            ],
            'BuatJadwalTemu' => [
                'demo-appointment-today' => [
                    'user_uid' => $patientUid,
                    'patient_id' => $patientUid,
                    'dokterid' => 'demo-doctor-ratna',
                    'doctor_id' => 'demo-doctor-ratna',
                    'doctor_schedule_id' => 'demo-schedule-ratna-today',
                    'appointment_date' => $today,
                    'appointment_time' => '09:00',
                    'appointment_time_start' => '09:00',
                    'appointment_time_end' => '09:30',
                    'schedule_time_range' => '09:00 - 12:00',
                    'queue_number' => 1,
                    'patient_name' => 'Pasien Dummy',
                    'patient_email' => 'pasien.dummy@medihub.test',
                    'patient_age' => 24,
                    'patient_gender' => 'Perempuan',
                    'patient_weight' => '52',
                    'patient_height' => '162',
                    'blood_type' => 'O',
                    'allergy_history' => 'Tidak ada alergi obat yang diketahui',
                    'complaint' => 'Sulit tidur dan mudah cemas sejak dua minggu terakhir.',
                    'medical_doc' => null,
                    'status' => 'Menunggu',
                    'cancellation_reason' => null,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-appointment-tomorrow' => [
                    'user_uid' => $patientUid,
                    'patient_id' => $patientUid,
                    'dokterid' => 'demo-doctor-ratna',
                    'doctor_id' => 'demo-doctor-ratna',
                    'doctor_schedule_id' => 'demo-schedule-ratna-tomorrow',
                    'appointment_date' => $tomorrow,
                    'appointment_time' => '13:00',
                    'appointment_time_start' => '13:00',
                    'appointment_time_end' => '13:30',
                    'schedule_time_range' => '13:00 - 16:00',
                    'queue_number' => 1,
                    'patient_name' => 'Pasien Dummy',
                    'patient_email' => 'pasien.dummy@medihub.test',
                    'patient_age' => 24,
                    'patient_gender' => 'Perempuan',
                    'patient_weight' => '52',
                    'patient_height' => '162',
                    'blood_type' => 'O',
                    'allergy_history' => 'Tidak ada alergi obat yang diketahui',
                    'complaint' => 'Kontrol lanjutan setelah sesi konsultasi awal.',
                    'medical_doc' => null,
                    'status' => 'Diperiksa',
                    'cancellation_reason' => null,
                    'created_at' => $now->toIso8601String(),
                    'update_at' => $now->toIso8601String(),
                ],
                'demo-appointment-completed' => [
                    'user_uid' => $patientUid,
                    'patient_id' => $patientUid,
                    'dokterid' => 'demo-doctor-ratna',
                    'doctor_id' => 'demo-doctor-ratna',
                    'doctor_schedule_id' => 'demo-schedule-ratna-today',
                    'appointment_date' => $yesterday,
                    'appointment_time' => '10:00',
                    'appointment_time_start' => '10:00',
                    'appointment_time_end' => '10:30',
                    'schedule_time_range' => '09:00 - 12:00',
                    'queue_number' => 2,
                    'patient_name' => 'Pasien Dummy',
                    'patient_email' => 'pasien.dummy@medihub.test',
                    'patient_age' => 24,
                    'patient_gender' => 'Perempuan',
                    'patient_weight' => '52',
                    'patient_height' => '162',
                    'blood_type' => 'O',
                    'allergy_history' => 'Tidak ada alergi obat yang diketahui',
                    'complaint' => 'Konsultasi awal terkait kecemasan.',
                    'medical_doc' => null,
                    'status' => 'Selesai',
                    'cancellation_reason' => null,
                    'created_at' => $now->copy()->subDay()->toIso8601String(),
                    'update_at' => $now->copy()->subDay()->toIso8601String(),
                ],
                'demo-appointment-cancelled' => [
                    'user_uid' => $patientUid,
                    'patient_id' => $patientUid,
                    'dokterid' => 'demo-doctor-arief',
                    'doctor_id' => 'demo-doctor-arief',
                    'doctor_schedule_id' => 'demo-schedule-arief-next-week',
                    'appointment_date' => $lastWeek,
                    'appointment_time' => '10:30',
                    'appointment_time_start' => '10:30',
                    'appointment_time_end' => '11:00',
                    'schedule_time_range' => '10:00 - 14:00',
                    'queue_number' => 1,
                    'patient_name' => 'Pasien Dummy',
                    'patient_email' => 'pasien.dummy@medihub.test',
                    'patient_age' => 24,
                    'patient_gender' => 'Perempuan',
                    'patient_weight' => '52',
                    'patient_height' => '162',
                    'blood_type' => 'O',
                    'allergy_history' => 'Tidak ada alergi obat yang diketahui',
                    'complaint' => 'Jadwal konsultasi psikologi.',
                    'medical_doc' => null,
                    'status' => 'Dibatalkan',
                    'cancellation_reason' => 'Ada agenda mendadak.',
                    'created_at' => $now->copy()->subDays(8)->toIso8601String(),
                    'update_at' => $now->copy()->subDays(7)->toIso8601String(),
                ],
            ],
            'CatatanMedis' => [
                'demo-medical-note-completed' => [
                    'appointment_id' => 'demo-appointment-completed',
                    'doctor_id' => 'demo-doctor-ratna',
                    'doctor_user_id' => $doctorUid,
                    'doctor_name' => 'Ratna Dewi',
                    'patient_id' => $patientUid,
                    'patient_name' => 'Pasien Dummy',
                    'keluhan_utama' => 'Kecemasan ringan dan sulit tidur.',
                    'hasil_observasi' => 'Pasien kooperatif, orientasi baik, tampak lelah namun stabil.',
                    'hasil_asesmen' => 'Gangguan kecemasan ringan.',
                    'kesimpulan' => 'Kondisi stabil dan dapat dilanjutkan dengan kontrol terjadwal.',
                    'rekomendasi' => 'Latihan relaksasi napas, sleep hygiene, dan kontrol satu minggu lagi.',
                    'resep_obat' => 'Melatonin 3 mg bila diperlukan sebelum tidur.',
                    'created_at' => $now->copy()->subDay()->toIso8601String(),
                ],
            ],
            'Notifications' => [
                'demo-notification-booking' => [
                    'patient_id' => $patientUid,
                    'title' => 'Janji Temu Berhasil Dibuat',
                    'message' => 'Janji temu Anda dengan dr. Ratna Dewi berhasil dibuat untuk hari ini pukul 09:00 WIB.',
                    'type' => 'success',
                    'read' => false,
                    'created_at' => $now->copy()->subMinutes(25)->toIso8601String(),
                ],
                'demo-notification-status' => [
                    'patient_id' => $patientUid,
                    'title' => 'Status Jadwal Temu Diperbarui',
                    'message' => 'Status jadwal temu Anda telah diubah menjadi: Diperiksa.',
                    'type' => 'appointment_status',
                    'read' => false,
                    'created_at' => $now->copy()->subMinutes(10)->toIso8601String(),
                ],
                'demo-notification-medical-note' => [
                    'patient_id' => $patientUid,
                    'title' => 'Catatan Medis Tersedia',
                    'message' => 'dr. Ratna Dewi telah mengisi catatan medis dan resep obat untuk kunjungan Anda.',
                    'type' => 'medical_record',
                    'read' => false,
                    'created_at' => $now->copy()->subMinutes(5)->toIso8601String(),
                ],
            ],
            'Ulasan' => [
                'demo-review-ratna' => [
                    'doctor_id' => 'demo-doctor-ratna',
                    'dokterid' => 'demo-doctor-ratna',
                    'patient_id' => $patientUid,
                    'user_id' => $patientUid,
                    'patient_name' => 'Pasien Dummy',
                    'rating' => 5,
                    'text' => 'Dokternya komunikatif dan penjelasannya mudah dipahami.',
                    'likes' => 12,
                    'created_at' => $now->copy()->subDays(3)->toIso8601String(),
                    'update_at' => $now->copy()->subDays(3)->toIso8601String(),
                ],
                'demo-review-arief' => [
                    'doctor_id' => 'demo-doctor-arief',
                    'dokterid' => 'demo-doctor-arief',
                    'patient_id' => $patientUid,
                    'user_id' => $patientUid,
                    'patient_name' => 'Pasien Dummy',
                    'rating' => 4.8,
                    'text' => 'Sesi konseling terasa nyaman dan sangat membantu.',
                    'likes' => 8,
                    'created_at' => $now->copy()->subDays(5)->toIso8601String(),
                    'update_at' => $now->copy()->subDays(5)->toIso8601String(),
                ],
            ],
        ];

        return $this->withoutExtraDemoDoctors($data);
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $data
     */
    private function seedFirestore(FirestoreService $firestore, array $data): void
    {
        $this->info('Menulis data Firestore demo...');

        foreach ($data as $collection => $documents) {
            foreach ($documents as $id => $payload) {
                $firestore->set($collection, (string) $id, $payload);
            }

            $this->line('  '.$collection.': '.count($documents).' dokumen');
        }
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $data
     */
    private function describeSeedPlan(array $data): void
    {
        $this->warn('Dry run aktif. Tidak ada data Firebase yang diubah.');

        foreach ($data as $collection => $documents) {
            $this->line('  '.$collection.': '.count($documents).' dokumen');
        }

        $this->newLine();
        $this->line('Akun yang akan dibuat/update saat dry-run dimatikan:');
        $this->line('  pasien.dummy@medihub.test / '.self::PASSWORD);
        $this->line('  dokter.dummy@medihub.test / '.self::PASSWORD);
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $data
     */
    private function deleteKnownDemoDocuments(FirestoreService $firestore, array $data): void
    {
        $this->warn('Menghapus dokumen demo yang dikenal...');

        foreach (array_reverse($data) as $collection => $documents) {
            foreach (array_keys($documents) as $id) {
                $firestore->delete($collection, (string) $id);
            }
        }

        foreach ($this->extraDoctorDemoDocuments() as $collection => $ids) {
            foreach ($ids as $id) {
                $firestore->delete($collection, $id);
            }
        }
    }
}
