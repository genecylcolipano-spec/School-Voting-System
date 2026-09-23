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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Yearly Student Roster Sync','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Yearly Student Roster Sync','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-4">
            <a href="<?php echo e(route($routePrefix.'.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to student roster</a>
        </div>

        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Yearly student roster sync',
            'description' => 'Upload next year’s official list. Returning students keep the same Student ID and passkey. Missing students can be archived so they cannot sign in.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-white">Upload next school year CSV</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Use the same columns as the regular roster import: account_id, first_name, last_name, grade_level, section.
                    This does not create login accounts.
                </p>

                <form method="POST" action="<?php echo e(route($routePrefix.'.year-sync.preview')); ?>" enctype="multipart/form-data" class="mt-6 space-y-5">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="school_year" class="block text-sm font-medium text-slate-300">School year</label>
                        <input id="school_year" name="school_year" value="<?php echo e(old('school_year', $suggestedSchoolYear)); ?>" required
                            class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white" placeholder="2026-2027">
                        <p class="mt-1 text-xs text-slate-500">Format 2026-2027. This is stamped on every returning and new roster row.</p>
                        <?php $__errorArgs = ['school_year'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-slate-300">CSV file</label>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv,text/plain" required
                            class="mt-2 block w-full cursor-pointer rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-violet-500">
                        <?php $__errorArgs = ['csv_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="archive_missing" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('archive_missing', true)): echo 'checked'; endif; ?>>
                        <span>
                            <span class="font-medium text-white">Archive students missing from this file</span>
                            <span class="mt-1 block text-slate-500">Use this at year-end for graduates and transfers. Their portal login is deactivated. Vote history is kept. Uncheck if this file is only a partial section update.</span>
                        </span>
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Preview changes</button>
                        <a href="<?php echo e(route($routePrefix.'.import.template')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Download template</a>
                    </div>
                </form>
            </section>

            <aside class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 text-sm text-slate-400">
                <h2 class="text-lg font-semibold text-white">What this does</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>Same Student ID keeps the same account and passkey.</li>
                    <li>Grade and section are updated on the roster and the login record.</li>
                    <li>New IDs are added to the roster only. They still register once.</li>
                    <li>Regular Import CSV still refuses to overwrite registered rows. Use this page for year-end updates.</li>
                </ul>
            </aside>
        </div>

        <?php if($preview): ?>
            <section class="mt-8 space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Preview · SY <?php echo e($preview['school_year']); ?></h2>
                        <p class="mt-1 text-sm text-slate-400">Review the counts, then apply. Nothing is saved until you confirm.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="<?php echo e(route($routePrefix.'.year-sync.cancel')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear preview</button>
                        </form>
                        <form method="POST" action="<?php echo e(route($routePrefix.'.year-sync.apply')); ?>" onsubmit="return confirm('Apply this school year sync? Missing students will be archived if that option is on.');">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Apply sync</button>
                        </form>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <?php $__currentLoopData = [
                        ['Add to roster', $preview['counts']['created']],
                        ['Update class', $preview['counts']['updated']],
                        ['Restore', $preview['counts']['restored']],
                        ['Unchanged', $preview['counts']['unchanged']],
                        ['Archive', $preview['counts']['archived']],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
                            <p class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($value)); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php $__currentLoopData = [
                    ['updated', 'Class updates (sample)'],
                    ['created', 'New roster rows (sample)'],
                    ['restored', 'Returning archived students (sample)'],
                    ['archived', 'Will be archived (sample)'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $title]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(($preview['counts'][$key] ?? 0) > 0): ?>
                        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
                            <div class="border-b border-slate-800 px-4 py-3">
                                <h3 class="text-sm font-semibold text-white"><?php echo e($title); ?></h3>
                            </div>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800 text-left text-slate-400">
                                        <th class="px-4 py-2 font-medium">Student ID</th>
                                        <th class="px-4 py-2 font-medium">Name</th>
                                        <th class="px-4 py-2 font-medium">From</th>
                                        <th class="px-4 py-2 font-medium">To</th>
                                        <th class="px-4 py-2 font-medium">Login</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $preview[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-b border-slate-800/80 text-slate-200">
                                            <td class="px-4 py-2 font-mono text-xs"><?php echo e($row['account_id']); ?></td>
                                            <td class="px-4 py-2"><?php echo e($row['name']); ?></td>
                                            <td class="px-4 py-2 text-slate-400"><?php echo e($row['from']); ?></td>
                                            <td class="px-4 py-2"><?php echo e($row['to']); ?></td>
                                            <td class="px-4 py-2"><?php echo e($row['has_account'] ? 'Keep account' : 'Roster only'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/rosters/year-sync.blade.php ENDPATH**/ ?>