<?php

namespace App\Http\Controllers;

use App\Models\FirestoreUser;
use App\Services\MedihubFirestoreRepository;
use App\Support\DoctorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const AVAILABLE_SERVICES = [
        'Umum',
        'Anak',
        'Penyakit Dalam',
        'Bedah',
        'Gigi dan Mulut',
        'Kandungan',
    ];

    public function __construct(
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    public function show(): View
    {
        return view('profile.show');
    }

    public function showDoctorForm(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dashboard');
        }

        return view('doctor.profile-form1', [
            'user' => $userData,
            'profileCompleted' => DoctorProfile::isComplete($userData),
        ]);
    }

    public function updateDoctorForm(Request $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUserData();

        if (($currentUser['role'] ?? null) !== 'dokter') {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'weight' => ['required', 'numeric', 'min:20', 'max:300'],
            'height' => ['required', 'numeric', 'min:100', 'max:250'],
            'gender' => ['required', 'string', 'in:Perempuan,Pria'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
        ]);

        $updatedUser = $this->doctorRepository->updateDoctorPersonal((string) $currentUser['id'], $validated);
        Auth::setUser(new FirestoreUser($updatedUser));

        return redirect()->route('doctor.profile.expertise')->with('success', 'Data diri dokter berhasil disimpan.');
    }

    public function showDoctorExpertiseForm(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dashboard');
        }

        if (! DoctorProfile::hasPersonalData($userData)) {
            return redirect()->route('doctor.profile-form');
        }

        return view('doctor.profile-expertise', [
            'user' => $userData,
            'services' => self::AVAILABLE_SERVICES,
        ]);
    }

    public function updateDoctorExpertiseForm(Request $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUserData();

        if (($currentUser['role'] ?? null) !== 'dokter') {
            return redirect()->route('dashboard');
        }

        if (! DoctorProfile::hasPersonalData($currentUser)) {
            return redirect()->route('doctor.profile-form');
        }

        $validated = $request->validate([
            'specialty' => ['required', 'string', 'max:100'],
            'sub_specialty' => ['nullable', 'string', 'max:100'],
            'started_practice_year' => ['required', 'integer', 'min:1950', 'max:' . now()->year],
            'education_institution' => ['required', 'string', 'max:255'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'string', 'max:100'],
            'bio' => ['required', 'string', 'max:2000'],
        ]);

        $updatedUser = $this->doctorRepository->updateDoctorExpertise((string) $currentUser['id'], $validated);
        Auth::setUser(new FirestoreUser($updatedUser));

        return redirect()->route('doctor.profile.certification')->with('success', 'Keahlian dokter berhasil disimpan. Silakan lengkapi sertifikasi Anda.');
    }

    public function showDoctorCertificationForm(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dashboard');
        }

        if (! DoctorProfile::hasExpertiseData($userData)) {
            return redirect()->route('doctor.profile.expertise');
        }

        return view('doctor.profile-certification', [
            'user' => $userData,
        ]);
    }

    public function updateDoctorCertificationForm(Request $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUserData();

        if (($currentUser['role'] ?? null) !== 'dokter') {
            return redirect()->route('dashboard');
        }

        if (! DoctorProfile::hasExpertiseData($currentUser)) {
            return redirect()->route('doctor.profile.expertise');
        }

        $validated = $request->validate([
            'str_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'sip_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'ijazah_doctor' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'ktp_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'profile_pict' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'certifications' => ['required', 'array', 'min:1', 'max:6'],
            'certifications.*' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
        ]);

        $userId = (string) $currentUser['id'];
        $storedDocuments = [
            'STR' => $this->storeDoctorFile($request->file('str_document'), $userId, 'documents'),
            'SIP' => $this->storeDoctorFile($request->file('sip_document'), $userId, 'documents'),
            'ijazah_doctor' => $this->storeDoctorFile($request->file('ijazah_doctor'), $userId, 'documents'),
            'KTP' => $this->storeDoctorFile($request->file('ktp_document'), $userId, 'documents'),
            'profile_pict' => $this->storeDoctorFile($request->file('profile_pict'), $userId, 'documents'),
        ];

        $storedCertifications = array_map(
            fn (UploadedFile $file): string => $this->storeDoctorFile($file, $userId, 'certifications'),
            $request->file('certifications', []),
        );

        $updatedUser = $this->doctorRepository->updateDoctorCertification($userId, [
            'documents' => $storedDocuments,
            'certifications' => $storedCertifications,
        ]);
        Auth::setUser(new FirestoreUser($updatedUser));

        return redirect()->route('dashboard')->with('success', 'Pendaftaran dokter selesai! Selamat datang di MediHub.');
    }

    /**
     * @return array<string, mixed>
     */
    private function getCurrentUserData(): array
    {
        $userId = (string) Auth::id();
        $userData = $this->doctorRepository->findUser($userId);

        abort_if(! $userData, 404);

        return $this->doctorRepository->hydrateDoctorData($userData);
    }

    private function storeDoctorFile(?UploadedFile $file, string $userId, string $directory): string
    {
        abort_if(! $file, 422, 'File dokumen dokter tidak valid.');

        return $file->store("doctor/{$userId}/{$directory}", 'public');
    }
}
