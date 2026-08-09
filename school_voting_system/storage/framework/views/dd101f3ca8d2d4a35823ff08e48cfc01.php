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
                    <h1 class="text-2xl font-bold text-white">Talent Competitions</h1>
                    <p class="mt-1 text-sm text-slate-400">View published events, explore candidate profiles, and cast secure votes.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="<?php echo e(route('student.talent-registration.index')); ?>" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                        Register your talent
                    </a>
                    <a href="<?php echo e(route('student.talent-registration.my-entries')); ?>" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                        My Entries
                    </a>
                    <a href="<?php echo e(route('student.dashboard')); ?>" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                        Back to dashboard
                    </a>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $badge = $event->student_phase_badge ?? $event->displayStatusLabel();
                        $cta = $event->student_phase_cta ?? 'View Event';
                        $href = $event->student_phase_href ?? route('student.talent-voting.show', $event);
                    ?>
                    <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
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
                        <div class="p-5">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-white"><?php echo e($event->title); ?></h2>
                                    <p class="mt-1 text-xs text-cyan-300"><?php echo e($event->type?->label()); ?></p>
                                    <?php if($event->description): ?>
                                        <p class="mt-2 text-sm text-slate-300"><?php echo e(\Illuminate\Support\Str::limit($event->description, 100)); ?></p>
                                    <?php endif; ?>
                                    <p class="mt-2 text-xs text-slate-500"><?php echo e($event->approved_entries_count ?? $event->entries_count ?? 0); ?> approved candidate(s)</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    <p><?php echo e($event->event_date?->format('M d, Y')); ?></p>
                                    <?php if($event->venue): ?>
                                        <p class="mt-1"><?php echo e($event->venue); ?></p>
                                    <?php endif; ?>
                                    <p class="mt-2 uppercase text-cyan-300"><?php echo e($badge); ?></p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <a href="<?php echo e($href); ?>" class="inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                    <?php echo e($cta); ?>

                                </a>
                                <?php if($event->student_has_voted ?? false): ?>
                                    <span class="text-xs text-emerald-300">You voted in this event</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-8 text-center md:col-span-2">
                        <p class="text-sm text-slate-400">No talent competitions have been published yet. Check back after your election admin approves candidates.</p>
                        <a href="<?php echo e(route('student.dashboard')); ?>" class="mt-4 inline-block text-sm text-cyan-300 hover:text-cyan-200">Return to dashboard</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php echo e($events->links()); ?>

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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/talent-voting/index.blade.php ENDPATH**/ ?>