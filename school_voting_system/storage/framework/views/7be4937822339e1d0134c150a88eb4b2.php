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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Edit ' . $rosterLabel . ' Roster','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Edit ' . $rosterLabel . ' Roster'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Edit '.$rosterLabel.' Roster Record',
            'description' => 'Update an official institutional roster row.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="<?php echo e(route($routePrefix.'.update', $record)); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div>
                    <label class="block text-sm font-medium text-slate-300"><?php echo e($rosterIdLabel); ?></label>
                    <input name="account_id" value="<?php echo e(old('account_id', $record->account_id)); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 font-mono text-white">
                    <?php $__errorArgs = ['account_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">First Name</label>
                        <input name="first_name" value="<?php echo e(old('first_name', $record->first_name)); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Last Name</label>
                        <input name="last_name" value="<?php echo e(old('last_name', $record->last_name)); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <?php if(count($extraFields) > 0): ?>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <label class="block text-sm font-medium text-slate-300"><?php echo e($field['label']); ?></label>
                                <input name="<?php echo e($field['name']); ?>" value="<?php echo e(old($field['name'], $record->{$field['name']})); ?>" <?php if($field['required'] ?? false): echo 'required'; endif; ?>
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                                <?php $__errorArgs = [$field['name']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
                    <a href="<?php echo e(route($routePrefix.'.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                </div>
            </form>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/rosters/edit.blade.php ENDPATH**/ ?>