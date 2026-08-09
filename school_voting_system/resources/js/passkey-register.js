/**
 * Passkey registration ceremony (navigator.credentials.create).
 * Binds once per button to avoid duplicate handlers when the script is included twice.
 */

import { bufferToBase64url, base64urlToBuffer } from './passkey-helpers.js';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        },
        ...options,
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = data.message
            ?? Object.values(data.errors ?? {}).flat()?.[0]
            ?? (response.status === 403
                ? 'Enrollment session expired. Open your enrollment link again.'
                : 'Passkey registration failed.');

        throw new Error(message);
    }

    return data;
}

function toCreationOptions(payload) {
    const options = payload.options ?? payload;
    const publicKey = { ...options };

    publicKey.challenge = base64urlToBuffer(publicKey.challenge, 'challenge');
    publicKey.user = {
        ...publicKey.user,
        id: base64urlToBuffer(publicKey.user?.id, 'user.id'),
    };

    if (publicKey.excludeCredentials) {
        publicKey.excludeCredentials = publicKey.excludeCredentials.map((item) => ({
            ...item,
            id: base64urlToBuffer(item.id, 'excludeCredentials.id'),
        }));
    }

    return publicKey;
}

function serializeAttestation(credential) {
    const { response } = credential;

    return {
        id: credential.id,
        rawId: bufferToBase64url(credential.rawId),
        type: credential.type,
        response: {
            attestationObject: bufferToBase64url(response.attestationObject),
            clientDataJSON: bufferToBase64url(response.clientDataJSON),
        },
    };
}

function bindPasskeyRegistration() {
    const button = document.getElementById('register-passkey-btn');

    if (!button || button.dataset.passkeyBound === 'true') {
        return;
    }

    button.dataset.passkeyBound = 'true';

    button.addEventListener('click', async () => {
        const status = document.getElementById('register-passkey-status');
        const label = document.getElementById('register-passkey-label');
        const spinner = document.getElementById('register-passkey-spinner');
        const deviceName = document.getElementById('device_name')?.value?.trim() || 'Primary Device';

        if (!window.PublicKeyCredential) {
            status.textContent = 'Passkeys are not supported in this browser.';
            return;
        }

        button.disabled = true;
        spinner?.classList.remove('hidden');
        if (label) {
            label.textContent = 'Waiting for device…';
        }
        status.textContent = 'Follow the biometric prompt on your device.';

        try {
            const challenge = await fetchJson(button.dataset.optionsUrl, { method: 'GET' });
            const credential = await navigator.credentials.create({
                publicKey: toCreationOptions(challenge),
            });

            if (!credential) {
                throw new Error('Passkey registration was cancelled.');
            }

            const result = await fetchJson(button.dataset.verifyUrl, {
                method: 'POST',
                body: JSON.stringify({
                    name: deviceName,
                    device_name: deviceName,
                    credential: serializeAttestation(credential),
                }),
            });

            if (result.redirect) {
                status.textContent = 'Passkey registered. Redirecting…';
                window.location.assign(result.redirect);
            } else {
                status.textContent = 'Passkey registered successfully.';
                window.location.reload();
            }
        } catch (error) {
            status.textContent = error?.name === 'NotAllowedError'
                ? 'Biometric registration was cancelled.'
                : (error?.message ?? 'Could not register passkey.');
        } finally {
            button.disabled = false;
            spinner?.classList.add('hidden');
            if (label) {
                label.textContent = 'Register passkey';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', bindPasskeyRegistration);
