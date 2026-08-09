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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Events & Talent','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Events & Talent','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Events & Talent',
            'description' => 'School events and talent competitions — separate from election voting.',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid gap-6 lg:grid-cols-2">
            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Talent Competitions</h2>
                        <p class="mt-1 text-sm text-slate-400">Debates, quiz bowls, talent shows</p>
                    </div>
                    <?php if($canCreateTalent): ?>
                        <a href="<?php echo e(route('admin.talent-competition.create')); ?>" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create</a>
                    <?php endif; ?>
                </div>

                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $talentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-white"><?php echo e($event->title); ?></span>
                                        <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $event->currentStatusKey(),'label' => $event->displayStatusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->currentStatusKey()),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->displayStatusLabel())]); ?>
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
                                    <p class="mt-1 text-xs text-slate-500"><?php echo e($event->entries_count); ?> entries · <?php echo e($event->event_date?->format('M d, Y')); ?></p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    <a href="<?php echo e(route('admin.talent-competition.edit', $event)); ?>" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    <?php if($canCreateTalent && (auth()->user()->isSuperAdmin() || (int) $event->created_by === (int) auth()->id())): ?>
                                        <?php
                                            $talentWarning = $event->entries_count > 0
                                                ? 'This talent competition contains related data: participants. Related judges, scores, and videos may also be linked.'
                                                : null;
                                        ?>
                                        <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.talent-competition.destroy', $event),'warning' => $talentWarning,'buttonClass' => 'text-xs font-semibold text-rose-300 hover:text-rose-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.talent-competition.destroy', $event)),'warning' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($talentWarning),'button-class' => 'text-xs font-semibold text-rose-300 hover:text-rose-200']); ?>
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
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-400">No talent events yet.</p>
                    <?php endif; ?>
                </div>

                <a href="<?php echo e(route('admin.talent-competition.index')); ?>" class="mt-4 inline-block text-sm font-semibold text-violet-300 hover:text-violet-200">View all talent events →</a>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">School Events</h2>
                        <p class="mt-1 text-sm text-slate-400">Announcements, orientations, campus activities</p>
                    </div>
                    <?php if($canCreateEvents): ?>
                        <a href="<?php echo e(route('admin.events.create')); ?>" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create</a>
                    <?php endif; ?>
                </div>

                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $schoolEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-white"><?php echo e($event->title); ?></span>
                                        <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $event->status?->value ?? 'scheduled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->status?->value ?? 'scheduled')]); ?>
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
                                    <p class="mt-1 text-xs text-slate-500"><?php echo e($event->event_date?->format('M d, Y')); ?> · <?php echo e($event->venue); ?></p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
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
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-400">No school events yet.</p>
                    <?php endif; ?>
                </div>

                <a href="<?php echo e(route('admin.events.index')); ?>" class="mt-4 inline-block text-sm font-semibold text-violet-300 hover:text-violet-200">View all events →</a>
            </section>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/events-talent/index.blade.php ENDPATH**/ ?>