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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $account->name,'user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($account->name),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('admin.students.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to students</a>
            <div class="flex flex-wrap gap-2">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('updateStudentRecord', $account)): ?>
                    <a href="<?php echo e(route('admin.students.edit', $account)); ?>" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('issuePasskeyReset', $account)): ?>
                    <form method="POST" action="<?php echo e(route('admin.passkey.reset', $account)); ?>" onsubmit="return confirm('Generate a passkey reset / enrollment link? This emails the address on file.');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset Passkey</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php echo $__env->make('admin.partials.enrollment-link-banner', ['contactAccount' => $account], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-mono text-xs text-slate-500"><?php echo e($account->account_id); ?></p>
                    <h2 class="mt-1 text-2xl font-bold text-white"><?php echo e($account->name); ?></h2>
                    <?php echo $__env->make('admin.partials.profile-contact', ['account' => $account], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <p class="mt-1 text-sm text-slate-400">
                        Grade <?php echo e($account->grade_level ?: '—'); ?> · Section <?php echo e($account->section ?: '—'); ?>

                    </p>
                </div>
                <div class="text-right">
                    <?php ($accountStatus = $account->accountStatusLabel()); ?>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'rounded-full border px-3 py-1 text-xs font-semibold',
                        'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $accountStatus === 'Active',
                        'border-amber-500/30 bg-amber-500/10 text-amber-200' => $accountStatus === 'Suspended',
                        'border-slate-600 bg-slate-800 text-slate-300' => $accountStatus === 'Deactivated',
                    ]); ?>"><?php echo e($accountStatus); ?></span>
                    <p class="mt-2 text-xs text-slate-500"><?php echo e($account->passkeys_count); ?> registered device(s)</p>
                </div>
            </div>
        </section>

        <section id="devices" class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Registered Devices</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">Name</th>
                            <th class="px-2 py-2">Status</th>
                            <th class="px-2 py-2">Last used</th>
                            <th class="px-2 py-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2"><?php echo e($device->device_name ?: $device->name); ?></td>
                                <td class="px-2 py-2"><?php echo e($device->status?->value ?? $device->status ?? 'active'); ?></td>
                                <td class="px-2 py-2 text-slate-400"><?php echo e(optional($device->last_used_at)->format('M d, Y g:i A') ?? '—'); ?></td>
                                <td class="px-2 py-2 text-slate-400"><?php echo e(optional($device->created_at)->format('M d, Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="px-2 py-6 text-center text-slate-500">No registered passkey devices.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="login-history" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Login History</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">When</th>
                            <th class="px-2 py-2">Browser</th>
                            <th class="px-2 py-2">OS</th>
                            <th class="px-2 py-2">IP</th>
                            <th class="px-2 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $loginHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2 text-slate-400"><?php echo e(optional($row['occurred_at'])->format('M d, Y g:i A')); ?></td>
                                <td class="px-2 py-2"><?php echo e($row['browser']); ?></td>
                                <td class="px-2 py-2"><?php echo e($row['os']); ?></td>
                                <td class="px-2 py-2 font-mono text-xs"><?php echo e($row['ip_address'] ?? '—'); ?></td>
                                <td class="px-2 py-2"><?php echo e($row['status']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="px-2 py-6 text-center text-slate-500">No login history available yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/students/show.blade.php ENDPATH**/ ?>