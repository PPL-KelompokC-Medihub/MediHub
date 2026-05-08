<?php

namespace App\Services\Pasien;

use App\Services\FirestoreService;

class DashboardService
{
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
        $appointments = [];

        return compact('categories', 'doctors', 'facilities', 'appointments');
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
                'description' => 'Rumah sakit umum modern dengan pelayanan kesehatan komprehensif yang berfokus pada kenyamanan pasien. Dengan fasilitas lengkap dan didukung oleh dokter spesialis berpengalaman di berbagai bidang serta tenaga medis profesional.',
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
                'nama' => 'dr. ' . ($user['fullname'] ?? $user['name'] ?? 'Tidak Diketahui'),
                'spesialis' => $specialist['service'] ?? 'Tidak Diketahui',
                'spesialis_key' => strtolower($specialist['service'] ?? 'Tidak Diketahui'),
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => isset($documents[$doctorId]['profile_pict']) 
                    ? asset('storage/' . $documents[$doctorId]['profile_pict'])
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
        return [
            [
                'name' => 'Rina',
                'rating' => '5.0',
                'date' => '17 Agustus | 19:07 PM',
                'text' => 'Pelayanan cepat dan terorganisir. Saya tidak perlu menunggu lama di ruang tunggu karena antrian sudah bisa daftar lewat aplikasi.',
                'likes' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=120&auto=format&fit=crop',
            ],
            [
                'name' => 'Melati',
                'rating' => '4.0',
                'date' => '17 Agustus | 18:07 PM',
                'text' => 'IGD buka 24 jam dan respon perawatnya sigap sekali. Hanya saja area parkir agak penuh di jam sibuk.',
                'likes' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=120&auto=format&fit=crop',
            ],
            [
                'name' => 'Ahmad',
                'rating' => '4.0',
                'date' => '17 Agustus | 19:07 PM',
                'text' => 'Secara keseluruhan puas, apalagi dengan adanya sistem antrian online jadi lebih efisien.',
                'likes' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=120&auto=format&fit=crop',
            ],
            [
                'name' => 'Yanto',
                'rating' => '5.0',
                'date' => '17 Agustus | 19:07 PM',
                'text' => 'Anak saya dirawat di ruang anak, suasananya dibuat ceria dan ramah anak.',
                'likes' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=120&auto=format&fit=crop',
            ],
        ];
    }
}
