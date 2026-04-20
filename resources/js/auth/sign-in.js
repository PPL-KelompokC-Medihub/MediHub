import { signInWithGoogleViaFirebase } from './google-auth';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('firebase-sign-in-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const errorEl = document.getElementById('firebase-auth-error');
    const submitButton = document.getElementById('sign-in-submit');
    const submitText = submitButton?.querySelector('.btn-text');
    const submitLoading = submitButton?.querySelector('.btn-loading');
    const googleButton = document.getElementById('google-sign-in-btn');
    const sessionUrl = form?.dataset.sessionUrl || '/auth/firebase/session';
    const firebaseApiKey = form?.dataset.firebaseApiKey || '';
    const googleClientId = form?.dataset.googleClientId || '';

    if (!form || !emailInput || !passwordInput || !submitButton) {
        return;
    }

    if (googleButton && !googleClientId) {
        googleButton.disabled = true;
        googleButton.title = 'Google Client ID belum diatur di server.';
    }

    document.getElementById('toggle-password')?.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        showError('');
        
        // Hide server-side blade error if present
        const bladeError = form.querySelector('.mediq-error:not(#firebase-auth-error)');
        if (bladeError) {
            bladeError.hidden = true;
        }

        if (!firebaseApiKey) {
            showError('Konfigurasi Firebase API key belum tersedia.');
            return;
        }

        setSubmitting(true);

        try {
            const signInResult = await signInWithFirebase(
                emailInput.value.trim(),
                passwordInput.value,
                firebaseApiKey,
            );

            const sessionResult = await createBackendSession(sessionUrl, signInResult.idToken);
            window.location.assign(sessionResult.redirect || '/dashboard');
        } catch (error) {
            showError(mapErrorMessage(error));
        } finally {
            setSubmitting(false);
        }
    });

    googleButton?.addEventListener('click', async () => {
        showError('');

        setSubmitting(true);
        if (googleButton) {
            googleButton.disabled = true;
        }

        try {
            const googleSignInResult = await signInWithGoogleViaFirebase({
                googleClientId,
                firebaseApiKey,
            });

            const sessionResult = await createBackendSession(sessionUrl, googleSignInResult.idToken);
            window.location.assign(sessionResult.redirect || '/dashboard');
        } catch (error) {
            showError(mapErrorMessage(error));
        } finally {
            setSubmitting(false);
            if (googleButton) {
                googleButton.disabled = false;
            }
        }
    });

    function setSubmitting(isSubmitting) {
        submitButton.disabled = isSubmitting;
        if (submitText) {
            submitText.hidden = isSubmitting;
        }
        if (submitLoading) {
            submitLoading.hidden = !isSubmitting;
        }
    }

    function showError(message) {
        if (!errorEl) {
            return;
        }

        const text = message.trim();
        errorEl.textContent = text;
        errorEl.hidden = text.length === 0;
    }
});

async function signInWithFirebase(email, password, apiKey) {
    const response = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=${apiKey}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            email,
            password,
            returnSecureToken: true,
        }),
    });

    const json = await response.json();
    if (!response.ok) {
        throw new Error(json?.error?.message || 'LOGIN_FAILED');
    }

    return json;
}

async function createBackendSession(url, idToken) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            id_token: idToken,
            role: 'dokter',
        }),
    });

    const json = await response.json();
    if (!response.ok) {
        throw new Error(json?.message || 'SESSION_FAILED');
    }

    return json;
}

function mapErrorMessage(error) {
    const code = (error instanceof Error ? error.message : String(error)).toUpperCase();

    if (code.includes('GOOGLE_CLIENT_ID_MISSING')) {
        return 'Google Client ID belum diatur di server.';
    }
    if (code.includes('FIREBASE_API_KEY_MISSING')) {
        return 'Firebase API key belum diatur.';
    }
    if (code.includes('KREDENSIAL FIREBASE ADMIN TIDAK VALID')) {
        return 'Kredensial Firebase Admin tidak valid. Buat ulang service account key lalu update FIREBASE_CREDENTIALS.';
    }
    if (code.includes('GOOGLE_SCRIPT_LOAD_FAILED') || code.includes('GOOGLE_API_NOT_AVAILABLE')) {
        return 'Gagal memuat layanan Google. Coba refresh halaman.';
    }
    if (code.includes('POPUP_CLOSED') || code.includes('ACCESS_DENIED') || code.includes('USER_CANCEL')) {
        return 'Login Google dibatalkan.';
    }
    if (code.includes('GOOGLE_FIREBASE_EXCHANGE_FAILED') || code.includes('INVALID_IDP_RESPONSE')) {
        return 'Akun Google tidak dapat diverifikasi oleh Firebase.';
    }

    if (code.includes('EMAIL_NOT_FOUND') || code.includes('INVALID_LOGIN_CREDENTIALS')) {
        return 'Email atau kata sandi salah.';
    }
    if (code.includes('INVALID_PASSWORD')) {
        return 'Kata sandi tidak sesuai.';
    }
    if (code.includes('INVALID_EMAIL')) {
        return 'Format email tidak valid.';
    }
    if (code.includes('TOO_MANY_ATTEMPTS_TRY_LATER')) {
        return 'Terlalu banyak percobaan. Coba beberapa saat lagi.';
    }
    if (code.includes('AKUN BELUM DIVERIFIKASI')) {
        return 'Akun belum diverifikasi. Cek email verifikasi Anda.';
    }
    if (code.includes('JENIS AKUN TIDAK SESUAI')) {
        return 'Jenis akun tidak sesuai. Silakan login melalui halaman yang benar.';
    }

    return 'Login gagal. Silakan coba lagi.';
}
