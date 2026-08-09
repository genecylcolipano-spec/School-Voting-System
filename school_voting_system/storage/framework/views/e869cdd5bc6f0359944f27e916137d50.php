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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Audit Logs','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit Logs','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Audit Logs',
            'description' => 'Critical system activity across users, security, backups, and administration.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-400">Newest events first. Export supports up to 5,000 rows.</p>
            <a href="<?php echo e(route('super-admin.audit.export', request()->only(['from', 'to', 'module']))); ?>"
                class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">
                Export CSV
            </a>
        </div>

        <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:grid-cols-2 lg:grid-cols-6">
            <input name="search" type="search" value="<?php echo e($filters['search']); ?>" placeholder="Search action, user, IP"
                class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white lg:col-span-2">
            <input name="from" type="date" value="<?php echo e($filters['from']); ?>" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <input name="to" type="date" value="<?php echo e($filters['to']); ?>" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <select name="module" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All modules</option>
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($module->value); ?>" <?php if($filters['module'] === $module->value): echo 'selected'; endif; ?>><?php echo e(str($module->value)->title()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="role" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All roles</option>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($role->value); ?>" <?php if($filters['role'] === $role->value): echo 'selected'; endif; ?>><?php echo e(str($role->value)->replace('_', ' ')->title()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="user_id" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white lg:col-span-2">
                <option value="">All users</option>
                <?php $__currentLoopData = $actors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($actor->id); ?>" <?php if($filters['user_id'] === (string) $actor->id): echo 'selected'; endif; ?>><?php echo e($actor->name); ?> (<?php echo e($actor->account_id); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <div class="flex gap-2 lg:col-span-4">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="<?php echo e(route('super-admin.system.audit.index')); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Timestamp</th>
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                        <th class="px-4 py-3 font-medium">Module</th>
                        <th class="px-4 py-3 font-medium">IP Address</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3 whitespace-nowrap"><?php echo e(optional($log->created_at)->format('M d, Y g:i A')); ?></td>
                            <td class="px-4 py-3"><?php echo e($log->user?->name ?? 'System'); ?></td>
                            <td class="px-4 py-3"><?php echo e($log->user?->roleLabel() ?? '—'); ?></td>
                            <td class="px-4 py-3"><?php echo e($log->action); ?></td>
                            <td class="px-4 py-3"><?php echo e(str($log->action_type?->value ?? $log->action_type)->title()); ?></td>
                            <td class="px-4 py-3 font-mono text-xs"><?php echo e($log->ip_address ?: '—'); ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">
                                    <?php echo e(ucfirst((string) ($log->status ?? 'success'))); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No audit events match your filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6"><?php echo e($logs->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/system/audit.blade.php ENDPATH**/ ?>