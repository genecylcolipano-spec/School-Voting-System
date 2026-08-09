/**
 * Passkey login ceremony — fetches challenge options, invokes WebAuthn, posts assertion.
 */

import { bufferToBase64url, base64urlToBuffer } from './passkey-helpers.js';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const statusPanel = () => document.getElementById('passkey-status');
const loginButton = () => document.getElementById('passkey-login-btn');
const loginLabel = () => document.getElementById('passkey-login-label');
const spinner = () => document.getElementById('passkey-spinner');

function setStatus(message, type = 'info') {
    const panel = statusPanel();
    if (!panel) return;

    if (!message) {
        panel.classList.add('hidden');
        panel.textContent = '';
        return;
    }

    panel.classList.remove('hidden', 'border-rose-500/40', 'bg-rose-500/10', 'text-rose-200', 'border-cyan-500/40', 'bg-cyan-500/10', 'text-cyan-100');
    panel.classList.add(type === 'error' ? 'border-rose-500/40' : 'border-cyan-500/40', type === 'error' ? 'bg-rose-500/10' : 'bg-cyan-500/10', type === 'error' ? 'text-rose-200' : 'text-cyan-100');
    panel.textContent = message;
}

function setLoading(isLoading) {
    loginButton().disabled = isLoading;
    spinner().classList.toggle('hidden', !isLoading);
    loginLabel().textContent = isLoading ? 'Authenticating…' : 'Login with Passkey / Fingerprint';
}

function toPublicKeyOptions(payload) {
    const options = payload.options ?? payload.publicKey ?? payload;
    const publicKey = { ...options };

    publicKey.challenge = base64urlToBuffer(publicKey.challenge, 'challenge');
    publicKey.allowCredentials = publicKey.allowCredentials?.map((item) => ({
        ...item,
        id: base64urlToBuffer(item.id, 'allowCredentials.id'),
    }));

    return publicKey;
}

function serializeAssertion(credential) {
    const { response } = credential;

    return {
        id: credential.id,
        rawId: bufferToBase64url(credential.rawId),
        type: credential.type,
        response: {
            authenticatorData: bufferToBase64url(response.authenticatorData),
            clientDataJSON: bufferToBase64url(response.clientDataJSON),
            signature: bufferToBase64url(response.signature),
            userHandle: response.userHandle ? bufferToBase64url(response.userHandle) : null,
        },
    };
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
            ...(options.headers ?? {}),
        },
        ...options,
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = data.message
            ?? Object.values(data.errors ?? {}).flat()?.[0]
            ?? 'Passkey authentication failed.';
        throw new Error(message);
    }

    return data;
}

export async function performPasskeyLogin() {
    if (window.location.hostname === '127.0.0.1' || window.location.hostname === '[::1]') {
        const port = window.location.port ? `:${window.location.port}` : '';
        window.location.replace(`${window.location.protocol}//localhost${port}${window.location.pathname}${window.location.search}`);
        return;
    }

    if (!window.PublicKeyCredential) {
        throw new Error('This browser does not support passkeys. Use a modern browser over HTTPS (or localhost).');
    }

    const challenge = await fetchJson(window.passkeyPortal.loginOptionsUrl, { method: 'GET' });
    const credential = await navigator.credentials.get({ publicKey: toPublicKeyOptions(challenge) });

    if (!credential) {
        throw new Error('No passkey was provided by your device.');
    }

    return fetchJson(window.passkeyPortal.loginVerifyUrl, {
        method: 'POST',
        body: JSON.stringify({ credential: serializeAssertion(credential) }),
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loginButton()?.addEventListener('click', async () => {
        setLoading(true);
        setStatus('');

        try {
            const result = await performPasskeyLogin();

            if (!result) {
                return;
            }

            setStatus('Signed in. Redirecting…');
            window.location.assign(result.redirect ?? '/dashboard');
        } catch (error) {
            const cancelled = error?.name === 'NotAllowedError';
            setStatus(
                cancelled
                    ? 'Passkey sign-in was cancelled.'
                    : (error?.message ?? 'Unable to sign in with passkey.'),
                cancelled ? 'info' : 'error',
            );
        } finally {
            setLoading(false);
        }
    });
});
