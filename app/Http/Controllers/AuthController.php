<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showSignIn(): View
    {
        return view('auth-pasien.login-pasien');
    }

    public function showSignUp(): View
    {
        return view('auth-pasien.register-pasien');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->forgetUser();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
