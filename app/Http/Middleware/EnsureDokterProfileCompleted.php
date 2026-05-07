<?php

namespace App\Http\Middleware;

use App\Domain\Dokter\DokterProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDokterProfileCompleted
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
            ! DokterProfile::isComplete($userData) &&
            ! $request->routeIs(
                'dokter.profile.personal',
                'dokter.profile.personal.update',
                'dokter.profile.expertise',
                'dokter.profile.expertise.update',
                'dokter.profile.certification',
                'dokter.profile.certification.update',
                'logout',
            )
        ) {
            if (! DokterProfile::hasPersonalData($userData)) {
                return redirect()->route('dokter.profile.personal');
            }

            if (! DokterProfile::hasExpertiseData($userData)) {
                return redirect()->route('dokter.profile.expertise');
            }

            return redirect()->route('dokter.profile.certification');
        }

        return $next($request);
    }
}
