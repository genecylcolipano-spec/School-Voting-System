<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'path' => null,
    'name' => null,
    'size' => 'md',
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
    'path' => null,
    'name' => null,
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizes = [
        'sm' => 'h-9 w-9 text-[11px]',
        'md' => 'h-12 w-12 text-sm',
        'lg' => 'h-16 w-16 text-lg',
        'xl' => 'h-32 w-32 text-3xl',
    ];
    $dimension = $sizes[$size] ?? $sizes['md'];

    $url = \App\Support\EventImageUrl::hasUploadedImage($path)
        ? \App\Support\EventImageUrl::resolve($path)
        : null;

    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
?>

<span <?php echo e($attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-700 bg-slate-800 {$dimension}"])); ?>>
    <?php if($url): ?>
        <img src="<?php echo e($url); ?>" alt="<?php echo e($name ? $name.' photo' : 'Candidate photo'); ?>" class="h-full w-full object-cover" loading="lazy">
    <?php elseif($initials !== ''): ?>
        <span class="font-semibold text-slate-300"><?php echo e($initials); ?></span>
    <?php else: ?>
        <svg class="h-1/2 w-1/2 text-slate-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" />
        </svg>
    <?php endif; ?>
</span>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/candidate-avatar.blade.php ENDPATH**/ ?>