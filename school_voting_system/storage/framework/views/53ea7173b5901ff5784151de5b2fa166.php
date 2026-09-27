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
    <?php if (isset($component)) { $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => ''.e($competition->title).'','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($competition->title).'','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('faculty.judging.index')); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Assigned competitions</a>
            <div class="flex flex-wrap items-center justify-end gap-2">
                <?php if($competition->hasPublishedResults()): ?>
                    <a
                        href="<?php echo e(route('faculty.results.talent.show', [$competition, 'from' => 'assigned'])); ?>"
                        class="rounded-xl border border-teal-500/30 bg-teal-500/10 px-3 py-1.5 text-sm font-semibold text-teal-100 transition hover:bg-teal-500/20"
                    >
                        View official results
                    </a>
                <?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalaba5df20cb4f1c691f51aa563c38f95d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaba5df20cb4f1c691f51aa563c38f95d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty.judging-phase-badge','data' => ['event' => $competition,'showOpensAt' => true,'class' => 'text-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty.judging-phase-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($competition),'show-opens-at' => true,'class' => 'text-right']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaba5df20cb4f1c691f51aa563c38f95d)): ?>
<?php $attributes = $__attributesOriginalaba5df20cb4f1c691f51aa563c38f95d; ?>
<?php unset($__attributesOriginalaba5df20cb4f1c691f51aa563c38f95d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaba5df20cb4f1c691f51aa563c38f95d)): ?>
<?php $component = $__componentOriginalaba5df20cb4f1c691f51aa563c38f95d; ?>
<?php unset($__componentOriginalaba5df20cb4f1c691f51aa563c38f95d); ?>
<?php endif; ?>
            </div>
        </div>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white"><?php echo e($competition->title); ?></h2>
                    <p class="mt-1 text-sm text-slate-400"><?php echo e($competition->votingMethodLabel()); ?> · <?php echo e($competition->displayStatusLabel()); ?></p>
                </div>
                <div class="text-right text-sm text-slate-400">
                    <p><?php echo e($progress['submitted']); ?>/<?php echo e($progress['approved']); ?> submitted</p>
                    <p><?php echo e($progress['remaining']); ?> remaining</p>
                </div>
            </div>
        </section>

        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('faculty.judging._performance-row', [
                    'competition' => $competition,
                    'entry' => $entry,
                    'sheet' => $sheets->get($entry->id),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    No approved performances are ready for judging yet.
                </div>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $attributes = $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $component = $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/judging/show.blade.php ENDPATH**/ ?>