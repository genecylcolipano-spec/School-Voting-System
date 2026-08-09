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
    <?php
        $statusLabel = $talentEvent->displayStatusLabel();
        $registrationOpen = $talentEvent->isRegistrationOpen();
        $competitionCategory = $talentEvent->talent_category?->label() ?? $talentEvent->type?->label() ?? 'Talent';
        $ctaClasses = $action['disabled']
            ? 'cursor-not-allowed rounded-xl border border-slate-700 bg-slate-800/80 px-5 py-2.5 text-sm font-semibold text-slate-400'
            : 'rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300';
        $secondaryCtaClasses = 'rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800';
    ?>

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <a href="<?php echo e(route('student.talent-registration.index')); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">&larr; Back to Competitions</a>
                <a href="<?php echo e(route('student.talent-registration.my-entries')); ?>" class="text-sm text-slate-300 hover:text-white">My Entries</a>
            </div>

            <?php if(session('success')): ?>
                <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            
            <section class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/80" aria-label="Competition hero">
                <div class="relative aspect-[21/9] h-[160px] max-h-[180px] w-full overflow-hidden sm:h-[200px] sm:max-h-[220px] lg:h-[240px] lg:max-h-[260px]">
                    <?php if (isset($component)) { $__componentOriginal320417d1f3b2a17423bd326bb6c46b6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal320417d1f3b2a17423bd326bb6c46b6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.competition-detail-banner','data' => ['event' => $talentEvent,'bare' => true,'showWarning' => false,'class' => 'absolute inset-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('competition-detail-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentEvent),'bare' => true,'show-warning' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'absolute inset-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal320417d1f3b2a17423bd326bb6c46b6c)): ?>
