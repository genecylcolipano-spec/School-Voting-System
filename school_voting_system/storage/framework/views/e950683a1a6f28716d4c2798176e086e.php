<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginal57da683fe32826f08aa9f05c3342a7e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Audit Log','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit Log','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Audit Log',
            'description' => 'Search and filter important system actions.',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <form method="GET" action="<?php echo e(route('admin.audit-logs.index')); ?>" class="mb-6 grid gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="text-[10px] uppercase text-slate-500">Search</label>
                <input type="search" name="search" value="<?php echo e($filters['search']); ?>" placeholder="Action or user…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">From</label>
                <input type="date" name="from" value="<?php echo e($filters['from']); ?>" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">To</label>
                <input type="date" name="to" value="<?php echo e($filters['to']); ?>" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">Module</label>
                <select name="module" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
                    <option value="">All modules</option>
                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($module->value); ?>" <?php if($filters['module'] === $module->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($module->value)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">Role</label>
                <input type="text" name="role" value="<?php echo e($filters['role']); ?>" placeholder="Admin role…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-6">
                <button type="submit" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Apply filters</button>
                <?php if($filters['search'] || $filters['from'] || $filters['to'] || $filters['module'] || $filters['role']): ?>
                    <a href="<?php echo e(route('admin.audit-logs.index')); ?>" class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-800 bg-slate-950/50 text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Module</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="text-slate-200 transition hover:bg-slate-950/40">
                                <td class="px-4 py-3 font-medium text-white"><?php echo e($log->admin_name ?? $log->user?->name ?? 'System'); ?></td>
                                <td class="px-4 py-3 text-slate-400"><?php echo e($log->admin_role ?? '—'); ?></td>
                                <td class="px-4 py-3"><?php echo e($log->action); ?></td>
                                <td class="px-4 py-3 capitalize text-violet-300"><?php echo e($log->action_type?->value ?? 'system'); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap"><?php echo e($log->created_at?->format('M d, Y')); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap"><?php echo e($log->created_at?->format('g:i A')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No audit entries match your filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($logs->hasPages()): ?>
                <div class="border-t border-slate-800 px-4 py-3"><?php echo e($logs->links()); ?></div>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $attributes = $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $component = $__componentOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/audit/index.blade.php ENDPATH**/ ?>