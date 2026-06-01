<?php

namespace Tests\Unit\Public;

use App\Http\Controllers\Public\DokterPublicController;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * PBI-09 (Daftar Layanan/Poli) & PBI-10 (Daftar Dokter + Jadwal).
 *
 * Controller diuji lewat pemanggilan langsung dan memeriksa objek View
 * (nama + data) yang dikembalikan, sehingga tidak bergantung pada render
 * file blade. FirestoreService di-mock agar bebas dari panggilan jaringan.
 */
class DokterPublicControllerTest extends TestCase
{
    private FirestoreService $firestore;

    private DokterPublicController $controller;

    private string $stubViewPath;

    protected function setUp(): void
    {
        parent::setUp();

        // View untuk PBI-09/10 belum ada di branch ini; daftarkan stub kosong
        // agar view() bisa membangun objek View untuk diinspeksi tanpa render.
        $this->stubViewPath = sys_get_temp_dir() . '/medihub-views-' . uniqid();
        mkdir($this->stubViewPath . '/dokter', 0777, true);
        foreach (['dokter/services', 'dokter/show'] as $view) {
            file_put_contents($this->stubViewPath . '/' . $view . '.blade.php', '');
        }
        ViewFacade::getFinder()->addLocation($this->stubViewPath);

        $this->firestore = Mockery::mock(FirestoreService::class);
        $this->controller = new DokterPublicController($this->firestore);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        foreach (['dokter/services', 'dokter/show'] as $view) {
            @unlink($this->stubViewPath . '/' . $view . '.blade.php');
        }
        @rmdir($this->stubViewPath . '/dokter');
        @rmdir($this->stubViewPath);

        parent::tearDown();
    }

    public function test_services_lists_doctors_and_unique_specialties(): void
    {
        $this->firestore->shouldReceive('all')
            ->with('Dokter')
            ->andReturn([
                ['id' => 'dok-a', 'specialty' => 'Anak'],
                ['id' => 'dok-b', 'specialty' => 'Gigi'],
                ['id' => 'dok-c', 'specialty' => 'Anak'],
                ['id' => 'dok-d', 'specialty' => null],
            ]);

        $view = $this->controller->services();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('dokter.services', $view->name());

        $data = $view->getData();
        $this->assertCount(4, $data['doctors']);
        // Spesialisasi unik & tanpa nilai kosong.
        $this->assertEqualsCanonicalizing(['Anak', 'Gigi'], $data['specialties']);
        $this->assertIsObject($data['doctors'][0]);
        $this->assertSame('dok-a', $data['doctors'][0]->id);
    }

    public function test_services_handles_empty_doctor_collection(): void
    {
        $this->firestore->shouldReceive('all')->with('Dokter')->andReturn([]);

        $view = $this->controller->services();

        $this->assertSame([], $view->getData()['doctors']);
        $this->assertSame([], $view->getData()['specialties']);
    }

    public function test_show_returns_doctor_detail_with_schedule_slots(): void
    {
        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'specialty' => 'Anak', 'email' => 'andi@example.com']);

        $view = $this->controller->show('dok-a');

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('dokter.show', $view->name());

        $data = $view->getData();
        $this->assertSame('dok-a', $data['doctor']->id);
        $this->assertIsObject($data['doctor']);
        // Slot waktu & hari tersedia ikut dikirim ke view (PBI-10).
        $this->assertNotEmpty($data['timeSlots']);
        $this->assertNotEmpty($data['availableSlots']);
        $this->assertContains('08:00', $data['timeSlots']);
    }

    public function test_show_aborts_with_404_when_doctor_missing(): void
    {
        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'ghost')
            ->andReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->show('ghost');
    }
}
