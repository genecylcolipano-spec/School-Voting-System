<?php if($canManageFundraisers): ?>
    <section id="fundraisers" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
        <?php if (isset($component)) { $__componentOriginal87b1b280c26c60b1db52189dd51eb1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal87b1b280c26c60b1db52189dd51eb1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-section-header','data' => ['title' => 'Fundraisers','description' => 'School fundraising campaigns — visible to students when active.','badge' => $statistics['active_fundraisers'] > 0 ? $statistics['active_fundraisers'].' active' : null,'badgeTone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Fundraisers','description' => 'School fundraising campaigns — visible to students when active.','badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['active_fundraisers'] > 0 ? $statistics['active_fundraisers'].' active' : null),'badge-tone' => 'emerald']); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <a href="<?php echo e(route('admin.fundraisers.index')); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">View all</a>
                <?php if($canCreateFundraiser): ?>
                    <a href="<?php echo e(route('admin.fundraisers.create')); ?>" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create Fundraiser</a>
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

        <?php if($fundraisers->isEmpty()): ?>
            <p class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-6 text-center text-sm text-slate-400">
                No fundraisers yet.
                <?php if($canCreateFundraiser): ?>
                    <a href="<?php echo e(route('admin.fundraisers.create')); ?>" class="ml-1 text-violet-300 hover:text-violet-200">Create one</a>
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div id="dashboard-fundraisers-grid" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php $__currentLoopData = $fundraisers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fundraiser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article data-fundraiser-id="<?php echo e($fundraiser->id); ?>" class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-semibold text-white"><?php echo e($fundraiser->title); ?></h4>
                            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $fundraiser->resolvedStatus()->value,'label' => $fundraiser->displayStatusLabel(),'dataLiveFundraiserStatus' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->resolvedStatus()->value),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->displayStatusLabel()),'data-live-fundraiser-status' => true]); ?>
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

                        <?php if($fundraiser->description): ?>
                            <p class="mt-2 line-clamp-2 text-sm text-slate-400"><?php echo e($fundraiser->description); ?></p>
                        <?php endif; ?>

                        <div class="mt-3 flex justify-between text-xs text-slate-400">
                            <span data-live-fundraiser-raised>Raised ₱<?php echo e(number_format((float) $fundraiser->amount_raised, 2)); ?></span>
                            <span>Goal ₱<?php echo e(number_format((float) $fundraiser->goal_amount, 2)); ?></span>
                        </div>

                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                            <div data-live-fundraiser-progress class="h-full rounded-full bg-gradient-to-r from-violet-600 to-indigo-400" style="width: <?php echo e($fundraiser->progressPercent()); ?>%"></div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                            <span data-live-fundraiser-donations><?php echo e($fundraiser->donations_count); ?> donation(s)</span>
                            <?php if($fundraiser->ends_on): ?>
                                <span>Ends <?php echo e($fundraiser->ends_on->format('M d, Y')); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $fundraiser)): ?>
                            <a href="<?php echo e(route('admin.fundraisers.edit', $fundraiser)); ?>" class="mt-3 inline-block text-xs font-semibold text-violet-300 hover:text-violet-200">Edit fundraiser →</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_fundraisers.blade.php ENDPATH**/ ?>