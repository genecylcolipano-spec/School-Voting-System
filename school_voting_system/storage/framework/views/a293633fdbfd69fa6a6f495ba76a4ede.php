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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Manage Students','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manage Students','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Students',
            'description' => 'Registered student system accounts only. Official institutional records live under Roster Management.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if(session('enrollment_url')): ?>
            <div class="mb-4 rounded-xl border border-violet-500/20 bg-slate-900/70 p-4">
                <p class="text-sm text-slate-300">Enrollment link (valid 2 hours):</p>
                <a href="<?php echo e(session('enrollment_url')); ?>" class="mt-2 block break-all text-sm text-violet-300 hover:text-violet-200"><?php echo e(session('enrollment_url')); ?></a>
            </div>
        <?php endif; ?>

        <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <input
                name="q"
                type="search"
                value="<?php echo e(request('q')); ?>"
                placeholder="Search by Student ID, name, or email"
                class="w-full min-w-0 flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 sm:min-w-[16rem]"
            />
            <select name="status" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:w-auto">
                <option value="">All account statuses</option>
                <option value="active" <?php if(($statusFilter ?? '') === 'active'): echo 'selected'; endif; ?>>Active</option>
                <option value="suspended" <?php if(in_array($statusFilter ?? '', ['suspended', 'inactive'], true)): echo 'selected'; endif; ?>>Suspended</option>
                <option value="deactivated" <?php if(in_array($statusFilter ?? '', ['deactivated', 'archived'], true)): echo 'selected'; endif; ?>>Deactivated</option>
            </select>
            <?php if(count($gradeLevels) > 0): ?>
                <select name="grade_level" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:w-auto">
                    <option value="">All grades</option>
                    <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($grade); ?>" <?php if(request('grade_level') === $grade): echo 'selected'; endif; ?>>Grade <?php echo e($grade); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endif; ?>
            <?php if(count($sections) > 0): ?>
                <select name="section" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:w-auto">
                    <option value="">All sections</option>
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sectionOption); ?>" <?php if(request('section') === $sectionOption): echo 'selected'; endif; ?>>Section <?php echo e($sectionOption); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endif; ?>
            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white sm:w-auto">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Student ID</th>
                        <th class="px-4 py-3 font-medium">Student Name</th>
                        <th class="px-4 py-3 font-medium">Grade</th>
                        <th class="px-4 py-3 font-medium">Section</th>
                        <th class="hidden px-4 py-3 font-medium lg:table-cell">Email Address</th>
                        <th class="px-4 py-3 font-medium">Account Status</th>
                        <th class="hidden px-4 py-3 font-medium sm:table-cell">Registered Devices</th>
                        <th class="hidden px-4 py-3 font-medium xl:table-cell">Last Login</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3 font-mono text-xs"><?php echo e($student->account_id); ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-white"><?php echo e($student->name); ?></div>
                                <div class="text-xs text-slate-500 lg:hidden"><?php echo e($student->email); ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <?php if($student->grade_level): ?>
                                    <?php echo e($student->grade_level); ?>

                                <?php else: ?>
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-200">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3"><?php echo e($student->section ?: '—'); ?></td>
                            <td class="hidden px-4 py-3 text-slate-400 lg:table-cell"><?php echo e($student->email); ?></td>
                            <td class="px-4 py-3">
                                <?php ($accountStatus = $student->accountStatusLabel()); ?>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'rounded-full border px-2 py-0.5 text-xs font-semibold',
                                    'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $accountStatus === 'Active',
                                    'border-amber-500/30 bg-amber-500/10 text-amber-200' => $accountStatus === 'Suspended',
                                    'border-slate-600 bg-slate-800/80 text-slate-300' => $accountStatus === 'Deactivated',
                                ]); ?>"><?php echo e($accountStatus); ?></span>
                            </td>
                            <td class="hidden px-4 py-3 sm:table-cell"><?php echo e($student->passkeys_count); ?></td>
                            <td class="hidden px-4 py-3 text-slate-400 xl:table-cell">
                                <?php if($student->last_login_at): ?>
                                    <?php echo e(\Illuminate\Support\Carbon::parse($student->last_login_at)->format('M d, Y g:i A')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <?php if (isset($component)) { $__componentOriginal49c58217ece949d54afc4039c845db44 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49c58217ece949d54afc4039c845db44 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.user-action-menu','data' => ['account' => $student,'variant' => 'student']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.user-action-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['account' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($student),'variant' => 'student']); ?>
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
                            <td colspan="9" class="px-4 py-6 text-slate-400">No registered student accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6"><?php echo e($students->links()); ?></div>
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/regular-admin-dashboard.js']); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/students/index.blade.php ENDPATH**/ ?>