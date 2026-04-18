<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;

class FirestoreSeeder extends Command
{
    protected $signature = 'firestore:seed {--fresh : Hapus data lama sebelum seed}';
    protected $description = 'Seed data awal (dokter, fasilitas, appointments) ke Firebase Firestore';

    public function handle(FirestoreService $firestore): int
    {
        if ($this->option('fresh')) {
            $this->warn('Menghapus data lama...');
            $this->deleteCollection($firestore, 'doctors');
            $this->deleteCollection($firestore, 'facilities');
            $this->deleteCollection($firestore, 'appointments');
            $this->info('Data lama dihapus.');
        }

        $this->info('Seeding dokter...');
        $doctors = [
            [
                'name' => 'dr. Arief Nugroho, S.Psi., M.Psi., Psikolog',
                'specialty' => 'Psikolog',
                'rating' => 5.0,
                'patients' => 450,
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop',
            ],
            [
                'name' => 'dr. Ratna Dewi dr., Sp.KJ',
                'specialty' => 'Psikiater',
                'rating' => 4.9,
                'patients' => 450,
                'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop',
            ],
            [
                'name' => 'dr. Andini Pratama, S.Psi., M.Psi., Psikolog',
                'specialty' => 'Psikolog',
                'rating' => 5.0,
                'patients' => 450,
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop',
            ],
        ];

        $doctorIds = [];
        foreach ($doctors as $doctor) {
            $result = $firestore->add('doctors', $doctor);
            $doctorIds[] = $result['id'];
            $this->line("  ✓ {$doctor['name']}");
        }

        $this->info('Seeding fasilitas...');
        $facilities = [
            [
                'name' => 'RS Medic Center',
                'type' => 'Rumah Sakit',
                'location' => 'Jl. Merdeka No. 123, Bandung',
                'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'Sehat Harmoni',
                'type' => 'Klinik',
                'location' => 'Jl. Sudirman No. 45, Bandung',
                'image' => 'https://images.unsplash.com/photo-1631217314831-c6227db76b6e?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'Suka Maju',
                'type' => 'Klinik',
                'location' => 'Jl. Ahmad Yani No. 78, Bandung',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'Harmony Dental',
                'type' => 'Klinik Gigi',
                'location' => 'Jl. Gatot Subroto No. 99, Bandung',
                'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=400&h=300&fit=crop',
            ],
        ];

        foreach ($facilities as $facility) {
            $firestore->add('facilities', $facility);
            $this->line("  ✓ {$facility['name']}");
        }

        $this->info('Seeding contoh appointment...');
        if (! empty($doctorIds)) {
            $sampleAppointments = [
                ['appointment_date' => '2026-08-30T13:00:00', 'patient_name' => 'Naswa Gyna Sahira'],
                ['appointment_date' => '2026-08-30T09:15:00', 'patient_name' => 'Naswa Gyna Sahira'],
                ['appointment_date' => '2026-07-03T18:15:00', 'patient_name' => 'Naswa Gyna Sahira'],
            ];

            foreach ($sampleAppointments as $apt) {
                $firestore->add('appointments', array_merge($apt, [
                    'doctor_id' => $doctorIds[0],
                    'user_uid' => 'seed-demo',
                ]));
                $this->line("  ✓ Appointment {$apt['appointment_date']}");
            }
        }

        $this->newLine();
        $this->info('✅ Seeding ke Firestore selesai!');
        $this->info("   Dokter: " . count($doctors));
        $this->info("   Fasilitas: " . count($facilities));
        $this->info("   Appointments: " . count($sampleAppointments ?? []));

        return self::SUCCESS;
    }

    private function deleteCollection(FirestoreService $firestore, string $collection): void
    {
        $docs = $firestore->all($collection);
        foreach ($docs as $doc) {
            $firestore->delete($collection, $doc['id']);
        }
    }
}
