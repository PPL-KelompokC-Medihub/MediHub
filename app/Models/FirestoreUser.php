<?php

namespace App\Models;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class FirestoreUser extends GenericUser implements MustVerifyEmail
{
    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

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
        // Tidak perlu implementasi karena email verification tidak digunakan
    }
}
