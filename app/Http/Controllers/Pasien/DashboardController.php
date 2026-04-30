<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Services\Pasien\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function index()
    {
        return view('pasien.beranda', $this->dashboardService->dashboardData());
    }

    public function layanan()
    {
        return view('pasien.layanan', $this->dashboardService->servicePageData());
    }
}
