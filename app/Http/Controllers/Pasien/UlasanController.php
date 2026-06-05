<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mengelola ulasan pasien terhadap layanan dokter.
 *
 * Sumber: PBI-23 (CR — Create & Read).
 *
 * - POST /pasien/ulasan          → buat ulasan baru
 * - GET  /pasien/ulasan/{id}     → ambil ulasan by dokter ID (JSON, untuk AJAX)
 */
class UlasanController extends Controller
{
    private const ULASAN_COLLECTION      = 'Ulasan';
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const PATIENT_COLLECTION     = 'Pasien';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    /**
     * Buat ulasan baru.
     *
     * POST /pasien/ulasan
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'string'],
            'rating'    => ['required', 'integer', 'min:1', 'max:5'],
            'ulasan'    => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.min'  => 'Rating minimal 1 bintang.',
            'rating.max'  => 'Rating maksimal 5 bintang.',
            'ulasan.min'  => 'Ulasan harus minimal 10 karakter.',
            'ulasan.max'  => 'Ulasan maksimal 1000 karakter.',
            'ulasan.required' => 'Ulasan tidak boleh kosong.',
        ]);

        $userId  = (string) Auth::id();
        $user    = Auth::user();

        // Cek apakah pasien pernah booking dengan dokter ini
        $hasAppointment = $this->hasCompletedAppointmentWithDoctor($userId, $validated['doctor_id']);

        if (! $hasAppointment) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Belum ada riwayat kunjungan dengan dokter ini.'], 403);
            }
            return back()->withErrors(['ulasan' => 'Anda belum pernah berkunjung ke dokter ini.']);
        }

        // Cek apakah sudah pernah membuat ulasan untuk dokter yang sama
        $existing = $this->findExistingReview($userId, $validated['doctor_id']);
        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda sudah memberikan ulasan untuk dokter ini.'], 409);
            }
            return back()->withErrors(['ulasan' => 'Anda sudah memberikan ulasan untuk dokter ini.']);
        }

        // Ambil nama pasien
        $patientName = $this->resolvePatientName($userId, $user);

        $this->firestore->add(self::ULASAN_COLLECTION, [
            'patient_id'   => $userId,
            'patient_name' => $patientName,
            'doctor_id'    => $validated['doctor_id'],
            'rating'       => (int) $validated['rating'],
            'ulasan'       => $validated['ulasan'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Ulasan berhasil ditambahkan.']);
        }

        return back()->with('success', 'Ulasan berhasil ditambahkan!');
    }

    /**
     * Ambil daftar ulasan berdasarkan ID dokter (untuk AJAX).
     *
     * GET /pasien/ulasan/{doctorId}
     */
    public function index(string $doctorId): JsonResponse
    {
        $reviews = $this->firestore->where(self::ULASAN_COLLECTION, 'doctor_id', '=', $doctorId);

        // Urutkan dari terbaru
        usort($reviews, fn ($a, $b) => strcmp(
            (string) ($b['created_at'] ?? ''),
            (string) ($a['created_at'] ?? ''),
        ));

        $formatted = array_map(function (array $review): array {
            $name   = (string) ($review['patient_name'] ?? 'Pasien');
            $initials = strtoupper(substr($name, 0, 1));

            return [
                'id'           => $review['id'],
                'patient_name' => $name,
                'avatar_url'   => "https://ui-avatars.com/api/?name={$initials}&background=6aa4ef&color=fff&size=64",
                'rating'       => (int) ($review['rating'] ?? 0),
                'ulasan'       => (string) ($review['ulasan'] ?? ''),
                'created_at'   => $review['created_at'] ?? '',
            ];
        }, $reviews);

        $avgRating = count($formatted) > 0
            ? round(array_sum(array_column($formatted, 'rating')) / count($formatted), 1)
            : 0;

        return response()->json([
            'success'    => true,
            'reviews'    => $formatted,
            'avg_rating' => $avgRating,
            'total'      => count($formatted),
        ]);
    }

    /**
     * Cek apakah pasien punya jadwal temu selesai dengan dokter.
     */
    private function hasCompletedAppointmentWithDoctor(string $userId, string $doctorId): bool
    {
        $appointments = $this->firestore->where(self::APPOINTMENT_COLLECTION, 'doctor_id', '=', $doctorId);

        foreach ($appointments as $appointment) {
            $belongsToPatient = in_array($userId, [
                (string) ($appointment['patient_id'] ?? ''),
                (string) ($appointment['user_uid']   ?? ''),
            ], true);

            if (! $belongsToPatient) {
                continue;
            }

            $status = strtolower((string) ($appointment['status'] ?? ''));
            if (in_array($status, ['selesai', 'done', 'completed'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah pasien sudah pernah memberi ulasan untuk dokter yang sama.
     */
    private function findExistingReview(string $userId, string $doctorId): ?array
    {
        $reviews = $this->firestore->where(self::ULASAN_COLLECTION, 'doctor_id', '=', $doctorId);

        foreach ($reviews as $review) {
            if ((string) ($review['patient_id'] ?? '') === $userId) {
                return $review;
            }
        }

        return null;
    }

    /**
     * Ambil nama pasien dari koleksi Pasien atau fallback ke nama akun.
     */
    private function resolvePatientName(string $userId, mixed $user): string
    {
        $patients = $this->firestore->where(self::PATIENT_COLLECTION, 'user_id', '=', $userId, 1);
        $patient  = $patients[0] ?? null;

        if ($patient) {
            $fullname = trim((string) ($patient['fullname'] ?? $patient['nama'] ?? ''));
            if ($fullname !== '') {
                return $fullname;
            }
        }

        return (string) ($user?->name ?? $user?->fullname ?? 'Pasien');
    }
}
