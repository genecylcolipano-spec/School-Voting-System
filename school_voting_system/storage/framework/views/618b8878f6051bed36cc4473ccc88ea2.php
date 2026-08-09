<section id="activity-timeline" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <?php if (isset($component)) { $__componentOriginal87b1b280c26c60b1db52189dd51eb1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-section-header','data' => ['title' => 'Recent Activity','description' => 'Latest election and administration events — newest first.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Activity','description' => 'Latest election and administration events — newest first.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9)): ?>
<?php $attributes = $__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9; ?>
<?php unset($__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal87b1b280c26c60b1db52189dd51eb1e9)): ?>
<?php $component = $__componentOriginal87b1b280c26c60b1db52189dd51eb1e9; ?>
<?php unset($__componentOriginal87b1b280c26c60b1db52189dd51eb1e9); ?>
<?php endif; ?>

    <div class="mt-5 space-y-0 divide-y divide-slate-800 rounded-xl border border-slate-800 bg-slate-950/40">
        <?php $__empty_1 = true; $__currentLoopData = $recentActivityTimeline ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="flex flex-wrap items-start gap-4 px-4 py-3.5 transition hover:bg-slate-900/50">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-lg" aria-hidden="true"><?php echo e($entry['icon']); ?></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white"><?php echo e($entry['activity']); ?></p>
                    <p class="mt-0.5 text-xs text-slate-400"><?php echo e($entry['user']); ?> · <?php echo e($entry['module'] ?? 'System'); ?></p>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p class="font-medium text-slate-300"><?php echo e($entry['date']); ?></p>
                    <p><?php echo e($entry['time']); ?></p>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="px-4 py-8 text-center text-sm text-slate-400">No recent activity recorded yet.</p>
        <?php endif; ?>
    </div>

    <div class="mt-4 text-right">
        <a href="<?php echo e(route('admin.audit-logs.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">View full audit log →</a>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_activity-timeline.blade.php ENDPATH**/ ?>