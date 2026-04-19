const GOOGLE_GSI_SCRIPT_SRC = 'https://accounts.google.com/gsi/client';

/**
 * Sign in with Google via GIS access token, then exchange it for Firebase ID token.
 * This keeps the backend flow unchanged (backend still verifies Firebase id_token).
 */
export async function signInWithGoogleViaFirebase({
    googleClientId,
    firebaseApiKey,
}) {
    if (!googleClientId) {
        throw new Error('GOOGLE_CLIENT_ID_MISSING');
    }

    if (!firebaseApiKey) {
        throw new Error('FIREBASE_API_KEY_MISSING');
    }

    await loadGoogleScript();
    const accessToken = await requestGoogleAccessToken(googleClientId);
    return exchangeGoogleTokenToFirebase(accessToken, firebaseApiKey);
}

async function loadGoogleScript() {
    if (window.google?.accounts?.oauth2) {
        return;
    }

    const existing = document.querySelector(`script[src="${GOOGLE_GSI_SCRIPT_SRC}"]`);
    if (existing) {
        await waitForGoogleApi();
        return;
    }

    await new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = GOOGLE_GSI_SCRIPT_SRC;
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        script.onerror = () => reject(new Error('GOOGLE_SCRIPT_LOAD_FAILED'));
        document.head.appendChild(script);
    });

    await waitForGoogleApi();
}

async function waitForGoogleApi(timeoutMs = 6000) {
    const startedAt = Date.now();

    while (!window.google?.accounts?.oauth2) {
        if (Date.now() - startedAt > timeoutMs) {
            throw new Error('GOOGLE_API_NOT_AVAILABLE');
        }

        await new Promise((resolve) => setTimeout(resolve, 40));
    }
}

function requestGoogleAccessToken(googleClientId) {
    return new Promise((resolve, reject) => {
        const tokenClient = window.google.accounts.oauth2.initTokenClient({
            client_id: googleClientId,
            scope: 'openid email profile',
            callback: (response) => {
                if (response?.error) {
                    reject(new Error(response.error));
                    return;
                }

                if (!response?.access_token) {
                    reject(new Error('GOOGLE_ACCESS_TOKEN_MISSING'));
                    return;
                }

                resolve(response.access_token);
            },
        });

        tokenClient.requestAccessToken({ prompt: 'select_account' });
    });
}

async function exchangeGoogleTokenToFirebase(accessToken, firebaseApiKey) {
    const response = await fetch(
        `https://identitytoolkit.googleapis.com/v1/accounts:signInWithIdp?key=${firebaseApiKey}`,
        {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                postBody: `access_token=${encodeURIComponent(accessToken)}&providerId=google.com`,
                requestUri: window.location.origin,
                returnSecureToken: true,
                returnIdpCredential: true,
            }),
        },
    );

    const json = await response.json();
    if (!response.ok) {
        throw new Error(json?.error?.message || 'GOOGLE_FIREBASE_EXCHANGE_FAILED');
    }

    if (!json?.idToken) {
        throw new Error('FIREBASE_ID_TOKEN_MISSING');
    }

    return {
        idToken: json.idToken,
        email: json.email || '',
        name: json.displayName || '',
    };
}
