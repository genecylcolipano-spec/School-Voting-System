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
            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide <?php echo e($acceptingScores ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/30 bg-amber-500/10 text-amber-200'); ?>">
                <?php echo e($acceptingScores ? 'Judging open' : 'Judging closed'); ?>

            </span>
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
                <?php
                    $sheet = $sheets->get($entry->id);
                    $status = $sheet?->status?->label() ?? 'Not started';
                    $tone = match ($sheet?->status?->value ?? null) {
                        'submitted' => 'text-emerald-200',
                        'draft' => 'text-amber-200',
                        default => 'text-slate-400',
                    };
                ?>
                <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-teal-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <div class="min-w-0">
                        <p class="font-semibold text-white"><?php echo e($entry->display_name); ?></p>
                        <p class="mt-1 text-sm text-slate-400">
                            <?php echo e($entry->performance_title ?: 'Performance'); ?>

                            <?php if($entry->talent_category): ?>
                                · <?php echo e($entry->talent_category->label()); ?>

                            <?php endif; ?>
                        </p>
                        <p class="mt-1 text-xs <?php echo e($tone); ?>">
                            <?php echo e($status); ?>

                            <?php if($sheet): ?>
                                · <?php echo e(number_format((float) $sheet->total_score, 2)); ?> pts
                            <?php endif; ?>
                        </p>
                    </div>
                    <a
                        href="<?php echo e(route('faculty.judging.score', [$competition, $entry])); ?>"
                        class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
                    >
                        <?php echo e($sheet?->isLocked() ? 'View scores' : ($sheet ? 'Continue' : 'Score')); ?>

                    </a>
                </article>
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