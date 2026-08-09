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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Donations','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Donations','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Donations',
            'description' => 'All contributions received across your fundraising campaigns.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Raised</p>
                <p class="mt-1 text-2xl font-bold text-white">₱<?php echo e(number_format($summary['total_raised'], 2)); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Donations</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($summary['total_donations'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Unique Donors</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($summary['unique_donors'])); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Active Campaigns</p>
                <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($summary['active_fundraisers'])); ?></p>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('admin.fundraisers.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Campaigns</a>
            <a href="<?php echo e(route('admin.fundraisers.transactions')); ?>" class="text-sm font-semibold text-slate-400 hover:text-white">Transactions →</a>

            <form method="GET" action="<?php echo e(route('admin.fundraisers.donations')); ?>" class="ml-auto">
                <select name="fundraiser" onchange="this.form.submit()" class="rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                    <option value="">All campaigns</option>
                    <?php $__currentLoopData = $fundraisers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($f->id); ?>" <?php if((string) $selectedFundraiser === (string) $f->id): echo 'selected'; endif; ?>><?php echo e($f->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Donor</th>
                        <th class="px-4 py-3">Campaign</th>
                        <th class="px-4 py-3">Message</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $donations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $donation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-semibold text-white">
                                <?php echo e($donation->is_anonymous ? 'Anonymous' : ($donation->donor?->name ?? '—')); ?>

                            </td>
                            <td class="px-4 py-3"><?php echo e($donation->fundraiser?->title ?? '—'); ?></td>
                            <td class="max-w-xs truncate px-4 py-3 text-slate-400"><?php echo e($donation->message ?: '—'); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400"><?php echo e(optional($donation->donated_at)->format('M d, Y g:i A')); ?></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300">₱<?php echo e(number_format((float) $donation->amount, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No donations recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($donations->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/fundraisers/donations.blade.php ENDPATH**/ ?>