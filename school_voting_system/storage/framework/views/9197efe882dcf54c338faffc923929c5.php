<?php
    $previewEvents = collect()
        ->merge($talentEvents->map(fn ($event) => ['kind' => 'talent', 'event' => $event]))
        ->merge($schoolEvents->map(fn ($event) => ['kind' => 'school', 'event' => $event]))
        ->sortByDesc(fn ($row) => $row['event']->event_date?->timestamp ?? 0)
        ->take(5);
?>

<div class="flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-white">Event Management</h3>
            <p class="mt-0.5 text-xs text-slate-400">Talent competitions and school events in your scope</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('admin.events-talent.index')); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">View all</a>
            <?php if($canCreateTalentEvents): ?>
                <a href="<?php echo e(route('admin.talent-competition.create')); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Create talent</a>
            <?php endif; ?>
            <?php if($canCreateEvents): ?>
                <a href="<?php echo e(route('admin.events.create')); ?>" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create school event</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 flex-1 overflow-x-auto">
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
                <?php $__empty_1 = true; $__currentLoopData = $previewEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                        <td class="px-2 py-3 text-slate-400"><?php echo e($event->event_date?->format('M d, Y') ?? '—'); ?></td>
                        <td class="px-2 py-3">
                            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $isTalent ? $event->currentStatusKey() : ($event->status?->value ?? 'scheduled'),'label' => $isTalent ? $event->displayStatusLabel() : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isTalent ? $event->currentStatusKey() : ($event->status?->value ?? 'scheduled')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isTalent ? $event->displayStatusLabel() : null)]); ?>
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
                            <div class="inline-flex items-center gap-3">
                                <?php if($isTalent): ?>
                                    <a href="<?php echo e(route('admin.talent-competition.edit', $event)); ?>" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    <?php if($canCreateTalentEvents && (auth()->user()->isSuperAdmin() || (int) $event->created_by === (int) auth()->id())): ?>
                                        <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.talent-competition.destroy', $event),'buttonClass' => 'text-xs font-semibold text-rose-300 hover:text-rose-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.talent-competition.destroy', $event)),'button-class' => 'text-xs font-semibold text-rose-300 hover:text-rose-200']); ?>
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
                                <?php else: ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $event)): ?>
                                        <a href="<?php echo e(route('admin.events.edit', $event)); ?>" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $event)): ?>
                                        <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.events.destroy', $event),'buttonClass' => 'text-xs font-semibold text-rose-300 hover:text-rose-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.events.destroy', $event)),'button-class' => 'text-xs font-semibold text-rose-300 hover:text-rose-200']); ?>
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
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-2 py-8 text-center text-slate-500">No events yet. Create a talent competition or school event to populate this table.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_events-preview.blade.php ENDPATH**/ ?>