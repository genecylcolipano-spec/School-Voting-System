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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Backup & Restore','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Backup & Restore','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Backup & Restore',
            'description' => 'Create disaster-recovery points before major operations. Restore is a future enhancement.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Backups</p>
                <p class="mt-2 text-2xl font-bold text-white"><?php echo e(number_format($stats['total'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Latest Backup</p>
                <p class="mt-2 text-sm font-semibold text-white"><?php echo e(optional($stats['latest']?->completed_at)->format('M d, Y g:i A') ?? 'None yet'); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Storage Used</p>
                <p class="mt-2 text-2xl font-bold text-white"><?php echo e($storageUsed); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Backup Date</p>
                <p class="mt-2 text-sm font-semibold text-white"><?php echo e(optional($stats['last_backup_at'])->format('M d, Y') ?? '—'); ?></p>
            </div>
        </div>

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Create Backup</h2>
                    <p class="mt-1 max-w-2xl text-sm text-slate-400">
                        Creates a full recovery point including users, passkeys, elections, votes, talent competitions,
                        fundraising, announcements, roster data, audit logs, system settings, and uploaded media
                        (logos, photos, attachments). Manual only — nothing is scheduled automatically.
                    </p>
                </div>
                <form method="POST" action="<?php echo e(route('super-admin.system.backups.store')); ?>"
                    onsubmit="return confirm('This will create a recovery point of the current system. Continue?')"
                    class="shrink-0">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="full_system">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Create Backup
                    </button>
                </form>
            </div>
        </section>

        <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:grid-cols-2 lg:grid-cols-6">
            <input name="search" type="search" value="<?php echo e($filters['search']); ?>" placeholder="Search name, type, creator"
                class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white lg:col-span-2">
            <select name="type" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All types</option>
                <?php $__currentLoopData = $backupTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if($filters['type'] === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All statuses</option>
                <option value="completed" <?php if($filters['status'] === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                <option value="failed" <?php if($filters['status'] === 'failed'): echo 'selected'; endif; ?>>Failed</option>
            </select>
            <input name="from" type="date" value="<?php echo e($filters['from']); ?>" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <input name="to" type="date" value="<?php echo e($filters['to']); ?>" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <div class="flex gap-2 lg:col-span-6">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="<?php echo e(route('super-admin.system.backups.index')); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset</a>
            </div>
        </form>

        <section class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Backup Name</th>
                        <th class="px-4 py-3 font-medium">Created By</th>
                        <th class="px-4 py-3 font-medium">Created Date</th>
                        <th class="px-4 py-3 font-medium">Size</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3">
                                <div class="font-medium text-white"><?php echo e($backup->label); ?></div>
                                <div class="text-xs text-slate-500">
                                    <?php echo e($backup->typeLabel()); ?>

                                    <?php if($backup->includedTableCount()): ?>
                                        · <?php echo e($backup->includedTableCount()); ?> tables
                                    <?php endif; ?>
                                    <?php if($backup->includedFileCount()): ?>
                                        · <?php echo e($backup->includedFileCount()); ?> files
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3"><?php echo e($backup->creator?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap"><?php echo e(optional($backup->completed_at)->format('M d, Y g:i A') ?? '—'); ?></td>
                            <td class="px-4 py-3"><?php echo e($backup->formattedSize()); ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200"><?php echo e(ucfirst($backup->status)); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?php echo e(route('super-admin.system.backups.download', $backup)); ?>"
                                        class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10">Download</a>
                                    <a href="<?php echo e(route('super-admin.system.backups.show', $backup)); ?>"
                                        class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800">View Details</a>
                                    <button type="button" disabled title="Available in a future update"
                                        class="cursor-not-allowed rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-500">
                                        Restore
                                    </button>
                                    <form method="POST" action="<?php echo e(route('super-admin.system.backups.destroy', $backup)); ?>"
                                        onsubmit="return confirm('Deleting this backup cannot be undone.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                No backups yet. Create a recovery point before major system changes.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <div class="mt-6"><?php echo e($backups->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/system/backups.blade.php ENDPATH**/ ?>