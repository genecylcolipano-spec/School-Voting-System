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
                <?php if($phase === 'past'): ?>
                    Finished assignments. Open judging to review your submitted scores. Official rankings appear after administrators publish results.
                <?php else: ?>
                    Competitions that are scheduled or open for judging. You can only judge competitions assigned to you by the Super Administrator.
                <?php endif; ?>
            </p>

            <nav class="mt-4 flex flex-wrap gap-2" aria-label="Assignment lists">
                <a
                    href="<?php echo e(route('faculty.judging.index', ['filter' => 'current'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition',
                        'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' => $phase === 'current',
                        'border border-slate-700 text-slate-300 hover:bg-slate-800' => $phase !== 'current',
                    ]); ?>"
                >
                    Current
                    <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[11px] leading-none"><?php echo e($currentCount); ?></span>
                </a>
                <a
                    href="<?php echo e(route('faculty.judging.index', ['filter' => 'past'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition',
                        'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' => $phase === 'past',
                        'border border-slate-700 text-slate-300 hover:bg-slate-800' => $phase !== 'past',
                    ]); ?>"
                >
                    Past
                    <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[11px] leading-none"><?php echo e($pastCount); ?></span>
                </a>
            </nav>
        </section>

        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $competitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $competition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $p = $progress[$competition->id] ?? ['approved' => 0, 'submitted' => 0, 'remaining' => 0, 'drafted' => 0, 'percent' => 0, 'judging_status' => 'Not Started'];
                    $assignment = $assignments[$competition->id] ?? null;
                    $judgingPhase = $competition->judgingPhase();
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
                                    <?php if (isset($component)) { $__componentOriginalaba5df20cb4f1c691f51aa563c38f95d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaba5df20cb4f1c691f51aa563c38f95d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty.judging-phase-badge','data' => ['event' => $competition]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty.judging-phase-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($competition)]); ?>
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
                                <h2 class="mt-2 text-lg font-semibold text-white"><?php echo e($competition->title); ?></h2>
                                <p class="mt-1 text-sm text-slate-400">
                                    <?php echo e($competition->status?->label() ?? $competition->displayStatusLabel()); ?>

                                    · <?php echo e(optional($competition->event_date)->format('M d, Y') ?? 'Date TBA'); ?>

                                    · <?php echo e($competition->approved_entries_count ?? 0); ?> participants
                                    <?php if($judgingPhase['key'] === 'scheduled' && filled($judgingPhase['opens_at'])): ?>
                                        · Opens <?php echo e($judgingPhase['opens_at']); ?>

                                    <?php elseif($judgingPhase['key'] === 'open' && filled($judgingPhase['closes_at'])): ?>
                                        · Closes <?php echo e($judgingPhase['closes_at']); ?>

                                    <?php endif; ?>
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
                            <div class="flex shrink-0 flex-col items-stretch gap-2 sm:items-end">
                                <a
                                    href="<?php echo e(route('faculty.judging.show', $competition)); ?>"
                                    class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-center text-sm font-semibold text-slate-950"
                                >
                                    Open Judging
                                </a>
                                <?php if($phase === 'past'): ?>
                                    <?php if($competition->hasPublishedResults()): ?>
                                        <a
                                            href="<?php echo e(route('faculty.results.talent.show', [$competition, 'from' => 'assigned'])); ?>"
                                            class="rounded-xl border border-teal-500/30 bg-teal-500/10 px-4 py-2 text-center text-sm font-semibold text-teal-100 transition hover:bg-teal-500/20"
                                        >
                                            View official results
                                        </a>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-2 text-center text-sm font-medium text-amber-200">
                                            Under administrator review
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    <?php if($phase === 'past'): ?>
                        No completed assignments yet.
                    <?php elseif($pastCount > 0): ?>
                        No competitions to judge right now. Finished assignments are listed under Past.
                    <?php else: ?>
                        You have not been assigned to any competitions yet. The Super Administrator must assign you as a judge.
                    <?php endif; ?>
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