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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Import ' . $rosterLabel . ' Roster','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Import ' . $rosterLabel . ' Roster'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-4">
            <a href="<?php echo e(route($routePrefix.'.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to roster</a>
        </div>

        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Import '.$rosterLabel.' Roster',
            'description' => 'Upload the official roster CSV. Matching identity is required before passkey registration.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
                <h2 class="text-lg font-semibold text-white">Upload CSV</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Existing roster entries are matched by account ID and updated. Records that have already registered cannot be overwritten.
                </p>

                <form method="POST" action="<?php echo e(route($routePrefix.'.import.store')); ?>" enctype="multipart/form-data" class="mt-6 space-y-5">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-slate-300">CSV File</label>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv,text/plain" required
                            class="mt-2 block w-full cursor-pointer rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-violet-500" />
                        <?php $__errorArgs = ['csv_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                            Import Roster
                        </button>
                        <a href="<?php echo e(route($routePrefix.'.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">CSV Format</h2>
                <p class="mt-2 text-sm text-slate-400">Maximum <?php echo e(number_format($maxRows)); ?> rows per upload. UTF-8 encoding recommended.</p>

                <div class="mt-4 space-y-3 text-sm text-slate-300">
                    <p><span class="font-medium text-white">Columns:</span> <?php echo e(implode(', ', $columns)); ?></p>
                    <p class="text-slate-400">Registration matches account ID plus first and last name (case-insensitive).</p>
                </div>

                <a href="<?php echo e(route($routePrefix.'.import.template')); ?>" class="mt-5 inline-flex rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">
                    Download template CSV
                </a>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/rosters/import.blade.php ENDPATH**/ ?>