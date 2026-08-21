/**
 * System-wide capitalization for human-entered text.
 * Mobile: sets autocapitalize. Desktop: capitalizes as the user types / on blur.
 * Opt out: autocapitalize="none" or data-auto-capitalize="off".
 */

const SKIP_TYPES = new Set([
    'email',
    'password',
    'number',
    'tel',
    'url',
    'search',
    'hidden',
    'date',
    'datetime-local',
    'time',
    'month',
    'week',
    'file',
    'checkbox',
    'radio',
    'range',
    'color',
    'submit',
    'button',
    'reset',
    'image',
]);

const SKIP_NAME = /(?:^|[_-])(account[_-]?id|email|password|username|user[_-]?name|token|otp|pin|code|slug|url|uri|whitelist|secret|credential|api[_-]?key|csrf|hash|student[_-]?id|social[_-]?media|phone|tel|hostname|domain)(?:$|[_-])/i;

const COMPOSING = new WeakSet();

function fieldKey(el) {
    return [el.getAttribute('name'), el.id, el.getAttribute('autocomplete')]
        .filter(Boolean)
        .join(' ');
}

function isSensitive(el) {
    if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) {
        return true;
    }

    if (el.disabled || el.readOnly) {
        return true;
    }

    if (el.dataset.autoCapitalize === 'off' || el.autocapitalize === 'none' || el.autocapitalize === 'off') {
        return true;
    }

    const type = (el instanceof HTMLInputElement ? el.type : 'textarea').toLowerCase();

    if (SKIP_TYPES.has(type)) {
        return true;
    }

    if (el instanceof HTMLInputElement && type !== 'text' && type !== 'search') {
        return true;
    }

    return SKIP_NAME.test(fieldKey(el));
}

function capitalizeWords(value) {
    return value.replace(/(^|[\s\-/'‘’])(\p{L})/gu, (full, prefix, letter) => prefix + letter.toLocaleUpperCase());
}

function capitalizeSentences(value) {
    return value.replace(/(^|[.!?]\s+)(\p{L})/gu, (full, prefix, letter) => prefix + letter.toLocaleUpperCase());
}

function transformValue(el, value) {
    if (el instanceof HTMLTextAreaElement || el.dataset.autoCapitalize === 'sentences') {
        return capitalizeSentences(value);
    }

    return capitalizeWords(value);
}

function applyCapitalization(el) {
    if (isSensitive(el) || COMPOSING.has(el)) {
        return;
    }

    const next = transformValue(el, el.value);

    if (next === el.value) {
        return;
    }

    const start = el.selectionStart;
    const end = el.selectionEnd;

    el.value = next;

    if (typeof start === 'number' && typeof end === 'number' && el === document.activeElement) {
        try {
            el.setSelectionRange(start, end);
        } catch {
            // Some input types do not support selection ranges.
        }
    }
}

function hintKeyboard(el) {
    if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) {
        return;
    }

    if (el.hasAttribute('autocapitalize')) {
        return;
    }

    if (isSensitive(el)) {
        el.setAttribute('autocapitalize', 'none');
        el.setAttribute('spellcheck', el.getAttribute('spellcheck') ?? 'false');

        return;
    }

    el.setAttribute(
        'autocapitalize',
        el instanceof HTMLTextAreaElement ? 'sentences' : 'words',
    );
}

function bindAutoCapitalize() {
    if (document.documentElement.dataset.autoCapitalizeBound === 'true') {
        return;
    }

    document.documentElement.dataset.autoCapitalizeBound = 'true';

    document.addEventListener('focusin', (event) => {
        hintKeyboard(event.target);
    }, true);

    document.addEventListener('compositionstart', (event) => {
        if (event.target instanceof HTMLElement) {
            COMPOSING.add(event.target);
        }
    }, true);

    document.addEventListener('compositionend', (event) => {
        if (event.target instanceof HTMLElement) {
            COMPOSING.delete(event.target);
            applyCapitalization(event.target);
        }
    }, true);

    document.addEventListener('input', (event) => {
        applyCapitalization(event.target);
    }, true);

    document.addEventListener('blur', (event) => {
        applyCapitalization(event.target);
    }, true);
}

bindAutoCapitalize();
