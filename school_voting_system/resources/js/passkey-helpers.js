export function bufferToBase64url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    bytes.forEach((b) => { binary += String.fromCharCode(b); });
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
}

export const PAGE_EXPIRED_MESSAGE = 'This page expired. Refresh the page, then try again.';
export const OFFLINE_MESSAGE = 'No internet connection. Check your signal, then try again.';
export const OPTIONS_START_FAILED_MESSAGE = 'Could not start passkey sign-in. Refresh the page, then try again.';
export const OPTIONS_REGISTER_FAILED_MESSAGE = 'Could not start passkey registration. Refresh the page, then try again.';

export function isOfflineLikeError(error) {
    if (typeof navigator !== 'undefined' && navigator.onLine === false) {
        return true;
    }

    const name = String(error?.name ?? '');
    const message = String(error?.message ?? '');

    return (name === 'TypeError' && /failed to fetch|networkerror|load failed|network request failed/i.test(message))
        || /failed to fetch/i.test(message);
}

export function userFacingHttpError(status, message, fallback) {
    const text = String(message ?? '');

    if (status === 419 || /csrf|token mismatch|page expired/i.test(text)) {
        return PAGE_EXPIRED_MESSAGE;
    }

    return text || fallback;
}

export function userFacingPasskeyError(error, fallback, startFailedMessage = OPTIONS_START_FAILED_MESSAGE) {
    if (isOfflineLikeError(error)) {
        return OFFLINE_MESSAGE;
    }

    const message = String(error?.message ?? '');

    if (/invalid webauthn payload|challenge is missing|malformed/i.test(message)) {
        return startFailedMessage;
    }

    return message || fallback;
}

export function requirePasskeyEndpoint(url, startFailedMessage = OPTIONS_START_FAILED_MESSAGE) {
    if (typeof url !== 'string' || url.length === 0) {
        throw new Error(startFailedMessage);
    }

    return url;
}

export function webAuthnPublicKeyFromPayload(payload, startFailedMessage = OPTIONS_START_FAILED_MESSAGE) {
    const options = payload?.options ?? payload?.publicKey ?? payload;

    if (! options || typeof options !== 'object' || Array.isArray(options)) {
        throw new Error(startFailedMessage);
    }

    if (typeof options.challenge !== 'string' || options.challenge.length === 0) {
        throw new Error(startFailedMessage);
    }

    return { ...options };
}

export function base64urlToBuffer(value, field = 'value') {
    if (typeof value !== 'string' || value.length === 0) {
        throw new Error(`Invalid WebAuthn payload: ${field} is missing or malformed.`);
    }

    const padding = '='.repeat((4 - (value.length % 4)) % 4);
    const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const buffer = new ArrayBuffer(raw.length);
    const view = new Uint8Array(buffer);
    for (let i = 0; i < raw.length; i += 1) view[i] = raw.charCodeAt(i);
    return buffer;
}

export async function fetchPasskeyJson(url, options = {}, messages = {}) {
    const startFailedMessage = messages.startFailedMessage ?? OPTIONS_START_FAILED_MESSAGE;

    if (typeof navigator !== 'undefined' && navigator.onLine === false) {
        throw new Error(OFFLINE_MESSAGE);
    }

    requirePasskeyEndpoint(url, startFailedMessage);

    const { headers: extraHeaders, ...rest } = options;
    let response;

    try {
        response = await fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                ...(extraHeaders ?? {}),
            },
            ...rest,
        });
    } catch (error) {
        throw new Error(isOfflineLikeError(error) ? OFFLINE_MESSAGE : startFailedMessage);
    }

    const contentType = response.headers.get('content-type') ?? '';
    const data = contentType.includes('application/json')
        ? await response.json().catch(() => null)
        : null;

    if (! response.ok) {
        const fallback = typeof messages.fallback === 'function'
            ? messages.fallback(response.status)
            : (messages.fallback ?? 'Passkey authentication failed.');

        throw new Error(userFacingHttpError(
            response.status,
            data?.message ?? Object.values(data?.errors ?? {}).flat()?.[0],
            fallback,
        ));
    }

    if (data === null || typeof data !== 'object') {
        throw new Error(startFailedMessage);
    }

    return data;
}
