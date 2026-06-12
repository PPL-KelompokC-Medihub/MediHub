<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DuskTestDataSeeder extends Seeder
{
    /**
     * Seed test data untuk Dusk Browser Tests.
     * 
     * Membuat user pasien dan dokter dengan appointment history
     * untuk keperluan automation testing.
     */
    public function run(): void
    {
        // 1. Create test doctor user
        $doctorData = [
            'id' => 'doctor-test-001',
            'fullname' => 'Dr. Budi Santoso',
            'email' => 'dokter@test.com',
            'password' => bcrypt('password123'),
            'role' => 'dokter',
            'email_verified_at' => now(),
            'created_at' => now(),
            'update_at' => now(),
        ];

        // Simpan ke database (sqlite untuk testing)
        \DB::table('users')->insertOrIgnore($doctorData);

        // 2. Create test patient users
        $patientData = [
            [
                'id' => 'patient-test-001',
                'fullname' => 'Pasien Test',
                'email' => 'pasien@test.com',
                'password' => bcrypt('password123'),
                'role' => 'pasien',
                'email_verified_at' => now(),
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 'patient-new-001',
                'fullname' => 'Pasien Baru',
                'email' => 'newpasien@test.com',
                'password' => bcrypt('password123'),
                'role' => 'pasien',
                'email_verified_at' => now(),
                'created_at' => now(),
                'update_at' => now(),
            ],
        ];

        foreach ($patientData as $patient) {
            \DB::table('users')->insertOrIgnore($patient);
        }

        // 3. Jika menggunakan Firestore untuk appointment history, 
        // setup appointment data di Firestore
        $this->seedAppointmentHistory();
    }

    /**
     * Seed appointment history ke Firestore (jika digunakan).
     */
    private function seedAppointmentHistory(): void
    {
        try {
            $firestore = app('firebase.firestore');

            // Check if we can access Firestore
            if (!$firestore) {
                \Log::warning('Firestore service not available for test seeding');
                return;
            }

            // Sample appointment history untuk patient 'patient-test-001'
            $appointmentHistory = [
                [
                    'patient_id' => 'patient-test-001',
                    'doctor_id' => 'doctor-test-001',
                    'jenis' => 'Konsultasi',
                    'rs' => 'Rumah Sakit Mitra MediHub',
                    'dokter' => 'Dr. Budi Santoso',
                    'dokter_foto' => 'https://via.placeholder.com/100?text=Dr.Budi',
                    'tanggal' => now()->subDays(5)->format('d M Y'),
                    'jam' => '10:00 - 11:00',
                    'status' => 'Selesai',
                    'status_key' => 'selesai',
                    'keluhan' => 'Demam dan batuk selama 3 hari',
                    'alergi' => 'Alergi Amoksisilin',
                    'pemeriksaan_fisik' => 'Tekanan darah 120/80, Suhu 37.8°C',
                    'diagnosis_sementara' => 'Flu biasa (Common Cold)',
                    'rencana_penanganan' => 'Istirahat cukup, minum banyak air, paracetamol 500mg 3x sehari',
                    'catatan_dokter' => 'Pasien disarankan istirahat selama 3-5 hari',
                    'resep_obat' => 'Paracetamol 500mg (3x sehari), Vitamin C 1000mg (2x sehari)',
                    'created_at' => now()->subDays(5)->toIso8601String(),
                    'updated_at' => now()->subDays(5)->toIso8601String(),
                ],
                [
                    'patient_id' => 'patient-test-001',
                    'doctor_id' => 'doctor-test-001',
                    'jenis' => 'Pemeriksaan Kesehatan',
                    'rs' => 'Rumah Sakit Mitra MediHub',
                    'dokter' => 'Dr. Budi Santoso',
                    'dokter_foto' => 'https://via.placeholder.com/100?text=Dr.Budi',
                    'tanggal' => now()->subDays(15)->format('d M Y'),
                    'jam' => '14:00 - 15:00',
                    'status' => 'Dibatalkan',
                    'status_key' => 'dibatalkan',
                    'keluhan' => '-',
                    'alergi' => 'Tidak ada',
                    'alasan_pembatalan' => 'Pasien tidak bisa hadir',
                    'created_at' => now()->subDays(15)->toIso8601String(),
                    'updated_at' => now()->subDays(12)->toIso8601String(),
                ],
            ];

            // Try to insert ke Firestore (collection: BuatJadwalTemu atau sesuai schema)
            // Sesuaikan dengan collection name dan structure di project Anda
            foreach ($appointmentHistory as $appointment) {
                // Ini adalah placeholder - sesuaikan dengan actual Firestore operations
                // Contoh: $firestore->collection('BuatJadwalTemu')->add($appointment)
                \Log::info('Would seed appointment to Firestore', $appointment);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to seed Firestore data', ['error' => $e->getMessage()]);
        }
    }
}
