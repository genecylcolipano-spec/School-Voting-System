<?php if(session('enrollment_url')): ?>
    <?php
        $appHost = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));
        $publicAppUrl = $appHost !== '' && ! in_array($appHost, ['localhost', '127.0.0.1', '::1'], true);
    ?>
    <div class="mb-4 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4" x-data="{ copied: false }">
        <p class="text-sm font-medium text-amber-100">Passkey enrollment link</p>
        <p class="mt-1 text-xs text-amber-100/70">
            Opening this will sign you out of Super Admin so they can register their own passkey.
            <?php if (! ($publicAppUrl)): ?>
                Local URLs cannot be opened on another device — copy the link only if they can reach this computer.
            <?php endif; ?>
        </p>
        <a href="<?php echo e(session('enrollment_url')); ?>" class="mt-2 block break-all text-sm text-violet-300 hover:text-violet-200">
            <?php echo e(session('enrollment_url')); ?>

        </a>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button"
                class="rounded-lg border border-amber-400/30 bg-slate-950/40 px-3 py-1.5 text-xs font-semibold text-amber-100 hover:bg-slate-950/70"
                @click="navigator.clipboard.writeText(<?php echo \Illuminate\Support\Js::from(session('enrollment_url'))->toHtml() ?>); copied = true; setTimeout(() => copied = false, 2000)">
                <span x-text="copied ? 'Copied!' : 'Copy link'"></span>
            </button>
            <a href="<?php echo e(session('enrollment_url')); ?>"
                class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">
                Open enrollment page
            </a>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/enrollment-link-banner.blade.php ENDPATH**/ ?>