<?php
    $inApp = $inApp ?? \App\Support\InAppBrowser::detect(request()->userAgent());
    $pageUrl = $pageUrl ?? url()->full();
    $appLabel = $inApp['name'] && $inApp['name'] !== 'in-app browser'
        ? $inApp['name']
        : 'this app';
?>

<div
    id="in-app-browser-gate"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses(['hidden' => ! $inApp['blocked']]); ?>"
    <?php if(! $inApp['blocked']): ?> hidden <?php endif; ?>
    data-blocked="<?php echo e($inApp['blocked'] ? '1' : '0'); ?>"
    data-android="<?php echo e($inApp['android'] ? '1' : '0'); ?>"
    data-ios="<?php echo e($inApp['ios'] ? '1' : '0'); ?>"
    data-page-url="<?php echo e($pageUrl); ?>"
    role="alert"
>
    <p class="rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
        <?php echo e($appLabel); ?> in-app browser cannot use passkeys. Open this page in Chrome or Safari to sign in.
    </p>

    <?php if($inApp['android'] || ! $inApp['ios']): ?>
        <a
            id="in-app-open-chrome"
            href="<?php echo e(\App\Support\InAppBrowser::chromeIntentUrl($pageUrl)); ?>"
            class="mt-4 flex w-full items-center justify-center rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60"
        >
            Open in Chrome
        </a>
    <?php endif; ?>

    <?php if($inApp['ios']): ?>
        <a
            href="<?php echo e(\App\Support\InAppBrowser::chromeSchemeUrl($pageUrl)); ?>"
            class="mt-4 flex w-full items-center justify-center rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60"
        >
            Open in Chrome
        </a>

        <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm text-slate-300">
            <li>Tap <span class="font-semibold text-white">⋯</span> or the share icon at the top.</li>
            <li>Tap <span class="font-semibold text-white">Open in Safari</span>.</li>
            <li>Sign in with Passkey there.</li>
        </ol>
    <?php endif; ?>

    <button
        id="in-app-copy-link"
        type="button"
        class="mt-3 flex w-full items-center justify-center rounded-xl border border-cyan-400/40 bg-transparent px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/10 focus:outline-none focus:ring-2 focus:ring-cyan-300/60"
    >
        Copy link
    </button>
    <p id="in-app-copy-status" class="mt-2 hidden text-center text-xs text-slate-400" role="status"></p>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/auth/partials/in-app-browser-gate.blade.php ENDPATH**/ ?>