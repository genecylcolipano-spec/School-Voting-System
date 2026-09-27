<?php
    $previewEvents = collect()
        ->merge($talentEvents->map(fn ($event) => ['kind' => 'talent', 'event' => $event]))
        ->merge($schoolEvents->map(fn ($event) => ['kind' => 'school', 'event' => $event]))
        ->sortByDesc(fn ($row) => $row['event']->event_date?->timestamp ?? 0)
        ->take(5);
?>

<div class="flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-4 shadow-sm shadow-black/20 sm:p-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-white">Event Management</h3>
            <p class="mt-0.5 text-xs text-slate-400">Talent competitions and school events in your scope</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('admin.events-talent.index')); ?>" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10 sm:flex-none">View all</a>
            <?php if($canCreateTalentEvents): ?>
                <a href="<?php echo e(route('admin.talent-competition.create')); ?>" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10 sm:flex-none">Create talent</a>
            <?php endif; ?>
            <?php if($canCreateEvents): ?>
                <a href="<?php echo e(route('admin.events.create')); ?>" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500 sm:flex-none">Create school event</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if($previewEvents->isEmpty()): ?>
        <p id="dashboard-events-empty" class="mt-6 px-2 py-8 text-center text-sm text-slate-500">No events yet. Create a talent competition or school event to populate this table.</p>
        <div id="dashboard-events-cards" class="mt-4 hidden space-y-3 md:hidden"></div>
        <div id="dashboard-events-table" class="mt-4 hidden flex-1 overflow-x-auto">
            <table class="min-w-full text-left text-xs sm:text-sm">
                <thead class="border-b border-slate-800 text-slate-400">
                    <tr>
                        <th class="px-2 py-2 font-medium">Event</th>
                        <th class="px-2 py-2 font-medium">Category</th>
                        <th class="px-2 py-2 font-medium">Schedule</th>
                        <th class="px-2 py-2 font-medium">Status</th>
                        <th class="px-2 py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="dashboard-events-tbody"></tbody>
            </table>
        </div>
    <?php else: ?>
        <p id="dashboard-events-empty" class="mt-6 hidden px-2 py-8 text-center text-sm text-slate-500">No events yet. Create a talent competition or school event to populate this table.</p>

        <div id="dashboard-events-cards" class="mt-4 space-y-3 md:hidden">
            <?php $__currentLoopData = $previewEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $event = $row['event'];
                    $isTalent = $row['kind'] === 'talent';
                ?>
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                    <div class="flex gap-3">
                        <?php if($event->image_url): ?>
                            <img src="<?php echo e($event->image_url); ?>" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover ring-1 ring-slate-700">
                        <?php else: ?>
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-[10px] font-bold text-violet-300">EV</div>
                        <?php endif; ?>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-white"><?php echo e($event->title); ?></p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                <?php echo e($isTalent ? ($event->type?->label() ?? 'Talent') : 'School Event'); ?>

                                · <?php echo e($event->event_date?->format('M d, Y') ?? '—'); ?>

                            </p>
                            <div class="mt-2">
                                <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $isTalent ? $event->currentStatusKey() : $event->displayStatus()->value,'label' => $event->displayStatusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isTalent ? $event->currentStatusKey() : $event->displayStatus()->value),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->displayStatusLabel())]); ?>
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
                            <?php echo $__env->make('admin.dashboard._event-preview-actions', [
                                'event' => $event,
                                'isTalent' => $isTalent,
                                'canCreateTalentEvents' => $canCreateTalentEvents,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div id="dashboard-events-table" class="mt-4 hidden flex-1 overflow-x-auto md:block">
            <table class="min-w-full text-left text-xs sm:text-sm">
                <thead class="border-b border-slate-800 text-slate-400">
                    <tr>
                        <th class="px-2 py-2 font-medium">Event</th>
                        <th class="px-2 py-2 font-medium">Category</th>
                        <th class="px-2 py-2 font-medium">Schedule</th>
                        <th class="px-2 py-2 font-medium">Status</th>
                        <th class="px-2 py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="dashboard-events-tbody" class="divide-y divide-slate-800/80">
                    <?php $__currentLoopData = $previewEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $event = $row['event'];
                            $isTalent = $row['kind'] === 'talent';
                        ?>
                        <tr class="text-slate-200">
                            <td class="px-2 py-3">
                                <div class="flex items-center gap-2.5">
                                    <?php if($event->image_url): ?>
                                        <img src="<?php echo e($event->image_url); ?>" alt="" class="h-9 w-9 shrink-0 rounded-lg object-cover ring-1 ring-slate-700">
                                    <?php else: ?>
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-[10px] font-bold text-violet-300">EV</div>
                                    <?php endif; ?>
                                    <span class="line-clamp-1 font-medium text-white"><?php echo e($event->title); ?></span>
                                </div>
                            </td>
                            <td class="px-2 py-3 text-slate-400">
                                <?php echo e($isTalent ? ($event->type?->label() ?? 'Talent') : 'School Event'); ?>

                            </td>
                            <td class="px-2 py-3 whitespace-nowrap text-slate-400"><?php echo e($event->event_date?->format('M d, Y') ?? '—'); ?></td>
                            <td class="px-2 py-3">
                                <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $isTalent ? $event->currentStatusKey() : $event->displayStatus()->value,'label' => $event->displayStatusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isTalent ? $event->currentStatusKey() : $event->displayStatus()->value),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->displayStatusLabel())]); ?>
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
                            </td>
                            <td class="px-2 py-3 text-right">
                                <?php echo $__env->make('admin.dashboard._event-preview-actions', [
                                    'event' => $event,
                                    'isTalent' => $isTalent,
                                    'canCreateTalentEvents' => $canCreateTalentEvents,
                                    'wrapperClass' => 'flex flex-wrap items-center justify-end gap-2',
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_events-preview.blade.php ENDPATH**/ ?>