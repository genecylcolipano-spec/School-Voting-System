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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $rosterLabel . ' Roster','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rosterLabel . ' Roster'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => $rosterLabel.' Roster',
            'description' => 'Official institutional records used for registration verification. These are not system accounts.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 flex flex-wrap justify-end gap-3">
            <a href="<?php echo e(route($routePrefix.'.export')); ?>" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Export CSV</a>
            <a href="<?php echo e(route($routePrefix.'.import')); ?>" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Import CSV</a>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php ($importResult = session('import_result')); ?>
        <?php if($importResult && ! empty($importResult['errors'])): ?>
            <div class="mb-6 overflow-x-auto rounded-2xl border border-rose-500/20 bg-rose-500/5">
                <div class="border-b border-rose-500/20 px-4 py-3">
                    <h3 class="text-sm font-semibold text-rose-200">Rows with errors</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-rose-500/10 text-left text-slate-400">
                            <th class="px-4 py-2 font-medium">Row</th>
                            <th class="px-4 py-2 font-medium">Account ID</th>
                            <th class="px-4 py-2 font-medium">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $importResult['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b border-rose-500/10 text-slate-200">
                                <td class="px-4 py-2"><?php echo e($error['row']); ?></td>
                                <td class="px-4 py-2 font-mono text-xs"><?php echo e($error['account_id']); ?></td>
                                <td class="px-4 py-2 text-rose-200"><?php echo e($error['message']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <?php $__currentLoopData = [
                ['Total Records', $summary['total']],
                ['Registered', $summary['registered']],
                ['Enrollment Pending', $summary['enrollment_pending'] ?? 0],
                ['Not Registered', $summary['pending']],
                ['Archived', $summary['archived']],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
                    <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($value)); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <form method="GET" class="mb-6 flex flex-wrap gap-3">
            <input name="q" type="search" value="<?php echo e(request('q')); ?>" placeholder="Search <?php echo e(strtolower($rosterIdLabel)); ?> or name"
                class="min-w-[16rem] flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100">
                <option value="">Active roster</option>
                <option value="registered" <?php if($statusFilter === 'registered'): echo 'selected'; endif; ?>>Registered</option>
                <option value="enrollment_pending" <?php if($statusFilter === 'enrollment_pending'): echo 'selected'; endif; ?>>Enrollment Pending</option>
                <option value="not_registered" <?php if(in_array($statusFilter, ['pending', 'not_registered'], true)): echo 'selected'; endif; ?>>Not Registered</option>
                <option value="archived" <?php if($statusFilter === 'archived'): echo 'selected'; endif; ?>>Archived</option>
            </select>
            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium"><?php echo e($rosterIdLabel); ?></th>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-4 py-3 font-medium"><?php echo e($field['label']); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-4 py-3 font-medium">Registration Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3 font-mono text-xs"><?php echo e($record->account_id); ?></td>
                            <td class="px-4 py-3"><?php echo e($record->first_name); ?> <?php echo e($record->last_name); ?></td>
                            <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="px-4 py-3"><?php echo e($record->{$field['name']} ?: '—'); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-4 py-3">
                                <?php if($record->archived_at): ?>
                                    <span class="rounded-full border border-slate-600 bg-slate-800/80 px-2 py-0.5 text-xs font-semibold text-slate-300">Archived</span>
                                <?php elseif($record->is_registered || (method_exists($record, 'isFullyRegistered') && $record->isFullyRegistered())): ?>
                                    <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">Registered</span>
                                <?php elseif(method_exists($record, 'isEnrollmentPending') && $record->isEnrollmentPending()): ?>
                                    <span class="rounded-full border border-sky-500/30 bg-sky-500/10 px-2 py-0.5 text-xs font-semibold text-sky-200">Enrollment Pending</span>
                                <?php else: ?>
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-200">Not Registered</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?php echo e(route($routePrefix.'.show', $record)); ?>" class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                    <a href="<?php echo e(route($routePrefix.'.edit', $record)); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
                                    <?php if($record->archived_at): ?>
                                        <form method="POST" action="<?php echo e(route($routePrefix.'.restore', $record)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="rounded-lg border border-emerald-500/30 px-3 py-1.5 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/10">Restore</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?php echo e(route($routePrefix.'.archive', $record)); ?>" onsubmit="return confirm('Archive this roster record? It will no longer be usable for registration.');">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="rounded-lg border border-amber-500/30 px-3 py-1.5 text-xs font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(4 + count($extraFields)); ?>" class="px-4 py-6 text-slate-400">No roster records yet. Import a CSV to begin.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6"><?php echo e($records->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/rosters/index.blade.php ENDPATH**/ ?>