export function bufferToBase64url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    bytes.forEach((b) => { binary += String.fromCharCode(b); });
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
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
