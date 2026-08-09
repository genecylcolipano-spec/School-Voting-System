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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Announcements','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Announcements','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Communication Center',
            'action' => route('admin.announcements.create'),
            'actionLabel' => 'New announcement',
            'showAction' => auth()->user()->can('create', App\Models\Announcement::class),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php if($announcement->is_pinned): ?>
                                    <span class="text-xs text-amber-300">📌</span>
                                <?php endif; ?>
                                <?php if($announcement->is_auto_generated): ?>
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-200">Auto</span>
                                <?php endif; ?>
                                <?php if($announcement->category): ?>
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($announcement->category->badgeClasses()); ?>"><?php echo e($announcement->category->label()); ?></span>
                                <?php endif; ?>
                                <?php if($announcement->priority): ?>
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($announcement->priority->badgeClasses()); ?>"><?php echo e($announcement->priority->label()); ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-white"><?php echo e($announcement->title); ?></h3>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-400"><?php echo e($announcement->summary); ?></p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            <p class="font-semibold text-slate-300"><?php echo e($announcement->displayStatusLabel()); ?></p>
                            <p class="mt-1"><?php echo e(optional($announcement->published_at)->format('M d, Y g:i A') ?? 'Not scheduled'); ?></p>
                            <p class="mt-1"><?php echo e($announcement->author?->name ?? 'System'); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $announcement)): ?>
                            <a href="<?php echo e(route('admin.announcements.edit', $announcement)); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Edit</a>
                            <a href="<?php echo e(route('admin.announcements.preview', $announcement)); ?>" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">Preview</a>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $announcement)): ?>
                            <form method="POST" action="<?php echo e(route('admin.announcements.destroy', $announcement)); ?>" onsubmit="return confirm('Delete announcement?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-sm text-rose-300 hover:text-rose-200">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400">No announcements yet.</p>
            <?php endif; ?>
        </div>
        <div class="mt-6"><?php echo e($announcements->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/announcements/index.blade.php ENDPATH**/ ?>