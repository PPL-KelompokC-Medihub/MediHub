<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dokter\UpdateCertificationRequest;
use App\Http\Requests\Dokter\UpdateExpertiseRequest;
use App\Http\Requests\Dokter\UpdatePersonalRequest;
use App\Models\FirestoreUser;
use App\Domain\Dokter\DokterProfile;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * 3-step onboarding profil dokter.
 *
 * Sesuai PBI-04 / KFD-03 — dokter wajib mengisi data pribadi, keahlian,
 * lalu mengunggah dokumen (STR, SIP, KTP, ijazah, foto profil) dan
 * sertifikasi sebelum bisa mengakses dashboard.
 *
 * Step 1: showPersonal/updatePersonal           -> data pribadi
 * Step 2: showExpertise/updateExpertise         -> keahlian & spesialisasi
 * Step 3: showCertification/updateCertification -> dokumen + sertifikasi
 *
 * Setelah ketiga step selesai, middleware
 * `EnsureDokterProfileCompleted` akan mengizinkan akses dashboard.
 */
class ProfileController extends Controller
{
    public const AVAILABLE_SERVICES = [
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

    /**
     * PBI-13: Halaman Profil Dokter (read-only view).
     *
     * Menampilkan seluruh informasi pribadi dan profesional dokter
     * yang sudah terdaftar — data diri, alamat, keahlian, dokumen,
     * dan sertifikasi.
     */
    public function show(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        return view('dokter.profil.show', [
            'user' => $userData,
            'services' => self::AVAILABLE_SERVICES,
        ]);
    }

    public function showPersonal(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        return view('dokter.profile.personal', [
            'user' => $userData,
            'profileCompleted' => DokterProfile::isComplete($userData),
        ]);
    }

    public function updatePersonal(UpdatePersonalRequest $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUserData();

        if (($currentUser['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        $updatedUser = $this->doctorRepository->updateDoctorPersonal(
            (string) $currentUser['id'],
            $request->validated(),
        );
        Auth::setUser(new FirestoreUser($updatedUser));

        return redirect()->route('dokter.profile.expertise')
            ->with('success', 'Data diri dokter berhasil disimpan.');
    }

    public function showExpertise(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        if (! DokterProfile::hasPersonalData($userData)) {
            return redirect()->route('dokter.profile.personal');
        }

        return view('dokter.profile.expertise', [
            'user' => $userData,
            'services' => self::AVAILABLE_SERVICES,
        ]);
    }

    public function updateExpertise(UpdateExpertiseRequest $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUserData();

        if (($currentUser['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        if (! DokterProfile::hasPersonalData($currentUser)) {
            return redirect()->route('dokter.profile.personal');
        }

        $updatedUser = $this->doctorRepository->updateDoctorExpertise(
            (string) $currentUser['id'],
            $request->validated(),
        );
        Auth::setUser(new FirestoreUser($updatedUser));

        return redirect()->route('dokter.profile.certification')
            ->with('success', 'Keahlian dokter berhasil disimpan. Silakan lengkapi sertifikasi Anda.');
    }

    public function showCertification(): View|RedirectResponse
    {
        $userData = $this->getCurrentUserData();

        if (($userData['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        if (! DokterProfile::hasExpertiseData($userData)) {
            return redirect()->route('dokter.profile.expertise');
        }

        return view('dokter.profile.certification', [
            'user' => $userData,
        ]);
    }

    public function updateCertification(UpdateCertificationRequest $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUserData();

        if (($currentUser['role'] ?? null) !== 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        if (! DokterProfile::hasExpertiseData($currentUser)) {
            return redirect()->route('dokter.profile.expertise');
        }

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

        return redirect()->route('dokter.dashboard')
            ->with('success', 'Pendaftaran dokter selesai! Selamat datang di MediHub.');
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
