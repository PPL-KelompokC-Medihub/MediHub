<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pasien\StoreBookingRequest;
use App\Services\Pasien\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, BookingService $bookingService): View
    {
        return view('pasien.booking', $bookingService->formData(
            (string) $request->query('doctor_id', ''),
        ));
    }

    public function store(
        StoreBookingRequest $request,
        BookingService $bookingService,
    ): RedirectResponse {
        $bookingService->createAppointment(
            $request->validated(),
            $request->file('medical_doc'),
        );

        return redirect()->route('pasien.beranda')->with('success', 'Jadwal temu berhasil dibuat.');
    }

    public function cancel(string $id, Request $request, BookingService $bookingService): RedirectResponse
    {
        $bookingService->cancel($id, $request->input('cancellation_reason'));

        return redirect()->route('pasien.riwayat')->with('success', 'Jadwal temu berhasil dibatalkan.');
    }

    public function destroy(Request $request, BookingService $bookingService): RedirectResponse
    {
        $validated = $request->validate([
            'appointments' => ['required', 'array', 'min:1'],
            'appointments.*' => ['required', 'string'],
        ], [
            'appointments.required' => 'Pilih jadwal temu yang ingin dibatalkan terlebih dahulu.',
            'appointments.array' => 'Pilih jadwal temu yang ingin dibatalkan terlebih dahulu.',
            'appointments.min' => 'Pilih jadwal temu yang ingin dibatalkan terlebih dahulu.',
        ]);

        $bookingService->deleteAppointment($validated['appointments']);

        return redirect()->route('pasien.beranda')->with('success', 'Jadwal temu berhasil dibatalkan.');
    }
}
