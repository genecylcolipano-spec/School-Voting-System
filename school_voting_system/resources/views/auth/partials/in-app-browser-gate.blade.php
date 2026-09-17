@php
    $inApp = $inApp ?? \App\Support\InAppBrowser::detect(request()->userAgent());
    $pageUrl = $pageUrl ?? url()->full();
    $appLabel = $inApp['name'] && $inApp['name'] !== 'in-app browser'
        ? $inApp['name']
        : 'this app';
@endphp

<div
    id="in-app-browser-gate"
    @class(['hidden' => ! $inApp['blocked']])
    @if (! $inApp['blocked']) hidden @endif
    data-blocked="{{ $inApp['blocked'] ? '1' : '0' }}"
    data-android="{{ $inApp['android'] ? '1' : '0' }}"
    data-ios="{{ $inApp['ios'] ? '1' : '0' }}"
    data-page-url="{{ $pageUrl }}"
    role="alert"
>
    <p class="rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
        {{ $appLabel }} in-app browser cannot use passkeys. Open this page in Chrome or Safari to sign in.
    </p>

    @if ($inApp['android'] || ! $inApp['ios'])
        <a
            id="in-app-open-chrome"
            href="{{ \App\Support\InAppBrowser::chromeIntentUrl($pageUrl) }}"
            class="mt-4 flex w-full items-center justify-center rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60"
        >
            Open in Chrome
        </a>
    @endif

    @if ($inApp['ios'])
        <a
            href="{{ \App\Support\InAppBrowser::chromeSchemeUrl($pageUrl) }}"
            class="mt-4 flex w-full items-center justify-center rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60"
        >
            Open in Chrome
        </a>

        <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm text-slate-300">
            <li>Tap <span class="font-semibold text-white">⋯</span> or the share icon at the top.</li>
            <li>Tap <span class="font-semibold text-white">Open in Safari</span>.</li>
            <li>Sign in with Passkey there.</li>
        </ol>
    @endif

    <button
        id="in-app-copy-link"
        type="button"
        class="mt-3 flex w-full items-center justify-center rounded-xl border border-cyan-400/40 bg-transparent px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/10 focus:outline-none focus:ring-2 focus:ring-cyan-300/60"
    >
        Copy link
    </button>
    <p id="in-app-copy-status" class="mt-2 hidden text-center text-xs text-slate-400" role="status"></p>
</div>
