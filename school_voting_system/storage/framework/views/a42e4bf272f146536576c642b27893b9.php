<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'src',
    'alt' => 'Image preview',
    'label' => 'Image',
    'inputId' => 'event-image-input',
    'previewId' => 'event-image-preview',
    'captionId' => 'event-image-caption',
    'hasUploaded' => false,
    'contain' => false,
    'orientation' => null,
    'warnPortrait' => false,
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
    'src',
    'alt' => 'Image preview',
    'label' => 'Image',
    'inputId' => 'event-image-input',
    'previewId' => 'event-image-preview',
    'captionId' => 'event-image-caption',
    'hasUploaded' => false,
    'contain' => false,
    'orientation' => null,
    'warnPortrait' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $placeholder = \App\Support\EventImageUrl::placeholder();
    $useContain = $contain || in_array($orientation, ['portrait', 'square'], true);
?>

<div>
    <label class="block text-sm font-medium text-slate-300"><?php echo e($label); ?></label>
    <div
        id="<?php echo e($previewId); ?>-frame"
        class="relative mt-2 aspect-video w-full max-w-xl overflow-hidden rounded-xl border border-slate-700 bg-slate-950"
        data-contain="<?php echo e($useContain ? '1' : '0'); ?>"
    >
        <img
            id="<?php echo e($previewId); ?>-blur"
            src="<?php echo e($src); ?>"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125 <?php echo e($useContain ? '' : 'hidden'); ?>"
        >
        <div class="absolute inset-0 z-[1] bg-gradient-to-t from-slate-950/50 via-transparent to-transparent"></div>
        <img
            id="<?php echo e($previewId); ?>"
            src="<?php echo e($src); ?>"
            alt="<?php echo e($alt); ?>"
            data-placeholder="<?php echo e($placeholder); ?>"
            class="absolute inset-0 z-[1] h-full w-full <?php echo e($useContain ? 'object-contain' : 'object-cover'); ?> object-center"
            onerror="this.onerror=null;this.src=this.dataset.placeholder;"
        >
    </div>
    <p id="<?php echo e($captionId); ?>" class="mt-1 text-xs text-slate-500">
        <?php if($hasUploaded): ?>
            Current uploaded banner. Choose a new file to replace it.
        <?php else: ?>
            Default placeholder shown. Upload a landscape banner (1600 × 900) for best results.
        <?php endif; ?>
    </p>
    <p
        id="<?php echo e($previewId); ?>-orientation-warning"
        class="mt-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-xs font-medium text-amber-100 <?php echo e($warnPortrait || $useContain ? '' : 'hidden'); ?>"
        data-default-hidden="<?php echo e($warnPortrait || $useContain ? '0' : '1'); ?>"
    >
        This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
    </p>
    <p class="mt-0.5 text-[11px] text-slate-600">
        Live preview: landscape fills 16:9 with cover; portrait/square stay fully visible over a blurred backdrop (never stretched).
    </p>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/event-image-field.blade.php ENDPATH**/ ?>