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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => 'Dashboard','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <section class="overflow-hidden rounded-2xl border border-teal-500/20 bg-gradient-to-br from-teal-900/70 via-slate-900 to-emerald-900/30 p-6 sm:p-8">
            <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-teal-200">Faculty Portal</span>
            <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl"><?php echo e($user->name); ?></h2>
            <p class="mt-3 max-w-2xl text-slate-300">
                Review assigned competitions, evaluate participant performances, submit scores, and stay informed with school announcements and school events.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a
                    href="<?php echo e(route('faculty.judging.index')); ?>"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                    aria-label="View assigned competitions"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 21h8m-4-4v4m-6.5-8.5A5.5 5.5 0 015 6.5V5a2 2 0 012-2h10a2 2 0 012 2v1.5a5.5 5.5 0 01-.5 2.5M6.5 12.5h11"/>
                    </svg>
                    View Assigned Competitions
                </a>

                <a
                    href="<?php echo e(route('faculty.events.index')); ?>"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-teal-400/40 bg-transparent px-5 py-2.5 text-sm font-semibold text-teal-100 transition hover:border-teal-300/60 hover:bg-teal-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                    aria-label="View school events"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    View School Events
                </a>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="<?php echo e(route('faculty.elections.index')); ?>" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open elections</p>
                <p class="mt-2 text-3xl font-bold text-white"><?php echo e($openElectionsCount); ?></p>
                <p class="mt-1 text-sm text-teal-300">View only →</p>
            </a>
            <a href="<?php echo e(route('faculty.events.index')); ?>" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Upcoming events</p>
                <p class="mt-2 text-3xl font-bold text-white"><?php echo e($upcomingEventsCount); ?></p>
                <p class="mt-1 text-sm text-teal-300">View only →</p>
            </a>
            <a href="<?php echo e(route('faculty.judging.index')); ?>" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned competitions</p>
                <p class="mt-2 text-3xl font-bold text-white"><?php echo e($openTalentCount); ?></p>
                <p class="mt-1 text-sm text-teal-300">Open judging →</p>
            </a>
        </div>

        <section class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-amber-100">Assigned Competitions</h3>
                    <p class="mt-1 text-sm text-amber-100/80">Competitions where the Super Administrator assigned you as a judge.</p>
                </div>
                <a href="<?php echo e(route('faculty.judging.index')); ?>" class="text-sm font-semibold text-amber-200 hover:text-amber-100">View all →</a>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <?php $__empty_1 = true; $__currentLoopData = $assignedCompetitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $competition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $assignment = $assignments[$competition->id] ?? null;
                        $p = $progress[$competition->id] ?? ['approved' => 0, 'submitted' => 0, 'percent' => 0, 'judging_status' => 'Not Started'];
                    ?>
                    <article class="rounded-xl border border-amber-500/15 bg-slate-950/40 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h4 class="font-semibold text-white"><?php echo e($competition->title); ?></h4>
                                <p class="mt-1 text-xs text-slate-400">
                                    <?php echo e($competition->talent_category?->label() ?? $competition->type?->label() ?? 'Talent'); ?>

                                    · <?php echo e($competition->displayStatusLabel()); ?>

                                </p>
                            </div>
                            <span class="rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-200">
                                <?php echo e($assignment?->roleLabel() ?? 'Judge'); ?>

                            </span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                            <div>
                                <dt class="uppercase tracking-wide text-slate-500">Date</dt>
                                <dd class="mt-0.5 text-slate-200"><?php echo e(optional($competition->event_date)->format('M d, Y') ?? 'TBA'); ?></dd>
                            </div>
                            <div>
                                <dt class="uppercase tracking-wide text-slate-500">Participants</dt>
                                <dd class="mt-0.5 text-slate-200"><?php echo e($competition->approved_entries_count ?? 0); ?></dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="uppercase tracking-wide text-slate-500">Judging Status</dt>
                                <dd class="mt-0.5 text-amber-100"><?php echo e($p['judging_status']); ?> · <?php echo e($p['percent']); ?>% complete</dd>
                            </div>
                        </dl>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="<?php echo e(route('faculty.judging.show', $competition)); ?>" class="rounded-lg bg-gradient-to-r from-teal-500 to-emerald-400 px-3 py-1.5 text-xs font-semibold text-slate-950">Open Judging</a>
                            <a href="<?php echo e(route('faculty.judging.show', $competition)); ?>" class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800">View Competition</a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="lg:col-span-2 rounded-xl border border-dashed border-amber-500/20 px-4 py-8 text-center text-sm text-amber-100/70">
                        No competitions assigned yet. The Super Administrator must assign you as a judge before competitions appear here.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-white">Announcements</h3>
                    <p class="mt-1 text-sm text-slate-400">Messages targeted to faculty and all users.</p>
                </div>
                <a href="<?php echo e(route('faculty.announcements.index')); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">View all →</a>
            </div>

            <ul class="mt-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li>
                        <a href="<?php echo e(route('faculty.announcements.show', $announcement)); ?>" class="block rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 transition hover:border-teal-500/30">
                            <p class="font-medium text-white"><?php echo e($announcement->title); ?></p>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-400"><?php echo e($announcement->summary ?? strip_tags((string) $announcement->body)); ?></p>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">
                        No announcements for faculty right now.
                    </li>
                <?php endif; ?>
            </ul>
        </section>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/dashboard.blade.php ENDPATH**/ ?>