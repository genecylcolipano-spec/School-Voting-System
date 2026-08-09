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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $pageTitle,'user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pageTitle),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => $pageTitle,
            'description' => $role->value === 'faculty'
                ? 'Registered faculty system accounts only. Official faculty records live under Roster Management.'
                : 'Registered administrator system accounts only. Official administrator records live under Roster Management.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 flex flex-wrap justify-end gap-3">
            <a href="<?php echo e($role->value === 'faculty' ? route('super-admin.roster.faculty.index') : route('super-admin.roster.administrators.index')); ?>"
               class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">
                Manage <?php echo e($role->value === 'faculty' ? 'Faculty' : 'Administrator'); ?> Roster
            </a>
            <a href="<?php echo e($createRoute); ?>" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                <?php echo e($role->value === 'faculty' ? 'Add Faculty' : 'Add Administrator'); ?>

            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
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
        <?php echo $__env->make('admin.partials.enrollment-link-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php $__currentLoopData = [
                ['Total Records', $summary['total']],
                ['Active Accounts', $summary['active']],
                ['Inactive Accounts', $summary['inactive']],
                ['Registered Devices', $summary['devices']],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
                    <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($value)); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <form method="GET" action="<?php echo e(route($indexRouteName)); ?>" class="mb-6 flex flex-wrap gap-3">
            <input
                name="q"
                type="search"
                value="<?php echo e(request('q')); ?>"
                placeholder="<?php echo e($role->value === 'faculty' ? 'Search by Faculty ID, Name, or Email...' : 'Search by Administrator ID, Name, or Email...'); ?>"
                class="min-w-[16rem] flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"
            />
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100">
                <option value="">All statuses</option>
                <option value="active" <?php if($statusFilter === 'active'): echo 'selected'; endif; ?>>Active</option>
                <option value="inactive" <?php if($statusFilter === 'inactive'): echo 'selected'; endif; ?>>Suspended</option>
                <option value="archived" <?php if($statusFilter === 'archived'): echo 'selected'; endif; ?>>Deactivated</option>
            </select>
            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Search</button>
            <?php if(request()->filled('q') || request()->filled('status')): ?>
                <a href="<?php echo e(route($indexRouteName)); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear</a>
            <?php endif; ?>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Account ID</th>
                        <th class="px-4 py-3"><?php echo e($role->value === 'faculty' ? 'Faculty Name' : 'Administrator Name'); ?></th>
                        <th class="hidden px-4 py-3 lg:table-cell">Email Address</th>
                        <?php if($role->value === 'admin'): ?>
                            <th class="hidden px-4 py-3 md:table-cell">Role</th>
                        <?php else: ?>
                            <th class="hidden px-4 py-3 md:table-cell">Department</th>
                            <th class="hidden px-4 py-3 md:table-cell">Assigned Competitions</th>
                        <?php endif; ?>
                        <th class="px-4 py-3">Account Status</th>
                        <th class="hidden px-4 py-3 sm:table-cell">Registered Devices</th>
                        <th class="hidden px-4 py-3 xl:table-cell">Last Login</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-slate-800/80 text-slate-200 transition hover:bg-slate-950/40">
                            <td class="px-4 py-3 font-mono text-xs text-slate-300"><?php echo e($account->account_id); ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-white"><?php echo e($account->name); ?></div>
                                <div class="mt-0.5 text-xs text-slate-500 lg:hidden"><?php echo e($account->email); ?></div>
                            </td>
                            <td class="hidden px-4 py-3 text-slate-400 lg:table-cell"><?php echo e($account->email); ?></td>
                            <?php if($role->value === 'admin'): ?>
                                <td class="hidden px-4 py-3 text-slate-300 md:table-cell"><?php echo e($account->staffRole?->name ?? $account->roleLabel()); ?></td>
                            <?php else: ?>
                                <td class="hidden px-4 py-3 text-slate-300 md:table-cell"><?php echo e($account->departmentLabel()); ?></td>
                                <td class="hidden px-4 py-3 text-slate-300 md:table-cell"><?php echo e(number_format($account->judging_assignments_count ?? 0)); ?></td>
                            <?php endif; ?>
                            <td class="px-4 py-3">
                                <?php ($accountStatus = $account->accountStatusLabel()); ?>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'rounded-full border px-2 py-0.5 text-xs font-semibold',
                                    'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $accountStatus === 'Active',
                                    'border-amber-500/30 bg-amber-500/10 text-amber-200' => $accountStatus === 'Suspended',
                                    'border-slate-600 bg-slate-800/80 text-slate-300' => $accountStatus === 'Deactivated',
                                ]); ?>"><?php echo e($accountStatus); ?></span>
                            </td>
                            <td class="hidden px-4 py-3 sm:table-cell"><?php echo e($account->passkeys_count); ?></td>
                            <td class="hidden px-4 py-3 text-slate-400 xl:table-cell">
                                <?php if($account->last_login_at): ?>
                                    <?php echo e(\Illuminate\Support\Carbon::parse($account->last_login_at)->format('M d, Y g:i A')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if (isset($component)) { $__componentOriginal49c58217ece949d54afc4039c845db44 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49c58217ece949d54afc4039c845db44 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.user-action-menu','data' => ['account' => $account,'variant' => $role->value === 'faculty' ? 'faculty' : 'admin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.user-action-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['account' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($account),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($role->value === 'faculty' ? 'faculty' : 'admin')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49c58217ece949d54afc4039c845db44)): ?>
<?php $attributes = $__attributesOriginal49c58217ece949d54afc4039c845db44; ?>
<?php unset($__attributesOriginal49c58217ece949d54afc4039c845db44); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49c58217ece949d54afc4039c845db44)): ?>
<?php $component = $__componentOriginal49c58217ece949d54afc4039c845db44; ?>
<?php unset($__componentOriginal49c58217ece949d54afc4039c845db44); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($role->value === 'faculty' ? 9 : 8); ?>" class="px-4 py-8 text-center text-slate-400">No registered <?php echo e(strtolower($pageTitle)); ?> accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6"><?php echo e($accounts->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/staff-users/index.blade.php ENDPATH**/ ?>