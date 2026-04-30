<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $userData = method_exists($user, 'getAttributes') ? $user->getAttributes() : [];
        $role = $this->normalizeRole($userData['role'] ?? $request->session()->get('medihub_user_role'));
        $allowedRoles = array_map(fn (string $value): string => $this->normalizeRole($value), $roles);

        if (in_array($role, $allowedRoles, true)) {
            return $next($request);
        }

        if ($role === 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        if ($role === 'pasien') {
            return redirect()->route('pasien.beranda');
        }

        Auth::guard('web')->forgetUser();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login-pasien');
    }

    private function normalizeRole(mixed $role): string
    {
        $normalized = strtolower(trim((string) $role));

        return match ($normalized) {
            'doctor', 'dokter' => 'dokter',
            'patient', 'pasien' => 'pasien',
            default => $normalized,
        };
    }
}