<?php $attributes = $__attributesOriginal320417d1f3b2a17423bd326bb6c46b6c; ?>
<?php unset($__attributesOriginal320417d1f3b2a17423bd326bb6c46b6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal320417d1f3b2a17423bd326bb6c46b6c)): ?>
<?php $component = $__componentOriginal320417d1f3b2a17423bd326bb6c46b6c; ?>
<?php unset($__componentOriginal320417d1f3b2a17423bd326bb6c46b6c); ?>
<?php endif; ?>
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-slate-950/25"></div>
                    <div class="absolute inset-0 z-[1] flex items-end p-4 sm:p-6">
                        <div class="w-full">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-cyan-400/30 bg-cyan-500/15 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cyan-100"><?php echo e($competitionCategory); ?></span>
                                <span class="rounded-full border border-slate-500/40 bg-slate-950/55 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-100"><?php echo e($statusLabel); ?></span>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                                    'bg-emerald-500/15 text-emerald-200' => $registrationOpen,
                                    'bg-slate-800/80 text-slate-300' => ! $registrationOpen,
                                ]); ?>"><?php echo e($registrationOpen ? 'Registration Open' : 'Registration Closed'); ?></span>
                            </div>
                            <h1 class="mt-2 text-xl font-bold leading-tight text-white sm:text-2xl lg:text-3xl"><?php echo e($talentEvent->title); ?></h1>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-800 px-4 py-4 sm:px-6">
                    <div class="text-sm text-slate-400">
                        <?php if($action['state'] === 'already_registered'): ?>
                            You are registered for this competition.
                        <?php elseif($action['can_register']): ?>
                            Review the details below, then register your performance entry.
                        <?php else: ?>
                            Registration is currently unavailable: <?php echo e($action['label']); ?>.
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <?php if($action['href'] && ! $action['disabled']): ?>
                            <a href="<?php echo e($action['href']); ?>" class="<?php echo e($ctaClasses); ?>"><?php echo e($action['label']); ?></a>
                        <?php else: ?>
                            <span class="<?php echo e($ctaClasses); ?>"><?php echo e($action['label']); ?></span>
                        <?php endif; ?>
                        <a href="<?php echo e(route('student.talent-registration.index')); ?>" class="<?php echo e($secondaryCtaClasses); ?>">Return to List</a>
                    </div>
                </div>
            </section>

            <div class="mt-5 grid gap-4 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 sm:p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Competition Overview</h2>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-slate-300"><?php echo e($talentEvent->description ?: 'No description provided.'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Theme / Category</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e($competitionCategory); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Organizer</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e($talentEvent->organizer ?: ($talentEvent->creator?->name ?? 'School Administration')); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Venue</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e($talentEvent->venue ?: 'Online Competition'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Competition Type</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e($talentEvent->type?->label() ?? 'Talent'); ?></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 sm:p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Competition Schedule</h2>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registration Opens</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e(optional($talentEvent->registration_starts_at)->format('M d, Y g:i A') ?? '—'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registration Closes</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e(optional($talentEvent->registration_ends_at)->format('M d, Y g:i A') ?? '—'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Competition Date</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e(optional($talentEvent->event_date)->format('M d, Y g:i A') ?? 'TBA'); ?></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 sm:p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Eligibility</h2>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-300">
                            <li>Open to authenticated students of this institution.</li>
                            <li>Registration method: <?php echo e($talentEvent->registrationMethodLabel()); ?>.</li>
                            <li>One performance entry per student for this competition.</li>
                            <?php if($talentEvent->max_contestants): ?>
                                <li>Maximum of <?php echo e($talentEvent->max_contestants); ?> participants.</li>
                            <?php endif; ?>
                        </ul>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 sm:p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Competition Mechanics</h2>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-300">
                            <li>Submission method: <?php echo e($talentEvent->submissionMethodLabel()); ?>.</li>
                            <li>Voting method: <?php echo e($talentEvent->voting_method?->label() ?? 'Configured by organizers'); ?>.</li>
                            <li>Ranking method: <?php echo e($talentEvent->rankingMethodLabel()); ?>.</li>
                            <li>Number of winners: <?php echo e($talentEvent->number_of_winners ?? '—'); ?>.</li>
                            <?php if($talentEvent->max_performance_duration_minutes): ?>
                                <li>Max performance duration: <?php echo e($talentEvent->max_performance_duration_minutes); ?> minutes.</li>
                            <?php endif; ?>
                        </ul>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 sm:p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Judging Criteria</h2>
                        <?php if($talentEvent->judgingCriteria->isNotEmpty()): ?>
                            <ul class="mt-3 divide-y divide-slate-800 rounded-xl border border-slate-800">
                                <?php $__currentLoopData = $talentEvent->judgingCriteria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                                        <span class="text-slate-200"><?php echo e($criterion->name); ?></span>
                                        <span class="font-semibold text-cyan-200"><?php echo e($criterion->max_points); ?> pts</span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php else: ?>
                            <p class="mt-3 text-sm text-slate-400">Judging criteria will be announced by the organizers.</p>
                        <?php endif; ?>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 sm:p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Submission &amp; Video Requirements</h2>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submission Deadline</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e(optional($talentEvent->submission_deadline)->format('M d, Y g:i A') ?? optional($talentEvent->registration_ends_at)->format('M d, Y g:i A') ?? '—'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Max Video Duration</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e($talentEvent->maxVideoDurationLabel()); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Max Upload Size</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e($talentEvent->maxUploadSizeMb()); ?> MB</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Allowed Formats</dt>
                                <dd class="mt-1 text-sm text-white"><?php echo e(implode(', ', $talentEvent->acceptedVideoFormatsArray())); ?></dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <aside class="space-y-4">
                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Participation</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Maximum Participants</dt>
                                <dd class="font-semibold text-white"><?php echo e($talentEvent->contestantLimitLabel()); ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Registered</dt>
                                <dd class="font-semibold text-white"><?php echo e($registeredCount); ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Remaining Slots</dt>
                                <dd class="font-semibold text-white"><?php echo e($remainingSlots === null ? 'Unlimited' : $remainingSlots); ?></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Organizer Contact</h2>
                        <p class="mt-3 text-sm text-slate-300"><?php echo e($talentEvent->organizer ?: 'School Administration'); ?></p>
                        <?php if($talentEvent->creator?->email): ?>
                            <p class="mt-2 text-sm text-cyan-300"><?php echo e($talentEvent->creator->email); ?></p>
                        <?php else: ?>
                            <p class="mt-2 text-xs text-slate-500">Contact your school administrator for questions about this competition.</p>
                        <?php endif; ?>
                    </section>

                    <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-cyan-200">Quick FAQ</h2>
                        <div class="mt-3 space-y-3 text-sm text-slate-300">
                            <div>
                                <p class="font-semibold text-white">Can I edit after submitting?</p>
                                <p class="mt-1 text-slate-400">No. Entries cannot be edited unless organizers reopen registration.</p>
                            </div>
                            <div>
                                <p class="font-semibold text-white">When will I know the result of review?</p>
                                <p class="mt-1 text-slate-400">You will receive a portal notification once your entry is approved or rejected.</p>
                            </div>
                        </div>
                    </section>
                </aside>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/talent-registration/show.blade.php ENDPATH**/ ?>