<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'event',
    'download' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'event',
    'download' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $posterUrl = $event->competitionPosterUrl();
?>

<?php if($posterUrl): ?>
    <section <?php echo e($attributes->class(['rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6'])); ?>>
        <div class="text-center">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300">Official Competition Poster</p>
            <p class="mt-1 text-sm text-slate-400">Promotional portrait asset (9:16). Separate from the landscape competition banner.</p>
        </div>
        <div class="mx-auto mt-5 w-full max-w-[14rem] sm:max-w-[16rem]">
            <div class="aspect-[9/16] overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 shadow-xl shadow-black/40 ring-1 ring-violet-500/10">
                <img
                    src="<?php echo e($posterUrl); ?>"
                    alt="<?php echo e($event->title); ?> official poster"
                    class="h-full w-full object-contain object-center"
                    loading="lazy"
                >
            </div>
        </div>
        <?php if($download): ?>
            <div class="mt-4 text-center">
                <a
                    href="<?php echo e($posterUrl); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-xl border border-violet-500/30 px-4 py-2 text-xs font-semibold text-violet-200 transition hover:bg-violet-500/10"
                >
                    View / Download Poster
                </a>
            </div>
        <?php endif; ?>
        <?php if($event->isLegacyPortraitAsBanner() && ! $event->hasExplicitCompetitionPoster()): ?>
            <p class="mt-4 text-center text-xs text-amber-200/90">
                This portrait image was originally uploaded as the competition banner. Upload a landscape banner (1600 × 900) for best results on cards and headers.
            </p>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/competition-poster.blade.php ENDPATH**/ ?>