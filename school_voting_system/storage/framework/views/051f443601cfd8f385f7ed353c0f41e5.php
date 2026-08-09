<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
    'variant' => 'secondary',
    'disabled' => false,
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
    'href' => null,
    'variant' => 'secondary',
    'disabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isDisabled = $disabled || $variant === 'disabled' || blank($href);

    // Preserve existing button language; fixed box keeps Action column aligned.
    $shell = implode(' ', [
        'inline-flex',
        'h-9',
        'w-[112px]',
        'shrink-0',
        'items-center',
        'justify-center',
        'overflow-hidden',
        'whitespace-nowrap',
        'rounded-xl',
        'px-3',
        'text-xs',
        'font-semibold',
        'leading-none',
        'transition',
        'focus:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-cyan-400/60',
        'focus-visible:ring-offset-2',
        'focus-visible:ring-offset-slate-950',
    ]);

    $tone = match (true) {
        $isDisabled => 'cursor-not-allowed border border-slate-700 text-slate-500',
        $variant === 'primary' => 'bg-gradient-to-r from-cyan-500 to-sky-400 text-slate-950 hover:from-cyan-400 hover:to-sky-300',
        default => 'border border-cyan-500/30 text-cyan-300 hover:bg-cyan-500/10',
    };
?>

<?php if($isDisabled): ?>
    <span
        <?php echo e($attributes->merge([
            'class' => $shell.' '.$tone,
            'aria-disabled' => 'true',
            'role' => 'button',
            'tabindex' => '-1',
        ])); ?>

    >
        <span class="truncate"><?php echo e($slot); ?></span>
    </span>
<?php else: ?>
    <a
        href="<?php echo e($href); ?>"
        <?php echo e($attributes->merge([
            'class' => $shell.' '.$tone,
        ])); ?>

    >
        <span class="truncate"><?php echo e($slot); ?></span>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/ui/action-button.blade.php ENDPATH**/ ?>