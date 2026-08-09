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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Talent Competition Reports','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Talent Competition Reports','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Talent Competition Reports',
            'description' => 'Participants, votes, performance statistics, and winners across all competitions.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('admin.reports.index')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Election Reports</a>
            <a href="<?php echo e(route('admin.reports.talent')); ?>" class="rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-1.5 text-sm font-semibold text-white">Talent Reports</a>
            <a href="<?php echo e(route('admin.reports.fundraising')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Fundraising Reports</a>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Competitions</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($totals['events'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Participants</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($totals['participants'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Approved</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($totals['approved'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Votes</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($totals['votes'])); ?></p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Competition</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Participants</th>
                        <th class="px-4 py-3 text-center">Approved</th>
                        <th class="px-4 py-3 text-center">Rejected</th>
                        <th class="px-4 py-3 text-center">Votes</th>
                        <th class="px-4 py-3">Voting Method</th>
                        <th class="px-4 py-3 text-center">Winners</th>
                        <th class="px-4 py-3 text-right">Reports</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-semibold text-white"><?php echo e($row['name']); ?></td>
                            <td class="px-4 py-3"><?php echo e($row['category']); ?></td>
                            <td class="px-4 py-3 text-xs"><?php echo e($row['status']); ?></td>
                            <td class="px-4 py-3 text-center"><?php echo e(number_format($row['participants'])); ?></td>
                            <td class="px-4 py-3 text-center text-emerald-300"><?php echo e(number_format($row['approved'])); ?></td>
                            <td class="px-4 py-3 text-center text-rose-300"><?php echo e(number_format($row['rejected'])); ?></td>
                            <td class="px-4 py-3 text-center font-bold text-white"><?php echo e(number_format($row['votes'])); ?></td>
                            <td class="px-4 py-3 text-xs"><?php echo e($row['voting_method']); ?></td>
                            <td class="px-4 py-3 text-center"><?php echo e($row['winners']); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <a href="<?php echo e($row['show_url']); ?>" class="font-semibold text-violet-300 hover:text-violet-200">View</a>
                                    <a href="<?php echo e($row['export_pdf']); ?>" class="font-semibold text-cyan-300 hover:text-cyan-200">PDF</a>
                                    <a href="<?php echo e($row['export_excel']); ?>" class="font-semibold text-cyan-300 hover:text-cyan-200">Excel</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="10" class="px-4 py-10 text-center text-slate-400">No talent competitions in your scope yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/reports/talent.blade.php ENDPATH**/ ?>