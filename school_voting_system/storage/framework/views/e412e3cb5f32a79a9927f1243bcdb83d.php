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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $election->title,'user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($election->title),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300">Student Election</p>
                <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl"><?php echo e($election->title); ?></h1>
                <p class="mt-1 text-sm text-slate-400">Created by <?php echo e($election->creator?->name ?? '—'); ?></p>
            </div>
            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $election->status?->value ?? 'draft','label' => $election->status?->label()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($election->status?->value ?? 'draft'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($election->status?->label())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $attributes = $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $component = $__componentOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?php $__currentLoopData = [
                ['Positions', $election->categories_count],
                ['Candidates', $election->candidates_count],
                ['Campaigns', $election->partylists_count],
                ['Votes', $election->votes_count],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
                    <p class="mt-1 text-xl font-bold text-white"><?php echo e(number_format($value)); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php
            $dependencyParts = collect([
                $election->categories_count > 0 ? 'positions' : null,
                $election->candidates_count > 0 ? 'candidates' : null,
                $election->partylists_count > 0 ? 'partylists' : null,
                $election->votes_count > 0 ? 'votes' : null,
            ])->filter()->values();
            $warningParts = collect();
            if ($election->results_locked) {
                $warningParts->push('Official results for this election are locked.');
            }
            if ($dependencyParts->isNotEmpty()) {
                $warningParts->push('This election contains related data: '.$dependencyParts->join(', ').'.');
            }
            $deleteWarning = $warningParts->isNotEmpty() ? $warningParts->implode(' ') : null;
        ?>

        <div class="mb-6 flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $election)): ?>
                <a href="<?php echo e(route('admin.elections.edit', $election)); ?>" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Edit Election</a>
                <?php if (! ($election->status === \App\Enums\ElectionStatus::Archived)): ?>
                    <?php if($election->is_paused): ?>
                        <form method="POST" action="<?php echo e(route('admin.elections.open-voting', $election)); ?>" data-confirm-sensitive data-confirm-title="Resume student voting?">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Resume Voting</button>
                        </form>
                    <?php elseif(! $election->isAcceptingVotes()): ?>
                        <form method="POST" action="<?php echo e(route('admin.elections.open-voting', $election)); ?>" data-confirm-sensitive data-confirm-title="Open student voting now?">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Open Voting</button>
                        </form>
                    <?php endif; ?>
                    <?php if($election->status === \App\Enums\ElectionStatus::Active): ?>
                        <form method="POST" action="<?php echo e(route('admin.elections.close-voting', $election)); ?>" data-confirm-sensitive data-confirm-title="Close student voting?">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Close Voting</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($canCreateElections): ?>
                <form method="POST" action="<?php echo e(route('admin.elections.duplicate', $election)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Duplicate</button>
                </form>
            <?php endif; ?>
            <a href="<?php echo e(route('admin.live.election')); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Live Monitoring</a>
            <a href="<?php echo e(route('admin.results.election.show', $election)); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Results →</a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $election)): ?>
                <?php if (! ($election->status === \App\Enums\ElectionStatus::Archived)): ?>
                    <form method="POST" action="<?php echo e(route('admin.elections.archive', $election)); ?>" onsubmit="return confirm('Archive this election? Students will no longer see it as an open vote.');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $election)): ?>
                <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.elections.destroy', $election),'warning' => $deleteWarning,'buttonClass' => 'rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.elections.destroy', $election)),'warning' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deleteWarning),'button-class' => 'rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57)): ?>
<?php $attributes = $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57; ?>
<?php unset($__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal469a4ba3cbb96eb4bd9792641d671d57)): ?>
<?php $component = $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57; ?>
<?php unset($__componentOriginal469a4ba3cbb96eb4bd9792641d671d57); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Election Information</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Status</dt><dd class="text-slate-200"><?php echo e($election->status?->label()); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting starts</dt><dd class="text-slate-200"><?php echo e(optional($election->voting_starts_at)->format('M d, Y g:i A') ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting ends</dt><dd class="text-slate-200"><?php echo e(optional($election->voting_ends_at)->format('M d, Y g:i A') ?: '—'); ?></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Results published</dt><dd class="text-slate-200"><?php echo e($election->public_results_published ? 'Yes' : 'No'); ?></dd></div>
                </dl>
                <?php if($election->description): ?>
                    <p class="mt-4 text-sm text-slate-400"><?php echo e($election->description); ?></p>
                <?php endif; ?>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Campaigns</h2>
                <ul class="mt-4 space-y-2 text-sm text-slate-300">
                    <?php $__empty_1 = true; $__currentLoopData = $election->partylists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li><?php echo e($campaign->name); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="text-slate-500">No campaigns attached.</li>
                    <?php endif; ?>
                </ul>
            </section>
        </div>

        <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <h2 class="text-base font-semibold text-white">Positions and candidates</h2>
            <div class="mt-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $election->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <p class="font-semibold text-white"><?php echo e($category->name); ?></p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-300">
                            <?php $__empty_2 = true; $__currentLoopData = $category->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <li><?php echo e($candidate->display_name); ?><?php if($candidate->party_or_group): ?> <span class="text-slate-500">· <?php echo e($candidate->party_or_group); ?></span><?php endif; ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <li class="text-slate-500">No candidates yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No positions yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="mt-6">
            <a href="<?php echo e(route('admin.elections.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to Elections</a>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/elections/show.blade.php ENDPATH**/ ?>