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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Fundraising Reports','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Fundraising Reports','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Fundraising Reports',
            'description' => 'Donation summary, goal progress, and transactions across all campaigns.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('admin.reports.index')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Election Reports</a>
            <a href="<?php echo e(route('admin.reports.talent')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Talent Reports</a>
            <a href="<?php echo e(route('admin.reports.fundraising')); ?>" class="rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-1.5 text-sm font-semibold text-white">Fundraising Reports</a>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Campaigns</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($summary['campaigns'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Goal</p>
                <p class="mt-1 text-2xl font-bold text-white">₱<?php echo e(number_format($summary['total_goal'], 2)); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Raised</p>
                <p class="mt-1 text-2xl font-bold text-emerald-300">₱<?php echo e(number_format($summary['total_raised'], 2)); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Donations</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($summary['total_donations'])); ?></p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Campaign</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Donations</th>
                        <th class="px-4 py-3 text-right">Goal</th>
                        <th class="px-4 py-3 text-right">Raised</th>
                        <th class="px-4 py-3">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $fundraisers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fundraiser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $progress = $fundraiser->progressPercent(); ?>
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-semibold text-white"><?php echo e($fundraiser->title); ?></td>
                            <td class="px-4 py-3 text-xs"><?php echo e($fundraiser->displayStatusLabel()); ?></td>
                            <td class="px-4 py-3 text-center"><?php echo e(number_format($fundraiser->donations_count)); ?></td>
                            <td class="px-4 py-3 text-right">₱<?php echo e(number_format((float) $fundraiser->goal_amount, 2)); ?></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300">₱<?php echo e(number_format((float) $fundraiser->amount_raised, 2)); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-emerald-500" style="width: <?php echo e(min(100, $progress)); ?>%"></div>
                                    </div>
                                    <span class="text-xs text-slate-400"><?php echo e(round($progress)); ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No fundraising campaigns yet.</td></tr>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/reports/fundraising.blade.php ENDPATH**/ ?>