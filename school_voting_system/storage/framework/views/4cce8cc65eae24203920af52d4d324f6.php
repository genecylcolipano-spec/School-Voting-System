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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Backup Details','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Backup Details','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Backup Details',
            'description' => 'Recovery point contents and metadata.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('super-admin.system.backups.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to Backup Manager</a>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('super-admin.system.backups.download', $backup)); ?>"
                    class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Download</a>
                <button type="button" disabled title="Available in a future update"
                    class="cursor-not-allowed rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-500">
                    Restore — Future Enhancement
                </button>
            </div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</p>
                <p class="mt-2 text-lg font-bold text-emerald-300"><?php echo e(ucfirst($details['status'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Archive Size</p>
                <p class="mt-2 text-lg font-bold text-white"><?php echo e($details['file_size_label']); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Database Size</p>
                <p class="mt-2 text-lg font-bold text-white"><?php echo e($details['database_size_label']); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Uploaded Files</p>
                <p class="mt-2 text-lg font-bold text-white"><?php echo e($details['files_size_label']); ?></p>
            </div>
        </div>

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-white">Overview</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Backup Name</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($details['label']); ?></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Type</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($details['type_label']); ?></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Created By</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($details['created_by']); ?></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Creation Date</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e(optional($details['created_at'])->format('M d, Y g:i A') ?? '—'); ?></dd>
                </div>
                <?php if($details['notes']): ?>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Notes</dt>
                        <dd class="mt-1 text-sm text-slate-300"><?php echo e($details['notes']); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Included Tables</h2>
                <p class="mt-1 text-sm text-slate-400"><?php echo e(count($details['tables'])); ?> table(s) exported</p>
                <div class="mt-4 max-h-80 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $details['tables']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between border-b border-slate-800/80 py-2 text-sm">
                            <span class="font-mono text-slate-200"><?php echo e($table['name'] ?? $table); ?></span>
                            <span class="text-slate-400"><?php echo e(number_format($table['rows'] ?? 0)); ?> rows</span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-6 text-center text-sm text-slate-500">No table inventory stored for this backup (legacy partial export).</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Included Uploaded Files</h2>
                <p class="mt-1 text-sm text-slate-400"><?php echo e(count($details['files'])); ?> file(s) packaged</p>
                <div class="mt-4 max-h-80 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $details['files']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-800/80 py-2 text-sm">
                            <span class="min-w-0 truncate font-mono text-slate-200" title="<?php echo e($file['path'] ?? ''); ?>"><?php echo e($file['path'] ?? '—'); ?></span>
                            <span class="shrink-0 text-slate-400">
                                <?php
                                    $bytes = (int) ($file['size'] ?? 0);
                                    $sizeLabel = $bytes >= 1048576
                                        ? round($bytes / 1048576, 2).' MB'
                                        : ($bytes >= 1024 ? round($bytes / 1024, 1).' KB' : $bytes.' B');
                                ?>
                                <?php echo e($sizeLabel); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-6 text-center text-sm text-slate-500">No uploaded files were included in this backup.</p>
                    <?php endif; ?>
                </div>
            </section>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/system/backup-show.blade.php ENDPATH**/ ?>