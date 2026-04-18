<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use App\Support\MapsFirestoreData;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use MapsFirestoreData;

    private const DASHBOARD_LIMIT = 4;

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index(): View
    {
        $facilities = $this->toObjects($this->firestore->all('facilities', self::DASHBOARD_LIMIT));
        $doctors = $this->toObjects($this->firestore->all('doctors', self::DASHBOARD_LIMIT));

        return view('dashboard.index', compact('facilities', 'doctors'));
    }
}
