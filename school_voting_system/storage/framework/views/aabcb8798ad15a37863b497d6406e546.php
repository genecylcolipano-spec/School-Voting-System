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
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Talent Competitions</h1>
                <p class="mt-1 text-sm text-slate-400">Browse competitions, review details, and register when the window is open.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?php echo e(route('student.talent-registration.my-entries')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20">
                    My Entries
                </a>
                <a href="<?php echo e(route('student.talent-voting.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                    Back to Talent Voting
                </a>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="mt-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $action = $flow->registrationAction($event, auth()->user());
                    $alreadySubmitted = $myEntries->has($event->id);
                ?>
                <article class="flex flex-col overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70 transition hover:border-cyan-500/40">
                    <?php if (isset($component)) { $__componentOriginaldc620424818b8a9f9fa858444666ff45 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc620424818b8a9f9fa858444666ff45 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.competition-card-banner','data' => ['event' => $event]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('competition-card-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event)]); ?>
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
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-200"><?php echo e($event->talent_category?->label() ?? 'Talent'); ?></span>
                            <span class="text-xs text-slate-500">
                                <?php if($event->isRegistrationOpen()): ?>
                                    Registration Open
                                <?php else: ?>
                                    <?php echo e($event->displayStatusLabel()); ?>

                                <?php endif; ?>
                            </span>
                        </div>
                        <h2 class="mt-3 text-lg font-semibold text-white"><?php echo e($event->title); ?></h2>
                        <?php if($event->description): ?>
                            <p class="mt-2 text-sm text-slate-400 line-clamp-3"><?php echo e($event->description); ?></p>
                        <?php endif; ?>
                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span>Deadline: <?php echo e(optional($event->submission_deadline ?? $event->registration_ends_at)->format('M d, Y g:i A') ?? '—'); ?></span>
                            <span>Participants: <?php echo e($event->active_entries_count ?? 0); ?><?php echo e($event->max_contestants ? ' / '.$event->max_contestants : ''); ?></span>
                        </div>
                        <div class="mt-auto flex flex-wrap gap-3 pt-4">
                            <a href="<?php echo e(route('student.talent-registration.show', $event)); ?>"
                                class="inline-flex flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                                View
                            </a>
                            <?php if($alreadySubmitted): ?>
                                <a href="<?php echo e(route('student.talent-registration.entry.show', $myEntries->get($event->id))); ?>"
                                    class="inline-flex items-center justify-center rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20">
                                    My Entry
                                </a>
                            <?php elseif($action['can_register']): ?>
                                <a href="<?php echo e($action['href']); ?>"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                                    Register Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="sm:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/70 p-8 text-center">
                    <p class="text-sm text-slate-400">No talent competitions are available right now. Please check back later.</p>
                </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/talent-registration/index.blade.php ENDPATH**/ ?>