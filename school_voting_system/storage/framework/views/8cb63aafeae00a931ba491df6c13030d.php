<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['steps' => []]));

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

foreach (array_filter((['steps' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(count($steps) > 0): ?>
    <section class="vm-lifecycle mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
        <h3 class="text-lg font-semibold text-white">Event Status Timeline</h3>
        <p class="mt-1 text-sm text-slate-400">Election lifecycle from draft through archival.</p>

        <ol class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-2">
            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $state = $step['state'] ?? 'upcoming';
                    $tone = match ($state) {
                        'current' => 'border-violet-400 bg-violet-500/15 text-violet-100 shadow-lg shadow-violet-900/20',
                        'completed' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-100',
                        default => 'border-slate-800 bg-slate-950/40 text-slate-400',
                    };
                ?>
                <li class="relative flex flex-1 flex-col items-center rounded-xl border px-3 py-4 text-center transition <?php echo e($tone); ?>">
                    <span class="text-xl" aria-hidden="true"><?php echo e($step['icon'] ?? '•'); ?></span>
                    <span class="mt-2 text-xs font-semibold uppercase tracking-wide"><?php echo e($step['label']); ?></span>
                    <?php if($state === 'current'): ?>
                        <span class="mt-2 rounded-full bg-violet-500/20 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-200">Current</span>
                    <?php endif; ?>
                    <?php if(! $loop->last): ?>
                        <span class="pointer-events-none absolute -bottom-3 left-1/2 hidden h-6 w-px -translate-x-1/2 bg-slate-700 lg:bottom-auto lg:left-auto lg:right-0 lg:top-1/2 lg:block lg:h-px lg:w-full lg:translate-x-1/2 lg:-translate-y-1/2" aria-hidden="true"></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/event-lifecycle-stepper.blade.php ENDPATH**/ ?>