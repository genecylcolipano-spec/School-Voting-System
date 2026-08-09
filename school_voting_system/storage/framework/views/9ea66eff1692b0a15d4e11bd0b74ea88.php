<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'feedUrl',
    'indexUrl',
    'markAllUrl',
    'markOneUrlTemplate',
    'initialCount' => 0,
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
    'feedUrl',
    'indexUrl',
    'markAllUrl',
    'markOneUrlTemplate',
    'initialCount' => 0,
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
    $isAdmin = $theme === 'admin';
    $isFaculty = $theme === 'faculty';
    $accentBorder = match (true) {
        $isAdmin => 'border-violet-500/25',
        $isFaculty => 'border-teal-500/25',
        default => 'border-cyan-500/25',
    };
    $accentText = match (true) {
        $isAdmin => 'text-violet-300',
        $isFaculty => 'text-teal-300',
        default => 'text-cyan-300',
    };
    $buttonBorder = match (true) {
        $isAdmin => 'border-violet-500/20 text-violet-300 hover:border-violet-400/40',
        $isFaculty => 'border-teal-500/25 text-teal-300 hover:border-teal-400/40',
        default => 'border-slate-600/50 text-cyan-400 hover:border-cyan-500/30',
    };
    $centerId = 'nc-'.str_replace('.', '', uniqid('', true));
?>

<div
    class="notification-center"
    data-notification-center
    data-center-id="<?php echo e($centerId); ?>"
    data-feed-url="<?php echo e($feedUrl); ?>"
    data-mark-all-url="<?php echo e($markAllUrl); ?>"
    data-mark-one-url-template="<?php echo e($markOneUrlTemplate); ?>"
    data-initial-count="<?php echo e($initialCount); ?>"
>
    <?php if (isset($component)) { $__componentOriginalca7a0abfe8e944091236a86c0d7e6936 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca7a0abfe8e944091236a86c0d7e6936 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-popover','data' => ['align' => 'end','widthClass' => 'w-[22rem]','panelClass' => ''.e($accentBorder).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('responsive-popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'end','width-class' => 'w-[22rem]','panel-class' => ''.e($accentBorder).'']); ?>
         <?php $__env->slot('trigger', null, []); ?> 
            <button
                type="button"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border <?php echo e($buttonBorder); ?> bg-slate-900 transition hover:bg-slate-800"
                aria-label="Notifications"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <span
                    data-notification-badge
                    class="absolute -right-1.5 -top-1.5 flex h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-red-500 px-0.5 text-[10px] font-bold leading-none text-white ring-2 ring-slate-950 <?php echo e($initialCount > 0 ? '' : 'hidden'); ?>"
                ><?php echo e($initialCount > 0 ? ($initialCount > 99 ? '99+' : $initialCount) : ''); ?></span>
            </button>
         <?php $__env->endSlot(); ?>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-800 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-white">Notifications</p>
                <p class="text-xs text-slate-500">Latest updates</p>
            </div>
            <form method="POST" action="<?php echo e($markAllUrl); ?>" data-notification-mark-all data-center-id="<?php echo e($centerId); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-xs font-semibold <?php echo e($accentText); ?> hover:opacity-80">Mark all read</button>
            </form>
        </div>

        <div
            data-notification-list
            data-center-id="<?php echo e($centerId); ?>"
            data-feed-url="<?php echo e($feedUrl); ?>"
            class="min-h-0 max-h-80 flex-1 divide-y divide-slate-800 overflow-y-auto overscroll-contain"
        >
            <p class="px-4 py-8 text-center text-sm text-slate-500">Loading notifications…</p>
        </div>

        <div class="shrink-0 border-t border-slate-800 bg-slate-950/60 px-4 py-3 text-center">
            <a href="<?php echo e($indexUrl); ?>" data-popover-close class="text-sm font-semibold <?php echo e($accentText); ?> hover:opacity-80">View All Notifications</a>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca7a0abfe8e944091236a86c0d7e6936)): ?>
<?php $attributes = $__attributesOriginalca7a0abfe8e944091236a86c0d7e6936; ?>
<?php unset($__attributesOriginalca7a0abfe8e944091236a86c0d7e6936); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca7a0abfe8e944091236a86c0d7e6936)): ?>
<?php $component = $__componentOriginalca7a0abfe8e944091236a86c0d7e6936; ?>
<?php unset($__componentOriginalca7a0abfe8e944091236a86c0d7e6936); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/notification-center.blade.php ENDPATH**/ ?>