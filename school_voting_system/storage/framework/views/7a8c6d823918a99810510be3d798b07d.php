<section id="talent" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
    <?php if (isset($component)) { $__componentOriginal87b1b280c26c60b1db52189dd51eb1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-section-header','data' => ['title' => 'Talent Voting','description' => $canViewRealtimeTalentCounts ? 'Talent Competition, Debate, Quiz — live vote totals available to authorized administrators.' : 'Talent events in your scope. Live vote totals are restricted for your role.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Talent Voting','description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canViewRealtimeTalentCounts ? 'Talent Competition, Debate, Quiz — live vote totals available to authorized administrators.' : 'Talent events in your scope. Live vote totals are restricted for your role.')]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('admin.talent-competition.index')); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">View All</a>
            <?php if($canCreateTalentEvents): ?>
                <a href="<?php echo e(route('admin.talent-competition.create')); ?>" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create Event</a>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9)): ?>
<?php $attributes = $__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9; ?>
<?php unset($__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal87b1b280c26c60b1db52189dd51eb1e9)): ?>
<?php $component = $__componentOriginal87b1b280c26c60b1db52189dd51eb1e9; ?>
<?php unset($__componentOriginal87b1b280c26c60b1db52189dd51eb1e9); ?>
<?php endif; ?>

    <div class="mt-4 space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $talentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $talentEvent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $showTalentVotes = $canViewRealtimeTalentCounts || $talentEvent->votingHasClosed() || $talentEvent->currentStatusKey() === 'results_published';
                $entriesCount = (int) ($talentEvent->entries_count ?? $talentEvent->entries->count());
                $votesCount = (int) ($talentEvent->votes_count ?? 0);
            ?>
            <details class="overflow-hidden rounded-xl border border-slate-800 bg-slate-950/50" data-talent-accordion <?php echo e($index === 0 ? 'open' : ''); ?>>
                <summary class="cursor-pointer list-none px-3 py-3 sm:px-4 sm:py-3.5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <img src="<?php echo e($talentEvent->thumbnailUrl()); ?>" alt="" class="hidden h-10 w-14 shrink-0 rounded-lg object-cover object-center sm:block">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-semibold text-white sm:text-base"><?php echo e($talentEvent->title); ?></h4>
                                    <span class="rounded-full bg-indigo-500/20 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-300">
                                        <?php echo e($talentEvent->type?->label()); ?>

                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400 sm:text-sm">
                                    <?php echo e($talentEvent->event_date?->format('M d, Y · g:i A')); ?>

                                    <?php if($talentEvent->venue): ?> · <?php echo e($talentEvent->venue); ?> <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $talentEvent->currentStatusKey(),'label' => $talentEvent->displayStatusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentEvent->currentStatusKey()),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentEvent->displayStatusLabel())]); ?>
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
                            <p class="mt-1 text-xs text-slate-500">
                                <?php echo e($entriesCount); ?> <?php echo e(\Illuminate\Support\Str::plural('entry', $entriesCount)); ?>

                                <?php if($showTalentVotes): ?>
                                    · <?php echo e($votesCount); ?> <?php echo e(\Illuminate\Support\Str::plural('vote', $votesCount)); ?>

                                <?php else: ?>
                                    · votes hidden
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </summary>

                <div class="border-t border-slate-800 px-3 pb-3 pt-2.5 sm:px-4 sm:pb-3.5">
                    <?php if (isset($component)) { $__componentOriginaldc620424818b8a9f9fa858444666ff45 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc620424818b8a9f9fa858444666ff45 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.competition-card-banner','data' => ['event' => $talentEvent,'compact' => true,'class' => 'mb-2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('competition-card-banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentEvent),'compact' => true,'class' => 'mb-2.5']); ?>
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

                    <div class="mb-2.5 flex flex-wrap gap-2">
                        <a href="<?php echo e(route('admin.talent-competition.show', $talentEvent)); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Manage</a>

                        <?php if($canManageTalentVoting && ! in_array($talentEvent->currentStatusKey(), ['voting_open', 'results_published', 'voting_closed', 'archived'], true)): ?>
                            <form method="POST" action="<?php echo e(route('admin.talent.open-voting', $talentEvent)); ?>" data-confirm-sensitive data-confirm-title="Open student voting?" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Open Student Voting</button>
                            </form>
                        <?php endif; ?>

                        <?php if($canPublishTalentResults && in_array($talentEvent->currentStatusKey(), ['voting_open', 'voting_closed', 'voting_paused'], true)): ?>
                            <form method="POST" action="<?php echo e(route('admin.talent.publish-results', $talentEvent)); ?>" data-confirm-sensitive data-confirm-title="Publish results?" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Publish Results</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-left text-xs sm:text-sm">
                            <thead class="border-b border-slate-800 text-slate-400">
                                <tr>
                                    <th class="px-2 py-1.5 sm:px-3">Candidate</th>
                                    <th class="px-2 py-1.5 sm:px-3">Profile</th>
                                    <th class="px-2 py-1.5 sm:px-3">Performance</th>
                                    <th class="px-2 py-1.5 sm:px-3">Status</th>
                                    <th class="px-2 py-1.5 sm:px-3">Votes</th>
                                    <th class="px-2 py-1.5 sm:px-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php $__empty_2 = true; $__currentLoopData = $talentEvent->entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <?php $entryVotes = (int) ($entry->votes_count ?? 0); ?>
                                    <tr class="text-slate-200">
                                        <td class="px-2 py-2 sm:px-3">
                                            <span class="font-medium"><?php echo e($entry->display_name); ?></span>
                                            <?php if($entry->grade_level): ?>
                                                <p class="text-[10px] text-slate-500">Grade <?php echo e($entry->grade_level); ?>-<?php echo e($entry->section); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="max-w-[10rem] px-2 py-2 text-slate-400 sm:px-3"><?php echo e(Str::limit($entry->profile_summary, 60)); ?></td>
                                        <td class="max-w-xs px-2 py-2 text-slate-400 sm:px-3"><?php echo e(Str::limit($entry->performance_description, 80)); ?></td>
                                        <td class="px-2 py-2 sm:px-3"><?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $entry->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entry->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $attributes = $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $component = $__componentOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?></td>
                                        <td class="px-2 py-2 font-semibold text-violet-200 sm:px-3"><?php echo e($showTalentVotes ? $entryVotes : '—'); ?></td>
                                        <td class="px-2 py-2 sm:px-3">
                                            <?php if($entry->isPending() && $canApproveTalentEntries): ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <form method="POST" action="<?php echo e(route('admin.talent.entries.approve', $entry)); ?>" data-confirm-sensitive class="inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="rounded bg-emerald-600 px-2 py-1 text-[10px] font-semibold text-white">Approve</button>
                                                    </form>
                                                    <button type="button" data-entry-reject-toggle="<?php echo e($entry->id); ?>" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-semibold text-white">Reject</button>
                                                    <a href="<?php echo e(route('admin.talent-participants.show', $entry)); ?>" class="rounded border border-slate-700 px-2 py-1 text-[10px] font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                                </div>
                                                <form id="entry-reject-form-<?php echo e($entry->id); ?>" method="POST" action="<?php echo e(route('admin.talent.entries.reject', $entry)); ?>" data-confirm-sensitive class="mt-1.5 hidden space-y-1.5">
                                                    <?php echo csrf_field(); ?>
                                                    <textarea name="reason" required rows="2" placeholder="Rejection reason…" class="w-full rounded border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white"></textarea>
                                                    <button type="submit" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">Submit</button>
                                                </form>
                                            <?php elseif($entry->status === 'rejected' && $entry->review_reason): ?>
                                                <div class="space-y-1">
                                                    <p class="text-[10px] text-rose-300"><?php echo e(Str::limit($entry->review_reason, 40)); ?></p>
                                                    <a href="<?php echo e(route('admin.talent-participants.show', $entry)); ?>" class="text-[10px] font-semibold text-violet-300 hover:text-violet-200">View</a>
                                                </div>
                                            <?php else: ?>
                                                <a href="<?php echo e(route('admin.talent-participants.show', $entry)); ?>" class="text-xs font-semibold text-violet-300 hover:text-violet-200">View</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <tr><td colspan="6" class="px-2 py-4 text-center text-slate-400 sm:px-3">No entries yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="space-y-2 md:hidden">
                        <?php $__empty_2 = true; $__currentLoopData = $talentEvent->entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <?php $entryVotes = (int) ($entry->votes_count ?? 0); ?>
                            <article class="rounded-lg border border-slate-800 bg-slate-900/80 p-2.5 text-sm">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium text-white"><?php echo e($entry->display_name); ?></p>
                                        <p class="text-xs text-slate-400"><?php echo e(Str::limit($entry->performance_description, 80)); ?></p>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $entry->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entry->status)]); ?>
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
                                <p class="mt-1.5 text-xs text-violet-200">Votes: <?php echo e($showTalentVotes ? $entryVotes : '—'); ?></p>
                                <div class="mt-1.5 flex flex-wrap gap-2">
                                    <?php if($entry->isPending() && $canApproveTalentEntries): ?>
                                        <form method="POST" action="<?php echo e(route('admin.talent.entries.approve', $entry)); ?>" data-confirm-sensitive class="flex-1">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="w-full rounded bg-emerald-600 px-2 py-1.5 text-xs font-semibold text-white">Approve</button>
                                        </form>
                                        <button type="button" data-entry-reject-toggle="<?php echo e($entry->id); ?>" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">Reject</button>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('admin.talent-participants.show', $entry)); ?>" class="rounded border border-slate-700 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-slate-800">View</a>
                                </div>
                                <?php if($entry->isPending() && $canApproveTalentEntries): ?>
                                    <form id="entry-reject-form-<?php echo e($entry->id); ?>" method="POST" action="<?php echo e(route('admin.talent.entries.reject', $entry)); ?>" data-confirm-sensitive class="mt-1.5 hidden space-y-1.5">
                                        <?php echo csrf_field(); ?>
                                        <textarea name="reason" required rows="2" class="w-full rounded border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white"></textarea>
                                        <button type="submit" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">Submit</button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <p class="text-center text-sm text-slate-400">No entries yet.</p>
                        <?php endif; ?>
                    </div>

                    <?php if($talentEvent->currentStatusKey() === 'results_published' && $showTalentVotes): ?>
                        <?php $winner = $talentEvent->entries->sortByDesc('votes_count')->first(); ?>
                        <?php if($winner && (int) ($winner->votes_count ?? 0) > 0): ?>
                            <div class="mt-2.5 rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2 text-sm text-emerald-200">
                                Winner: <strong><?php echo e($winner->display_name); ?></strong> with <?php echo e((int) $winner->votes_count); ?> <?php echo e(\Illuminate\Support\Str::plural('vote', (int) $winner->votes_count)); ?>

                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </details>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400">No talent events in your assigned scope.</p>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_talent.blade.php ENDPATH**/ ?>