<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'user',
    'size' => 'md',
    'theme' => 'admin',
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
    'user',
    'size' => 'md',
    'theme' => 'admin',
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
        'sm' => 'h-8 w-8 text-[10px]',
        'md' => 'h-10 w-10 text-xs',
        'nav' => 'h-11 w-11 text-xs',
        'lg' => 'h-14 w-14 text-sm',
        'xl' => 'h-20 w-20 text-2xl',
    ];
    $dimension = $sizes[$size] ?? $sizes['md'];
    $url = $user->avatarUrl();
    $initials = $user->initials();
    $isStudent = $theme === 'student';
    $shell = $isStudent
        ? 'border-cyan-400/30 bg-gradient-to-br from-cyan-500 to-sky-400'
        : 'border-violet-400/30 bg-gradient-to-br from-violet-600 to-indigo-500';
    $initialsClass = $isStudent ? 'font-semibold text-slate-950' : 'font-semibold text-white';
?>

<span <?php echo e($attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border {$shell} {$dimension}"])); ?>>
    <?php if($url): ?>
        <img
            src="<?php echo e($url); ?>"
            alt="<?php echo e($user->name); ?> profile photo"
            class="absolute inset-0 h-full w-full object-cover"
            loading="lazy"
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
        >
        <span class="hidden <?php echo e($initialsClass); ?>"><?php echo e($initials); ?></span>
    <?php else: ?>
        <span class="<?php echo e($initialsClass); ?>"><?php echo e($initials); ?></span>
    <?php endif; ?>
</span>

<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/user-avatar.blade.php ENDPATH**/ ?>