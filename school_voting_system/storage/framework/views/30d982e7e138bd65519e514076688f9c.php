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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => 'Assigned Competitions','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Assigned Competitions','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                Talent competitions where you are assigned as a judge. You can only judge competitions assigned to you by the Super Administrator.
            </p>
        </section>

        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $competitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $competition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $p = $progress[$competition->id] ?? ['approved' => 0, 'submitted' => 0, 'remaining' => 0, 'drafted' => 0, 'percent' => 0, 'judging_status' => 'Not Started'];
                    $assignment = $assignments[$competition->id] ?? null;
                    $registrationOpen = $competition->isRegistrationOpen();
                ?>
                <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
                    <?php if (isset($component)) { $__componentOriginaldc620424818b8a9f9fa858444666ff45 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc620424818b8a9f9fa858444666ff45 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.competition-card-banner','data' => ['event' => $competition]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('competition-card-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($competition)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc620424818b8a9f9fa858444666ff45)): ?>
<?php $attributes = $__attributesOriginaldc620424818b8a9f9fa858444666ff45; ?>
<?php unset($__attributesOriginaldc620424818b8a9f9fa858444666ff45); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc620424818b8a9f9fa858444666ff45)): ?>
<?php $component = $__componentOriginaldc620424818b8a9f9fa858444666ff45; ?>
<?php unset($__componentOriginaldc620424818b8a9f9fa858444666ff45); ?>
<?php endif; ?>
                    <div class="p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-200">
                                        <?php echo e($assignment?->roleLabel() ?? 'Judge'); ?>

                                    </span>
                                    <span class="rounded-full border border-slate-600/40 bg-slate-800/60 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-300">
                                        <?php echo e($competition->talent_category?->label() ?? 'Talent'); ?>

                                    </span>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                                        'bg-emerald-500/15 text-emerald-200' => $registrationOpen,
                                        'bg-slate-800/80 text-slate-300' => ! $registrationOpen,
                                    ]); ?>">
                                        <?php echo e($registrationOpen ? 'Registration Open' : 'Registration Closed'); ?>

                                    </span>
                                </div>
                                <h2 class="mt-2 text-lg font-semibold text-white"><?php echo e($competition->title); ?></h2>
                                <p class="mt-1 text-sm text-slate-400">
                                    <?php echo e($competition->displayStatusLabel()); ?>

                                    · <?php echo e(optional($competition->event_date)->format('M d, Y') ?? 'Date TBA'); ?>

                                    · <?php echo e($competition->approved_entries_count ?? 0); ?> participants
                                </p>
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Progress · <?php echo e($p['judging_status']); ?></span>
                                        <span><?php echo e($p['submitted']); ?>/<?php echo e($p['approved']); ?> · <?php echo e($p['percent']); ?>%</span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400" style="width: <?php echo e(min(100, $p['percent'])); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="<?php echo e(route('faculty.judging.show', $competition)); ?>"
                                    class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
                                >
                                    Open Judging
                                </a>
                                <a
                                    href="<?php echo e(route('faculty.judging.show', $competition)); ?>"
                                    class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800"
                                >
                                    View Participants
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    You have not been assigned to any competitions yet. The Super Administrator must assign you as a judge.
                </div>
            <?php endif; ?>
        </div>

        <div><?php echo e($competitions->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/judging/index.blade.php ENDPATH**/ ?>