<?php

namespace App\Models;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class FirestoreUser extends GenericUser implements MustVerifyEmail
{
    public function hasVerifiedEmail(): bool
    {
        return (bool) ($this->attributes['email_verified'] ?? true);
    }

    public function markEmailAsVerified(): bool
    {
        $this->attributes['email_verified'] = true;

        return true;
    }

    public function getEmailForVerification(): string
    {
        return (string) ($this->attributes['email'] ?? '');
    }

    public function sendEmailVerificationNotification(): void
    {
        // Pengiriman email verifikasi ditangani via Firebase API
        // di javascript frontend (resources/js/auth/sign-up.js) 
        // sehingga metode ini dibiarkan kosong di sisi backend.
    }
}
