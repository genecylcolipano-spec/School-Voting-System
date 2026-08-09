<section id="activity" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <?php if (isset($component)) { $__componentOriginal87b1b280c26c60b1db52189dd51eb1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-section-header','data' => ['title' => 'Your Activity Log','description' => 'Only your actions — cannot delete entries.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Your Activity Log','description' => 'Only your actions — cannot delete entries.']); ?>
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

    <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="mt-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="text-[10px] uppercase text-slate-500">From</label>
            <input type="date" name="from" value="<?php echo e($activityFilter['from']); ?>" class="mt-1 block rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
        </div>
        <div>
            <label class="text-[10px] uppercase text-slate-500">To</label>
            <input type="date" name="to" value="<?php echo e($activityFilter['to']); ?>" class="mt-1 block rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
        </div>
        <div>
            <label class="text-[10px] uppercase text-slate-500">Action type</label>
            <select name="action_type" class="mt-1 block rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                <option value="">All types</option>
                <?php $__currentLoopData = $actionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->value ?? $type); ?>" <?php if($activityFilter['action_type'] === ($type->value ?? $type)): echo 'selected'; endif; ?>><?php echo e(ucfirst($type->value ?? $type)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Filter</button>
        <?php if($activityFilter['from'] || $activityFilter['to'] || $activityFilter['action_type']): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-400 hover:text-white">Clear</a>
        <?php endif; ?>
    </form>

    <div class="mt-4 hidden overflow-x-auto md:block">
        <table class="min-w-full text-left text-xs sm:text-sm">
            <thead class="border-b border-slate-800 text-slate-400">
                <tr>
                    <th class="px-3 py-2">Timestamp</th>
                    <th class="px-3 py-2">Action</th>
                    <th class="px-3 py-2">IP</th>
                    <th class="px-3 py-2">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                <?php $__empty_1 = true; $__currentLoopData = $activityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="text-slate-200">
                        <td class="px-3 py-2 whitespace-nowrap"><?php echo e($log->created_at?->format('M d, H:i')); ?></td>
                        <td class="px-3 py-2"><?php echo e($log->action); ?></td>
                        <td class="px-3 py-2 font-mono text-xs"><?php echo e($log->ip_address); ?></td>
                        <td class="px-3 py-2">
                            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $log->status,'label' => $log->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($log->status),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($log->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $attributes = $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $component = $__componentOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="px-3 py-6 text-center text-slate-400">No activity logged yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 space-y-2 md:hidden">
        <?php $__empty_1 = true; $__currentLoopData = $activityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="rounded-lg border border-slate-800 bg-slate-950/50 p-3 text-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-medium text-white"><?php echo e($log->action); ?></p>
                    <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $log->status,'label' => $log->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($log->status),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($log->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $attributes = $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $component = $__componentOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
                </div>
                <p class="mt-1 text-xs text-slate-400"><?php echo e($log->created_at?->format('M d, H:i')); ?> · <?php echo e($log->ip_address); ?></p>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-center text-sm text-slate-400">No activity logged yet.</p>
        <?php endif; ?>
    </div>

    <?php if($activityLogs->hasPages()): ?>
        <div class="mt-4">
            <?php echo e($activityLogs->links()); ?>

        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_activity.blade.php ENDPATH**/ ?>