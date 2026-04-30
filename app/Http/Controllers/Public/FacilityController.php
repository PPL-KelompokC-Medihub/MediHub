<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Halaman fasilitas/rumah sakit.
 *
 * Sumber: PBI-09. Menampilkan detail fasilitas dengan dokter terkait,
 * fasilitas yang tersedia (UGD, apotek, lab, radiologi), dan galeri.
 */
class FacilityController extends Controller
{
    use MapsFirestoreData;

    private const COLLECTION = 'facilities';
    private const RELATED_DOCTORS_LIMIT = 3;
    private const AVAILABLE_FEATURES = ['24 Jam UGD', 'Apotek', 'Laboratorium', 'Radiologi', 'Rawat Inap'];
    private const GALLERY_IMAGES = [
        'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=400&h=300&fit=crop',
        'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?w=400&h=300&fit=crop',
        'https://images.unsplash.com/photo-1516549655169-df83a0774514?w=400&h=300&fit=crop',
    ];

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->firestore->all(self::COLLECTION));
    }

    public function show(string $id): View
    {
        $facility = $this->firestore->find(self::COLLECTION, $id);

        if (! $facility) {
            abort(404);
        }

        $doctors = $this->toObjects($this->firestore->all('Dokter', self::RELATED_DOCTORS_LIMIT));
        $facilities = self::AVAILABLE_FEATURES;
        $galleryImages = self::GALLERY_IMAGES;
        $facility = $this->toObject($facility);

        return view('facility.show', compact('facility', 'doctors', 'facilities', 'galleryImages'));
    }
}
