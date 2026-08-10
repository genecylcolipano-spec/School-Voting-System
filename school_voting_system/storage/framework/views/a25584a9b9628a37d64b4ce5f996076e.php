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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'View ' . $rosterLabel . ' Roster','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('View ' . $rosterLabel . ' Roster'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => $rosterLabel.' Roster Record',
            'description' => $record->first_name.' '.$record->last_name,
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="<?php echo e(route($routePrefix.'.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to roster</a>
            <a href="<?php echo e(route($routePrefix.'.edit', $record)); ?>" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
        </div>

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                    <dt class="text-slate-500"><?php echo e($rosterIdLabel); ?></dt>
                    <dd class="font-mono text-slate-200"><?php echo e($record->account_id); ?></dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                    <dt class="text-slate-500">Name</dt>
                    <dd class="text-slate-200"><?php echo e($record->first_name); ?> <?php echo e($record->last_name); ?></dd>
                </div>
                <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500"><?php echo e($field['label']); ?></dt>
                        <dd class="text-slate-200"><?php echo e($record->{$field['name']} ?: '—'); ?></dd>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Registration Status</dt>
                    <dd>
                        <?php if($record->archived_at): ?>
                            <span class="rounded-full border border-slate-600 bg-slate-800/80 px-2 py-0.5 text-xs font-semibold text-slate-300">Archived</span>
                        <?php elseif($record->is_registered || (method_exists($record, 'isFullyRegistered') && $record->isFullyRegistered())): ?>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">Registered</span>
                        <?php elseif(method_exists($record, 'isEnrollmentPending') && $record->isEnrollmentPending()): ?>
                            <span class="rounded-full border border-sky-500/30 bg-sky-500/10 px-2 py-0.5 text-xs font-semibold text-sky-200">Enrollment Pending</span>
                        <?php else: ?>
                            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-200">Not Registered</span>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/rosters/show.blade.php ENDPATH**/ ?>