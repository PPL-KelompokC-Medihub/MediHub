<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Auth shell untuk halaman generic (alias) dan logout.
 *
 * Login & register sebenarnya dikerjakan oleh FirebaseSessionController
 * (lihat di folder yang sama). Controller ini cuma menampilkan view
 * alternatif dan memutus session saat logout.
 */
class AuthController extends Controller
{
    public function showSignIn(): View
    {
        return view('auth.pasien.login');
    }

    public function showSignUp(): View
    {
        return view('auth.pasien.register');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->forgetUser();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
