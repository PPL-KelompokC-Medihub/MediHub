<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * CRUD jadwal praktik dokter.
 *
 * Pasien akan memilih jadwal yang dibuat di sini saat booking. Data
 * disimpan di collection Firestore "JadwalDokter" dengan field:
 * dokterid, tanggal, jam_mulai, jam_selesai.
 *
 * Sumber: PBI-07 / KFD-04.
 */
class ScheduleController extends Controller
{
    use MapsFirestoreData;

    private const COLLECTION = 'JadwalDokter';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index(): View
    {
        $dokterid = Auth::id();

        $jadwal = $this->toObjects(
            $this->firestore->where(self::COLLECTION, 'dokterid', '=', $dokterid)
        );

        // Kelompokkan per minggu
        $mingguIni = [];
        $mingguDepan = [];

        $startMingguIni = now()->startOfWeek();
        $endMingguIni = now()->endOfWeek();
        $startMingguDepan = now()->addWeek()->startOfWeek();
        $endMingguDepan = now()->addWeek()->endOfWeek();

        foreach ($jadwal as $j) {
            $tanggal = \Carbon\Carbon::parse($j->tanggal ?? null);
            if ($tanggal->between($startMingguIni, $endMingguIni)) {
                $mingguIni[] = $j;
            } elseif ($tanggal->between($startMingguDepan, $endMingguDepan)) {
                $mingguDepan[] = $j;
            }
        }

        return view('dokter.jadwal', compact('mingguIni', 'mingguDepan', 'jadwal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|string',
            'jam_selesai' => 'required|string',
        ]);

        $this->firestore->add(self::COLLECTION, [
            'dokterid' => Auth::id(),
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|string',
            'jam_selesai' => 'required|string',
        ]);

        $this->firestore->update(self::COLLECTION, $id, $validated);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $this->firestore->delete(self::COLLECTION, $id);

        return response()->json(['success' => true]);
    }
}
