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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Competition Management','user' => $user,'notificationsCount' => $notificationsCount,'assignedRole' => $assignedRole]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Competition Management','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'assigned-role' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($assignedRole)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Competition Management',
            'description' => 'Create, configure, publish, and archive talent competitions.',
            'showAction' => $canManageTalentEvents,
            'actionLabel' => 'Create Competition',
            'action' => route('admin.talent-competition.create'),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="GET" action="<?php echo e(route('admin.talent-competition.index')); ?>" class="mb-4 flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" value="<?php echo e($filters['q']); ?>" placeholder="Title, code, venue…"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="">All</option>
                    <?php $__currentLoopData = ['draft' => 'Draft', 'registration_open' => 'Registration Open', 'registration_closed' => 'Registration Closed', 'voting_open' => 'Voting Open', 'voting_closed' => 'Voting Closed', 'results_published' => 'Results Published', 'archived' => 'Archived']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if($filters['status'] === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
                <select name="category" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="">All</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->value); ?>" <?php if($filters['category'] === $category->value): echo 'selected'; endif; ?>><?php echo e($category->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort</label>
                <select name="sort" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="newest" <?php if($filters['sort'] === 'newest'): echo 'selected'; endif; ?>>Newest</option>
                    <option value="oldest" <?php if($filters['sort'] === 'oldest'): echo 'selected'; endif; ?>>Oldest</option>
                    <option value="title" <?php if($filters['sort'] === 'title'): echo 'selected'; endif; ?>>Title</option>
                    <option value="participants" <?php if($filters['sort'] === 'participants'): echo 'selected'; endif; ?>>Participants</option>
                    <option value="votes" <?php if($filters['sort'] === 'votes'): echo 'selected'; endif; ?>>Votes</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Apply</button>
            <?php if($filters['q'] || $filters['status'] || $filters['category'] || $filters['sort'] !== 'newest'): ?>
                <a href="<?php echo e(route('admin.talent-competition.index')); ?>" class="text-sm font-semibold text-slate-400 hover:text-white">Clear</a>
            <?php endif; ?>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Banner</th>
                        <th class="px-4 py-3">Competition</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Registration</th>
                        <th class="px-4 py-3">Voting</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Participants</th>
                        <th class="px-4 py-3 text-center">Votes</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $talentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="text-slate-300">
                            <td class="px-4 py-3">
                                <img src="<?php echo e($event->thumbnailUrl()); ?>" alt="" class="h-12 w-16 rounded-lg object-cover" loading="lazy">
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-white"><?php echo e($event->title); ?></p>
                                <?php if($event->competition_code): ?>
                                    <p class="text-xs text-slate-500"><?php echo e($event->competition_code); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs"><?php echo e($event->talent_category?->label() ?? '—'); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400"><?php echo e($event->registrationWindowLabel()); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400"><?php echo e($event->votingWindowLabel()); ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] font-semibold text-violet-200"><?php echo e($event->displayStatusLabel()); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-white"><?php echo e(number_format($event->entries_count)); ?></td>
                            <td class="px-4 py-3 text-center font-semibold text-white"><?php echo e($canViewRealtimeTalentCounts || ! $event->isAcceptingVotes() ? number_format($event->votes_count) : '—'); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400"><?php echo e(optional($event->created_at)->format('M d, Y')); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="<?php echo e(route('admin.talent-competition.show', $event)); ?>" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                    <?php if($canManageTalentEvents): ?>
                                        <a href="<?php echo e(route('admin.talent-competition.edit', $event)); ?>" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Edit</a>
                                        <form method="POST" action="<?php echo e(route('admin.talent-competition.duplicate', $event)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Duplicate</button>
                                        </form>
                                        <?php if (! ($event->published_to_students)): ?>
                                            <form method="POST" action="<?php echo e(route('admin.talent-competition.publish', $event)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="rounded-lg border border-emerald-500/40 px-2 py-1 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">Publish</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (! ($event->isArchived())): ?>
                                            <form method="POST" action="<?php echo e(route('admin.talent-competition.archive', $event)); ?>" onsubmit="return confirm('Archive this competition?');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="rounded-lg border border-amber-500/40 px-2 py-1 text-xs font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php
                                            $talentDeps = collect([
                                                $event->entries_count > 0 ? 'participants' : null,
                                                $event->votes_count > 0 ? 'votes' : null,
                                            ])->filter()->values();
                                            $talentWarning = $talentDeps->isNotEmpty()
                                                ? 'This talent competition contains related data: '.$talentDeps->join(', ').'. Related judges, scores, and videos may also be linked.'
                                                : null;
                                        ?>
                                        <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.talent-competition.destroy', $event),'warning' => $talentWarning,'buttonClass' => 'rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.talent-competition.destroy', $event)),'warning' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentWarning),'button-class' => 'rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10']); ?>
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
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-400">
                                No competitions found.
                                <?php if($canManageTalentEvents): ?>
                                    <a href="<?php echo e(route('admin.talent-competition.create')); ?>" class="ml-1 font-semibold text-violet-300 hover:text-violet-200">Create one →</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($talentEvents->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/talent-competition/index.blade.php ENDPATH**/ ?>