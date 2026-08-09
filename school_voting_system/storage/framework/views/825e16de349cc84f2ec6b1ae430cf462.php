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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Talent Participants','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Talent Participants','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Participants',
            'description' => 'Review and manage contestant registrations. Only approved participants appear in student voting.',
            'showAction' => $canManage,
            'actionLabel' => 'Add Participant',
            'action' => route('admin.talent-participants.create', array_filter(['event' => $selectedEvent])),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php
            $tabs = [
                'all' => 'All',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'withdrawn' => 'Withdrawn',
                'disqualified' => 'Disqualified',
                'archived' => 'Archived',
            ];
        ?>

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-slate-800 pb-3">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $params = array_filter([
                        'status' => $key === 'all' ? null : $key,
                        'event' => $selectedEvent,
                        'q' => $search ?: null,
                    ]);
                ?>
                <a href="<?php echo e(route('admin.talent-participants.index', $params)); ?>"
                   class="rounded-full px-3 py-1.5 text-sm font-semibold transition <?php echo e($activeStatus === $key ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'); ?>">
                    <?php echo e($label); ?>

                    <span class="ml-1 rounded-full bg-slate-950/40 px-1.5 py-0.5 text-xs"><?php echo e($counts[$key] ?? 0); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <form method="GET" action="<?php echo e(route('admin.talent-participants.index')); ?>" class="mb-4 flex flex-wrap items-end gap-3">
            <?php if($activeStatus !== 'all'): ?>
                <input type="hidden" name="status" value="<?php echo e($activeStatus); ?>">
            <?php endif; ?>
            <div class="min-w-[12rem] flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" value="<?php echo e($search); ?>" placeholder="Name, Student ID, title…"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Competition</label>
                <select name="event" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="">All competitions</option>
                    <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($event->id); ?>" <?php if((string) $selectedEvent === (string) $event->id): echo 'selected'; endif; ?>><?php echo e($event->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Apply</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Photo</th>
                        <th class="px-4 py-3">Student ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Course</th>
                        <th class="px-4 py-3">Year</th>
                        <th class="px-4 py-3">Section</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Performance</th>
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="text-slate-300">
                            <td class="px-4 py-3">
                                <?php if($entry->photoUrl()): ?>
                                    <img src="<?php echo e($entry->photoUrl()); ?>" loading="lazy" alt="" class="h-10 w-10 rounded-lg object-cover">
                                <?php else: ?>
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/15 text-sm font-bold text-violet-200"><?php echo e(strtoupper(substr($entry->display_name, 0, 1))); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs font-mono text-slate-400"><?php echo e($entry->student_id_number ?: '—'); ?></td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-white"><?php echo e($entry->display_name); ?></p>
                                <p class="text-[11px] text-slate-500"><?php echo e($entry->talentEvent?->title); ?></p>
                            </td>
                            <td class="px-4 py-3 text-xs"><?php echo e($entry->course_strand ?: '—'); ?></td>
                            <td class="px-4 py-3 text-xs"><?php echo e($entry->grade_level ?: '—'); ?></td>
                            <td class="px-4 py-3 text-xs"><?php echo e($entry->section ?: '—'); ?></td>
                            <td class="px-4 py-3 text-xs"><?php echo e($entry->talentCategoryLabel() ?? '—'); ?></td>
                            <td class="max-w-[10rem] truncate px-4 py-3 text-xs"><?php echo e($entry->performance_title ?: '—'); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400"><?php echo e(optional($entry->submitted_at ?? $entry->created_at)->format('M d, Y')); ?></td>
                            <td class="px-4 py-3"><?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
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
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="<?php echo e(route('admin.talent-participants.show', $entry)); ?>" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                    <?php if($canManage): ?>
                                        <a href="<?php echo e(route('admin.talent-participants.edit', $entry)); ?>" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Edit</a>
                                        <?php if($entry->hasVideo()): ?>
                                            <?php if($entry->videoEmbedUrl()): ?>
                                                <a href="<?php echo e($entry->video_url); ?>" target="_blank" rel="noopener" class="rounded-lg border border-violet-500/40 px-2 py-1 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Watch</a>
                                            <?php elseif($entry->videoFileUrl()): ?>
                                                <a href="<?php echo e($entry->videoFileUrl()); ?>" target="_blank" class="rounded-lg border border-violet-500/40 px-2 py-1 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Watch</a>
                                                <?php if($entry->videoDownloadUrl()): ?>
                                                    <a href="<?php echo e($entry->videoDownloadUrl()); ?>" class="rounded-lg border border-cyan-500/40 px-2 py-1 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">Download</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if($entry->status !== \App\Models\TalentEventEntry::STATUS_APPROVED): ?>
                                            <form method="POST" action="<?php echo e(route('admin.talent.entries.approve', $entry)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="rounded-lg border border-emerald-500/40 px-2 py-1 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if($entry->status !== \App\Models\TalentEventEntry::STATUS_REJECTED): ?>
                                            <form method="POST" action="<?php echo e(route('admin.talent.entries.reject', $entry)); ?>" onsubmit="this.querySelector('[name=reason]').value = prompt('Reason for rejection:') || ''; return this.querySelector('[name=reason]').value !== '';">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="reason" value="">
                                                <button type="submit" class="rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('admin.talent-participants.destroy', $entry)); ?>" onsubmit="return confirm('Delete this participant?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-400 hover:bg-rose-500/10 hover:text-rose-200">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="px-4 py-10 text-center text-slate-400">
                                No participants found.
                                <?php if($canManage): ?>
                                    <a href="<?php echo e(route('admin.talent-participants.create')); ?>" class="ml-1 font-semibold text-violet-300 hover:text-violet-200">Add one →</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($participants->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/talent-participants/index.blade.php ENDPATH**/ ?>