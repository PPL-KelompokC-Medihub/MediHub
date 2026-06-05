<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Services\Pasien\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private FirestoreService $firestore,
    ) {}

    public function index()
    {
        return view('pasien.beranda', $this->dashboardService->dashboardData());
    }

    public function layanan()
    {
        return view('pasien.layanan', $this->dashboardService->servicePageData());
    }

    public function storeReview(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['required', 'string', 'min:8', 'max:1000'],
        ], [
            'rating.required' => 'Rating wajib dipilih.',
            'text.required' => 'Ulasan wajib diisi.',
            'text.min' => 'Ulasan terlalu singkat.',
        ]);

        $userId = (string) Auth::id();
        $user = Auth::user();
        $userData = $this->firestore->find('Users', $userId) ?? [];

        $this->firestore->add('Ulasan', [
            'patient_id' => $userId,
            'user_id' => $userId,
            'patient_name' => $userData['fullname'] ?? $user?->fullname ?? $user?->name ?? 'Pasien',
            'patient_email' => $userData['email'] ?? $user?->email ?? null,
            'rating' => (int) $validated['rating'],
            'text' => $validated['text'],
            'likes' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        return redirect()
            ->route('pasien.layanan')
            ->with('success', 'Ulasan berhasil dikirim.');
    }
}
