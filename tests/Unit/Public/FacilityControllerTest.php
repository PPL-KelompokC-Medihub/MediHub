<?php

namespace Tests\Unit\Public;

use App\Http\Controllers\Public\FacilityController;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * PBI-09 — Daftar Fasilitas Rumah Sakit (detail fasilitas).
 *
 * Controller diuji lewat pemanggilan langsung & inspeksi objek View.
 * FirestoreService di-mock agar tidak ada panggilan HTTP nyata.
 */
class FacilityControllerTest extends TestCase
{
    private FirestoreService $firestore;

    private FacilityController $controller;

    private string $stubViewPath;

    protected function setUp(): void
    {
        parent::setUp();

        // View facility.show belum ada di branch ini; daftarkan stub kosong
        // agar view() bisa membangun objek View untuk diinspeksi tanpa render.
        $this->stubViewPath = sys_get_temp_dir() . '/medihub-facility-views-' . uniqid();
        mkdir($this->stubViewPath . '/facility', 0777, true);
        file_put_contents($this->stubViewPath . '/facility/show.blade.php', '');
        ViewFacade::getFinder()->addLocation($this->stubViewPath);

        $this->firestore = Mockery::mock(FirestoreService::class);
        $this->controller = new FacilityController($this->firestore);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        @unlink($this->stubViewPath . '/facility/show.blade.php');
        @rmdir($this->stubViewPath . '/facility');
        @rmdir($this->stubViewPath);

        parent::tearDown();
    }

    public function test_show_returns_facility_with_related_doctors_features_and_gallery(): void
    {
        $this->firestore->shouldReceive('find')
            ->with('facilities', 'fac-1')
            ->andReturn(['id' => 'fac-1', 'name' => 'RS Medihub Pusat']);

        $this->firestore->shouldReceive('all')
            ->with('Dokter', 3)
            ->andReturn([
                ['id' => 'dok-a', 'specialty' => 'Anak'],
                ['id' => 'dok-b', 'specialty' => 'Gigi'],
            ]);

        $view = $this->controller->show('fac-1');

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('facility.show', $view->name());

        $data = $view->getData();
        $this->assertSame('fac-1', $data['facility']->id);
        $this->assertCount(2, $data['doctors']);
        $this->assertIsObject($data['doctors'][0]);
        $this->assertContains('Apotek', $data['facilities']);
        $this->assertContains('Laboratorium', $data['facilities']);
        $this->assertNotEmpty($data['galleryImages']);
    }

    public function test_show_aborts_with_404_when_facility_missing(): void
    {
        $this->firestore->shouldReceive('find')
            ->with('facilities', 'ghost')
            ->andReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->show('ghost');
    }
}
