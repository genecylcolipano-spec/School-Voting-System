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
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Results</h1>
                    <p class="mt-1 text-sm text-slate-400">View official results of student elections and voting events.</p>
                </div>
                <a href="<?php echo e(route('student.dashboard')); ?>" class="inline-flex w-full items-center justify-center rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 transition hover:bg-slate-800 sm:w-auto">
                    Back to dashboard
                </a>
            </div>

            <?php if(! $hasEvents): ?>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-cyan-500/20 bg-slate-900/50 px-6 py-16 text-center transition hover:border-cyan-500/30">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-500/10 text-3xl">🏆</div>
                    <h2 class="text-xl font-bold text-white">No Results Available</h2>
                    <p class="mt-2 max-w-md text-sm text-slate-400">Results will appear after voting events are completed.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="group rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 transition hover:-translate-y-0.5 hover:border-cyan-400/30 hover:bg-slate-900/90 sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xl" aria-hidden="true"><?php echo e($event['icon']); ?></span>
                                        <h2 class="text-lg font-semibold text-white"><?php echo e($event['name']); ?></h2>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                                        <span class="rounded-full bg-slate-800/80 px-2.5 py-0.5 text-xs font-medium text-slate-300"><?php echo e($event['category']); ?></span>
                                        <?php if($event['date']): ?>
                                            <span><?php echo e($event['date']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap items-center gap-3 sm:flex-col sm:items-end lg:flex-row lg:items-center">
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide',
                                        'bg-emerald-500/15 text-emerald-300' => ($event['student_status_tone'] ?? '') === 'closed',
                                        'bg-sky-500/15 text-sky-300' => ($event['student_status_tone'] ?? '') === 'live',
                                        'bg-amber-500/15 text-amber-300' => ($event['student_status_tone'] ?? '') === 'review',
                                        'bg-slate-700/80 text-slate-300' => ($event['student_status_tone'] ?? '') === 'idle',
                                    ]); ?>"><?php echo e($event['student_status']); ?></span>

                                    <?php if($event['can_vote'] ?? false): ?>
                                        <a
                                            href="<?php echo e($event['vote_url']); ?>"
                                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:opacity-90"
                                        >
                                            Vote Now
                                        </a>
                                    <?php elseif($event['can_view_results']): ?>
                                        <a
                                            href="<?php echo e($event['show_url']); ?>"
                                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:opacity-90"
                                        >
                                            View Results
                                        </a>
                                    <?php elseif(($event['student_status_tone'] ?? '') === 'review'): ?>
                                        <span class="inline-flex max-w-xs items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-2 text-sm font-medium text-amber-200">
                                            Under administrator review
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2 text-sm font-medium text-slate-400">
                                            Results Not Yet Available
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if(! $hasCompletedEvents): ?>
                    <div class="mt-8 flex flex-col items-center justify-center rounded-2xl border border-dashed border-cyan-500/15 bg-slate-900/40 px-6 py-10 text-center">
                        <div class="mb-3 text-2xl">🏆</div>
                        <p class="text-sm font-medium text-slate-300">No official results published yet.</p>
                        <p class="mt-1 text-sm text-slate-500">Check back once administrators publish completed event results.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/results/index.blade.php ENDPATH**/ ?>