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
        'STR'           => $this->storeDoctorFileIfExists($request->file('str_document'), $userId, 'documents') ?? ($currentUser['STR'] ?? null),
        'SIP'           => $this->storeDoctorFileIfExists($request->file('sip_document'), $userId, 'documents') ?? ($currentUser['SIP'] ?? null),
        'ijazah_doctor' => $this->storeDoctorFileIfExists($request->file('ijazah_doctor'), $userId, 'documents') ?? ($currentUser['ijazah_doctor'] ?? null),
        'KTP'           => $this->storeDoctorFileIfExists($request->file('ktp_document'), $userId, 'documents') ?? ($currentUser['KTP'] ?? null),
        'profile_pict'  => $this->storeDoctorFileIfExists($request->file('profile_pict'), $userId, 'documents') ?? ($currentUser['profile_pict'] ?? null),
        ];

       $existingCerts = [];
        for ($i = 0; $i < 6; $i++) {
            $existingCerts[$i] = $currentUser['certification' . ($i + 1)] ?? null;
        }

        foreach ($request->file('certifications', []) as $index => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $existingCerts[$index] = $this->storeDoctorFileIfExists($file, $userId, 'certifications');
            }
        }

        $storedCertifications = array_values(array_filter($existingCerts));

        $updatedUser = $this->doctorRepository->updateDoctorCertification($userId, [
            'documents'      => $storedDocuments,
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

    private function storeDoctorFileIfExists(?UploadedFile $file, string $userId, string $directory): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        return $file->store("doctor/{$userId}/{$directory}", 'public');
    }
}