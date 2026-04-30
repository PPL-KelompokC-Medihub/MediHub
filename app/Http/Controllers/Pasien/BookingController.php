<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pasien\StoreBookingRequest;
use App\Services\Pasien\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Booking jadwal temu oleh pasien.
 *
 * Sumber: PBI-11 / KFP-06.
 *
 * - GET  /pasien/booking          → form booking (autofill data pasien)
 * - POST /pasien/booking          → simpan booking baru ke Firestore
 *
 * Form Request: {@see StoreBookingRequest}
 * Service     : {@see BookingService}
 */
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
}
