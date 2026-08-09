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
    <?php if (isset($component)) { $__componentOriginal57da683fe32826f08aa9f05c3342a7e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $talentEvent->title,'user' => $user,'notificationsCount' => $notificationsCount,'assignedRole' => $assignedRole]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentEvent->title),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'assigned-role' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($assignedRole)]); ?>
        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        
        <div class="mb-6">
            <div class="relative aspect-video overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-950/90">
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
                <div class="absolute inset-0 z-[1] bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 z-[2] p-5 sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300"><?php echo e($talentEvent->talent_category?->label() ?? 'Talent Competition'); ?></p>
                            <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl"><?php echo e($talentEvent->title); ?></h1>
                            <?php if($talentEvent->competition_code): ?>
                                <p class="mt-1 text-sm text-slate-300">Code: <?php echo e($talentEvent->competition_code); ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="rounded-full bg-violet-500/20 px-3 py-1 text-xs font-bold text-violet-100"><?php echo e($talentEvent->displayStatusLabel()); ?></span>
                    </div>
                </div>
            </div>
            <?php if($talentEvent->shouldWarnNonLandscapeBanner()): ?>
                <p class="mt-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-center text-xs font-medium text-amber-100 sm:text-sm">
                    This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
                </p>
            <?php endif; ?>
        </div>

        <?php if (isset($component)) { $__componentOriginal97baff9d35efe1eef17429e974d232dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal97baff9d35efe1eef17429e974d232dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.competition-poster','data' => ['event' => $talentEvent,'download' => true,'class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('competition-poster'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentEvent),'download' => true,'class' => 'mb-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal97baff9d35efe1eef17429e974d232dd)): ?>
<?php $attributes = $__attributesOriginal97baff9d35efe1eef17429e974d232dd; ?>
<?php unset($__attributesOriginal97baff9d35efe1eef17429e974d232dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal97baff9d35efe1eef17429e974d232dd)): ?>
<?php $component = $__componentOriginal97baff9d35efe1eef17429e974d232dd; ?>
<?php unset($__componentOriginal97baff9d35efe1eef17429e974d232dd); ?>
<?php endif; ?>

        
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-7">
            <?php $__currentLoopData = [
                ['Total Participants', $talentEvent->entries_count],
                ['Pending', $talentEvent->pending_entries_count],
                ['Approved', $talentEvent->approved_entries_count],
                ['Rejected', $talentEvent->rejected_entries_count],
                ['Votes Cast', $talentEvent->votes_count],
                ['Winners', $talentEvent->number_of_winners ?? 3],
                ['Status', $talentEvent->displayStatusLabel()],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
                    <p class="mt-1 truncate text-xl font-bold text-white"><?php echo e(is_numeric($value) ? number_format($value) : $value); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <?php if($canManageTalentEvents): ?>
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="<?php echo e(route('admin.talent-competition.edit', $talentEvent)); ?>" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Edit Competition</a>
                <a href="<?php echo e(route('admin.talent-competition.settings', $talentEvent)); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Settings</a>
                <a href="<?php echo e(route('admin.talent-competition.judges', $talentEvent)); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Judges</a>
                <a href="<?php echo e(route('admin.talent-participants.index', ['event' => $talentEvent->id])); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Participants</a>
                <a href="<?php echo e(route('admin.live.talent')); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Live Monitoring</a>
                <a href="<?php echo e(route('admin.results.talent.show', $talentEvent)); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Results →</a>

                <form method="POST" action="<?php echo e(route('admin.talent-competition.open-registration', $talentEvent)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Open Registration</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.talent-competition.close-registration', $talentEvent)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Close Registration</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.talent.open-voting', $talentEvent)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Open Voting</button>
                </form>
                <form method="POST" action="<?php echo e(route('admin.talent-competition.close-voting', $talentEvent)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Close Voting</button>
                </form>
                <?php if($canPublishResults): ?>
                    <form method="POST" action="<?php echo e(route('admin.talent.publish-results', $talentEvent)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-cyan-500/40 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">Publish Results</button>
                    </form>
                <?php endif; ?>
                <?php if (! ($talentEvent->published_to_students)): ?>
                    <form method="POST" action="<?php echo e(route('admin.talent-competition.publish', $talentEvent)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Publish Competition</button>
                    </form>
                <?php endif; ?>
                <?php if (! ($talentEvent->isArchived())): ?>
                    <form method="POST" action="<?php echo e(route('admin.talent-competition.archive', $talentEvent)); ?>" onsubmit="return confirm('Archive this competition?');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Archive</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Competition Information</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Organizer</dt><dd class="text-slate-200"><?php echo e($talentEvent->organizer ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Venue</dt><dd class="text-slate-200"><?php echo e($talentEvent->venue); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Event Date</dt><dd class="text-slate-200"><?php echo e(optional($talentEvent->event_date)->format('M d, Y g:i A')); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Type</dt><dd class="text-slate-200"><?php echo e($talentEvent->type?->label()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Visible to Students</dt><dd class="text-slate-200"><?php echo e($talentEvent->published_to_students ? 'Yes' : 'No'); ?></dd></div>
                </dl>
                <?php if($talentEvent->description): ?>
                    <p class="mt-4 text-sm text-slate-400"><?php echo e($talentEvent->description); ?></p>
                <?php endif; ?>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Schedule</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Registration</dt><dd class="text-right text-slate-200"><?php echo e($talentEvent->registrationWindowLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Submission Deadline</dt><dd class="text-slate-200"><?php echo e(optional($talentEvent->submission_deadline)->format('M d, Y g:i A') ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting</dt><dd class="text-right text-slate-200"><?php echo e($talentEvent->votingWindowLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Results Target</dt><dd class="text-slate-200"><?php echo e(optional($talentEvent->results_publish_at)->format('M d, Y g:i A') ?: '—'); ?></dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Rules</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Participants</dt><dd class="text-slate-200"><?php echo e($talentEvent->contestantLimitLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Performance Duration</dt><dd class="text-slate-200"><?php echo e($talentEvent->performanceDurationLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Video Duration</dt><dd class="text-slate-200"><?php echo e($talentEvent->maxVideoDurationLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Upload Size</dt><dd class="text-slate-200"><?php echo e($talentEvent->maxUploadSizeMb()); ?> MB</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Formats</dt><dd class="text-slate-200">.<?php echo e(implode(', .', $talentEvent->acceptedVideoFormatsArray())); ?></dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Registration & Voting</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Registration Method</dt><dd class="text-slate-200"><?php echo e($talentEvent->registrationMethodLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Submission Method</dt><dd class="text-slate-200"><?php echo e($talentEvent->submissionMethodLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting Method</dt><dd class="text-right text-slate-200"><?php echo e($talentEvent->votingMethodLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Ranking Method</dt><dd class="text-slate-200"><?php echo e($talentEvent->rankingMethodLabel()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Number of Winners</dt><dd class="text-slate-200"><?php echo e($talentEvent->number_of_winners ?? 3); ?></dd></div>
                </dl>
            </section>
        </div>

        <div class="mt-6">
            <a href="<?php echo e(route('admin.talent-competition.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to Competition Management</a>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $attributes = $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $component = $__componentOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/talent-competition/show.blade.php ENDPATH**/ ?>