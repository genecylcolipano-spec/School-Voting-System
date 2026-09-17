/**
 * Detect in-app browsers that cannot run WebAuthn, and hand the user to Chrome when possible.
 */

const AUTO_OPEN_KEY = 'svs.in-app-chrome-intent';

export function detectInAppBrowser(userAgent = navigator.userAgent) {
    const ua = String(userAgent ?? '');
    const android = /Android/i.test(ua);
    const ios = /iPhone|iPad|iPod/i.test(ua);

    const patterns = [
        ['Messenger', /FBAN\/Messenger|MessengerForiOS|MessengerLite/i],
        ['Facebook', /FBAN|FBAV|FB_IAB|FB4A|FBIOS|IABMV/i],
        ['Instagram', /Instagram/i],
        ['TikTok', /BytedanceWebview|TikTok|musical_ly/i],
        ['LINE', / Line\//i],
        ['Snapchat', /Snapchat/i],
        ['WhatsApp', /WhatsApp/i],
        ['WeChat', /MicroMessenger/i],
        ['LinkedIn', /LinkedInApp/i],
        ['X', /Twitter/i],
        ['Pinterest', /Pinterest/i],
        ['KakaoTalk', /KAKAOTALK/i],
    ];

    let name = null;

    for (const [label, pattern] of patterns) {
        if (pattern.test(ua)) {
            name = label;
            break;
        }
    }

    if (!name && android && /; wv\)/i.test(ua)) {
        name = 'in-app browser';
    }

    return { blocked: name !== null, android, ios, name };
}

export function initInAppBrowserGate() {
    const gate = document.getElementById('in-app-browser-gate');
    const supported = document.getElementById('passkey-supported-panel');

    if (!gate) {
        return detectInAppBrowser().blocked;
    }

    const detected = detectInAppBrowser();
    const blocked = gate.dataset.blocked === '1' || detected.blocked;

    if (!blocked) {
        return false;
    }

    gate.hidden = false;
    gate.classList.remove('hidden');
    supported?.classList.add('hidden');
    supported?.setAttribute('hidden', '');

    const copyButton = document.getElementById('in-app-copy-link');
    const copyStatus = document.getElementById('in-app-copy-status');
    const pageUrl = gate.dataset.pageUrl || window.location.href;

    copyButton?.addEventListener('click', async () => {
        const copied = await copyText(pageUrl);

        if (copyStatus) {
            copyStatus.textContent = copied
                ? 'Link copied. Paste it in Chrome or Safari.'
                : pageUrl;
            copyStatus.classList.remove('hidden');
        }
    });

    maybeOpenChrome(gate, detected.android || gate.dataset.android === '1');

    return true;
}

function maybeOpenChrome(gate, isAndroid) {
    const openChrome = document.getElementById('in-app-open-chrome');

    if (!isAndroid || !openChrome?.getAttribute('href')) {
        return;
    }

    const marker = `${AUTO_OPEN_KEY}:${window.location.href}`;

    try {
        if (sessionStorage.getItem(marker) === '1') {
            return;
        }

        sessionStorage.setItem(marker, '1');
    } catch {
        // Private / in-app storage can throw; still attempt Chrome once.
    }

    window.location.replace(openChrome.getAttribute('href'));
}

async function copyText(value) {
    try {
        await navigator.clipboard.writeText(value);

        return true;
    } catch {
        try {
            const field = document.createElement('textarea');
            field.value = value;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.left = '-9999px';
            document.body.appendChild(field);
            field.select();
            const ok = document.execCommand('copy');
            field.remove();

            return ok;
        } catch {
            return false;
        }
    }
}
