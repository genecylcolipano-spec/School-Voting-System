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
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Voting</h1>
                    <p class="mt-1 text-sm text-slate-400">Open elections and voting history.</p>
                </div>
                <a href="<?php echo e(route('student.dashboard')); ?>" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    Back to dashboard
                </a>
            </div>

            <?php if(session('error')): ?>
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $elections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $election): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-white"><?php echo e($election->title); ?></h2>
                                <?php if($election->description): ?>
                                    <p class="mt-1 text-sm text-slate-300 line-clamp-2"><?php echo e($election->description); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e($election->status?->value ?? $election->status); ?></p>
                                <p class="mt-1 text-xs text-slate-400">
                                    <?php if($election->voting_starts_at): ?>
                                        Starts: <?php echo e($election->voting_starts_at->format('M d, Y g:i A')); ?>

                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-slate-400">
                                    <?php if($election->voting_ends_at): ?>
                                        Ends: <?php echo e($election->voting_ends_at->format('M d, Y g:i A')); ?>

                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php
                            $availability = $electionService->votingAvailability($election, $student);
                        ?>

                        <?php if($availability['state'] === 'open'): ?>
                            <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-200">🟢 <?php echo e($availability['title']); ?></p>
                                <p class="mt-1 text-sm text-emerald-100/80"><?php echo e($availability['message']); ?></p>
                                <?php if($availability['submessage']): ?>
                                    <p class="mt-1 text-sm text-emerald-100/70"><?php echo e($availability['submessage']); ?></p>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo e(route('student.voting.show', $election)); ?>" class="mt-4 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                Vote Now
                            </a>
                        <?php elseif($availability['state'] === 'voted'): ?>
                            <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-200">✅ <?php echo e($availability['title']); ?></p>
                                <p class="mt-1 text-sm text-emerald-100/80"><?php echo e($availability['message']); ?></p>
                                <?php if($availability['submessage']): ?>
                                    <p class="mt-1 text-sm text-emerald-100/70"><?php echo e($availability['submessage']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php elseif($availability['state'] === 'results_published'): ?>
                            <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-200">🏆 <?php echo e($availability['title']); ?></p>
                                <?php if($availability['message']): ?>
                                    <p class="mt-1 text-sm text-emerald-100/80"><?php echo e($availability['message']); ?></p>
                                <?php endif; ?>
                                <?php if($availability['submessage']): ?>
                                    <p class="mt-1 text-sm text-emerald-100/70"><?php echo e($availability['submessage']); ?></p>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo e(route('student.results.election.show', $election)); ?>" class="mt-4 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                View Results
                            </a>
                        <?php elseif($availability['state'] === 'not_started'): ?>
                            <p class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                                <?php echo e($availability['message']); ?>

                            </p>
                            <?php if($election->voting_starts_at): ?>
                                <p class="mt-2 text-xs text-slate-400">
                                    Opens <?php echo e($election->voting_starts_at->format('M d, Y g:i A')); ?>

                                </p>
                            <?php endif; ?>
                        <?php elseif($availability['state'] === 'under_review'): ?>
                            <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-amber-200">🟡 <?php echo e($availability['title']); ?></p>
                                <p class="mt-1 text-sm text-amber-100/80"><?php echo e($availability['message']); ?></p>
                                <?php if($availability['submessage']): ?>
                                    <p class="mt-1 text-sm text-amber-100/70"><?php echo e($availability['submessage']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php elseif($availability['message']): ?>
                            <p class="mt-4 rounded-xl border border-slate-700 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                                <?php echo e($availability['message']); ?>

                                <?php if($availability['submessage']): ?>
                                    <span class="mt-1 block text-slate-400"><?php echo e($availability['submessage']); ?></span>
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p class="mt-4 text-sm text-slate-400">Voting is not currently available.</p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-slate-300">
                        No elections found.
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6">
                <?php echo e($elections->links()); ?>

            </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/voting/index.blade.php ENDPATH**/ ?>