document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('firebase-sign-up-form');
    const successModal = document.getElementById('sign-up-success-modal');
    const successButton = document.getElementById('sign-up-success-login-btn');
    const errorEl = document.getElementById('firebase-auth-error');
    const signInUrl = form?.dataset.signInUrl || '/login-pasien';
    const registerUrl = form?.dataset.registerUrl || '/auth/firebase/register';
    const firebaseApiKey = form?.dataset.firebaseApiKey || '';
    const submitButton = document.getElementById('sign-up-submit');
    const submitText = submitButton?.querySelector('.btn-text');
    const submitLoading = submitButton?.querySelector('.btn-loading');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const matchHint = document.getElementById('password-match-hint');
    const strengthFill = document.getElementById('password-strength-fill');
    const strengthText = document.getElementById('password-strength-text');
    const ruleLength = document.getElementById('rule-length');
    const ruleNumber = document.getElementById('rule-number');
    const ruleCase = document.getElementById('rule-case');
    const ruleSymbol = document.getElementById('rule-symbol');

    if (!form || !successModal || !passwordInput || !passwordConfirmInput || !submitButton) {
        return;
    }

    if (!firebaseApiKey) {
        showError('Konfigurasi Firebase API key belum tersedia.');
        return;
    }

    let redirectScheduled = false;
    let serverRedirectUrl = signInUrl; // fallback

    const redirectAfterSignup = () => {
        if (redirectScheduled) {
            return;
        }

        redirectScheduled = true;
        window.setTimeout(() => {
            window.location.assign(serverRedirectUrl);
        }, 1200);
    };

    successButton?.addEventListener('click', redirectAfterSignup);

    const triggerRedirectWhenVisible = () => {
        if (!successModal.hidden) {
            redirectAfterSignup();
        }
    };

    new MutationObserver(triggerRedirectWhenVisible).observe(successModal, {
        attributes: true,
        attributeFilter: ['hidden', 'class', 'style'],
    });

    window.addEventListener('mediq:signup-success', redirectAfterSignup);
    triggerRedirectWhenVisible();

    document.getElementById('toggle-password')?.addEventListener('click', () => {
        togglePasswordField(passwordInput);
    });

    document.getElementById('toggle-confirm')?.addEventListener('click', () => {
        togglePasswordField(passwordConfirmInput);
    });

    passwordInput.addEventListener('input', () => {
        updatePasswordFeedback(passwordInput.value);
        updatePasswordMatchHint();
    });
    passwordConfirmInput.addEventListener('input', updatePasswordMatchHint);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        showError('');
        
        // Hide server-side blade error if present
        const bladeError = form.querySelector('.mediq-error:not(#firebase-auth-error)');
        if (bladeError) {
            bladeError.hidden = true;
        }

        const formData = new FormData(form);
        const name = String(formData.get('name') ?? '').trim();
        const email = String(formData.get('email') ?? '').trim();
        const password = String(formData.get('password') ?? '');
        const passwordConfirmation = String(formData.get('password_confirmation') ?? '');
        const role = String(formData.get('role') ?? 'pasien');

        if (password !== passwordConfirmation) {
            showError('Konfirmasi kata sandi belum sesuai.');
            updatePasswordMatchHint();
            return;
        }

        setSubmitting(true);

        try {
            const signUpResult = await registerWithFirebase(email, password, firebaseApiKey);
            console.log('SIGN UP RESULT:', signUpResult);
            console.log('ID TOKEN:', signUpResult.idToken);
            await sendVerificationEmail(signUpResult.idToken, firebaseApiKey);
            await syncUserToBackend(registerUrl, {
                id_token: signUpResult.idToken,
                name,
                role,
            });

            // Setelah berhasil daftar pasien, arahkan ke login pasien
            serverRedirectUrl = signInUrl;

            successModal.hidden = false;
            window.dispatchEvent(new CustomEvent('mediq:signup-success'));
        } catch (error) {
            console.error('REGISTER ERROR:', error);
            showError(mapErrorMessage(error));
        } finally {
            setSubmitting(false);
        }
    });

    updatePasswordFeedback(passwordInput.value);
    updatePasswordMatchHint();

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

        const trimmed = message.trim();
        errorEl.textContent = trimmed;
        errorEl.hidden = trimmed.length === 0;
    }

    function updatePasswordFeedback(password) {
        const hasLength = password.length >= 8;
        const hasNumber = /\d/.test(password);
        const hasCase = /[a-z]/.test(password) && /[A-Z]/.test(password);
        const hasSymbol = /[^A-Za-z0-9]/.test(password);
        const score = [hasLength, hasNumber, hasCase, hasSymbol].filter(Boolean).length;

        if (ruleLength) ruleLength.checked = hasLength;
        if (ruleNumber) ruleNumber.checked = hasNumber;
        if (ruleCase) ruleCase.checked = hasCase;
        if (ruleSymbol) ruleSymbol.checked = hasSymbol;

        if (!strengthFill || !strengthText) {
            return;
        }

        const width = (score / 4) * 100;
        strengthFill.style.width = `${width}%`;

        if (score <= 1) {
            strengthFill.style.backgroundColor = '#e65b54';
            strengthText.textContent = 'Kekuatan kata sandi: lemah';
            return;
        }

        if (score <= 3) {
            strengthFill.style.backgroundColor = '#f1a33b';
            strengthText.textContent = 'Kekuatan kata sandi: sedang';
            return;
        }

        strengthFill.style.backgroundColor = '#46b96c';
        strengthText.textContent = 'Kekuatan kata sandi: kuat';
    }

    function updatePasswordMatchHint() {
        if (!matchHint) {
            return;
        }

        const hasConfirmValue = passwordConfirmInput.value.length > 0;
        const isMatch = passwordInput.value === passwordConfirmInput.value;

        if (!hasConfirmValue || isMatch) {
            matchHint.hidden = true;
            return;
        }

        matchHint.hidden = false;
    }
});

function togglePasswordField(input) {
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
}

async function registerWithFirebase(email, password, apiKey) {
    const response = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=${apiKey}`, {
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
        throw new Error(json?.error?.message || 'REGISTER_FAILED');
    }

    return json;
}

async function sendVerificationEmail(idToken, apiKey) {
    const response = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=${apiKey}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            requestType: 'VERIFY_EMAIL',
            idToken,
        }),
    });

    if (!response.ok) {
        const json = await response.json();
        throw new Error(json?.error?.message || 'VERIFY_EMAIL_FAILED');
    }
}

async function syncUserToBackend(url, payload) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });

    const json = await response.json();
    if (!response.ok) {
        throw new Error(json?.message || 'SYNC_REGISTER_FAILED');
    }

    return json;
}

function mapErrorMessage(error) {
    const code = (error instanceof Error ? error.message : String(error)).toUpperCase();

    if (code.includes('EMAIL_EXISTS')) {
        return 'Email sudah terdaftar. Silakan login.';
    }
    if (code.includes('INVALID_EMAIL')) {
        return 'Format email tidak valid.';
    }
    if (code.includes('WEAK_PASSWORD')) {
        return 'Kata sandi terlalu lemah. Gunakan kombinasi yang lebih kuat.';
    }
    if (code.includes('VERIFY_EMAIL_FAILED')) {
        return 'Akun dibuat, tapi gagal kirim email verifikasi. Coba login untuk kirim ulang.';
    }

    return 'Registrasi gagal. Silakan coba lagi.';
}
