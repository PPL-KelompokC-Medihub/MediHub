<?php

use App\Http\Middleware\EnsureDokterProfileCompleted;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request): string {
            $doctorOnlyPaths = [
                'dokter/appointment*',
                'dokter/catatan-medis*',
                'dokter/dashboard',
                'dokter/jadwal*',
                'dokter/profil',
                'dokter/profile*',
                'dokter/riwayat',
            ];

            foreach ($doctorOnlyPaths as $path) {
                if ($request->is($path)) {
                    return route('login-dokter');
                }
            }

            return route('login-pasien');
        });

        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();
            $userData = $user && method_exists($user, 'getAttributes') ? $user->getAttributes() : [];

            $role = strtolower(trim((string) ($userData['role'] ?? $request->session()->get('medihub_user_role', ''))));
            $role = match ($role) {
                'doctor' => 'dokter',
                'patient' => 'pasien',
                default => $role,
            };

            return match ($role) {
                'dokter' => route('dokter.dashboard'),
                'pasien' => route('pasien.beranda'),
                default => route('dashboard'),
            };
        });

        $middleware->alias([
            'dokter.profile.completed' => EnsureDokterProfileCompleted::class,
            'role' => EnsureUserRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            return back()->withErrors([
                'files' => 'Total ukuran dokumen terlalu besar. Maksimal total upload adalah 8 MB, dan setiap file maksimal 2 MB.',
            ]);
        });
    })->create();
