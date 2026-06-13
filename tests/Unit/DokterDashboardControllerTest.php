<?php

namespace Tests\Unit;

use App\Http\Controllers\Dokter\DashboardController;
use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class DokterDashboardControllerTest extends TestCase
{
    public function test_dashboard_filters_patient_search_and_exposes_real_status(): void
    {
        Auth::setUser(new FirestoreUser([
            'id' => 'doctor-user-1',
            'email' => 'doctor@example.test',
            'role' => 'dokter',
        ]));

        $appointments = [
            [
                'id' => 'appointment-1',
                'dokterid' => 'doctor-1',
                'patient_id' => 'patient-1',
                'patient_name' => 'Sari Melati',
                'patient_email' => 'sari@example.test',
                'appointment_date' => now()->toDateString(),
                'appointment_time_start' => '09:00',
                'appointment_time_end' => '09:30',
                'complaint' => 'Demam',
                'status' => 'Diperiksa',
            ],
            [
                'id' => 'appointment-2',
                'dokterid' => 'doctor-1',
                'patient_id' => 'patient-2',
                'patient_name' => 'Budi Santoso',
                'patient_email' => 'budi@example.test',
                'appointment_date' => now()->toDateString(),
                'appointment_time_start' => '10:00',
                'status' => 'Menunggu',
            ],
        ];

        $firestore = Mockery::mock(FirestoreService::class);
        $firestore->shouldReceive('where')->andReturnUsing(function (
            string $collection,
            string $field,
            string $operator,
            mixed $value,
            ?int $limit = null,
        ) use ($appointments): array {
            if ($collection === 'Dokter' && $field === 'usersId') {
                return [['id' => 'doctor-1', 'usersId' => 'doctor-user-1']];
            }

            if ($collection === 'BuatJadwalTemu') {
                $values = is_array($value) ? array_map('strval', $value) : [(string) $value];

                return array_values(array_filter(
                    $appointments,
                    fn (array $appointment): bool => in_array((string) ($appointment[$field] ?? ''), $values, true),
                ));
            }

            return [];
        });

        $repository = Mockery::mock(MedihubFirestoreRepository::class);
        $repository->shouldReceive('findUser')->with('doctor-user-1')->andReturn([
            'id' => 'doctor-user-1',
            'fullname' => 'Dokter Bima',
        ]);
        $repository->shouldReceive('hydrateDoctorData')->andReturn([
            'name' => 'Dokter Bima',
            'profile_pict' => null,
        ]);

        $controller = new DashboardController($firestore, $repository);
        $view = $controller->index(Request::create('/dokter/dashboard', 'GET', [
            'search' => 'sari',
            'date' => now()->toDateString(),
        ]));

        $data = $view->getData();

        $this->assertCount(1, $data['appointments']);
        $this->assertSame('Sari Melati', $data['appointments'][0]->patient_name);
        $this->assertSame('Diperiksa', $data['appointments'][0]->display_status);
        $this->assertSame('diperiksa', $data['appointments'][0]->status_key);
        $this->assertSame(2, $data['jadwalHariIni']);
        $this->assertSame(2, $data['totalPasien']);
    }
}
