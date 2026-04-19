<?php

namespace App\Http\Middleware;

use App\Support\DoctorProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctorProfileCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $userData = method_exists($user, 'getAttributes') ? $user->getAttributes() : [];

        if (
            ($userData['role'] ?? null) === 'dokter' &&
            ! DoctorProfile::isComplete($userData) &&
            ! $request->routeIs(
                'doctor.profile-form',
                'doctor.profile.update',
                'doctor.profile.expertise',
                'doctor.profile.expertise.update',
                'doctor.profile.certification',
                'doctor.profile.certification.update',
                'logout',
            )
        ) {
            if (! DoctorProfile::hasPersonalData($userData)) {
                return redirect()->route('doctor.profile-form');
            }

            if (! DoctorProfile::hasExpertiseData($userData)) {
                return redirect()->route('doctor.profile.expertise');
            }

            return redirect()->route('doctor.profile.certification');
        }

        return $next($request);
    }
}
