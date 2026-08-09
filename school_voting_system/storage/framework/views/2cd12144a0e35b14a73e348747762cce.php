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
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Fundraising</h1>
                    <p class="mt-1 text-sm text-slate-400">Support school initiatives and campaigns.</p>
                </div>
                <a href="<?php echo e(route('student.dashboard')); ?>" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    Back to dashboard
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <?php $__empty_1 = true; $__currentLoopData = $fundraisers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fundraiser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                        <?php if($fundraiser->hasUploadedBanner()): ?>
                            <?php if (isset($component)) { $__componentOriginalb4ae95e62e8615350ae7fdaa410354d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-image','data' => ['src' => $fundraiser->bannerUrl(),'srcMedium' => $fundraiser->bannerMediumUrl(),'srcMobile' => $fundraiser->bannerMobileUrl(),'orientation' => $fundraiser->bannerOrientation(),'contain' => $fundraiser->bannerNeedsContainLayout(),'alt' => $fundraiser->title,'class' => 'rounded-none']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->bannerUrl()),'src-medium' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->bannerMediumUrl()),'src-mobile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->bannerMobileUrl()),'orientation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->bannerOrientation()),'contain' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->bannerNeedsContainLayout()),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->title),'class' => 'rounded-none']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0)): ?>
<?php $attributes = $__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0; ?>
<?php unset($__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4ae95e62e8615350ae7fdaa410354d0)): ?>
<?php $component = $__componentOriginalb4ae95e62e8615350ae7fdaa410354d0; ?>
<?php unset($__componentOriginalb4ae95e62e8615350ae7fdaa410354d0); ?>
<?php endif; ?>
                        <?php endif; ?>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h2 class="truncate text-lg font-semibold text-white"><?php echo e($fundraiser->title); ?></h2>
                                    <?php if($fundraiser->description): ?>
                                        <p class="mt-2 text-sm text-slate-300 line-clamp-2"><?php echo e($fundraiser->description); ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-300">
                                    <?php echo e($fundraiser->displayStatusLabel()); ?>

                                </span>
                            </div>

                            <div class="mt-4 text-xs text-slate-400">
                                Raised ₱<?php echo e(number_format((float) $fundraiser->amount_raised, 2)); ?> · Goal ₱<?php echo e(number_format((float) $fundraiser->goal_amount, 2)); ?>

                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400" style="width: <?php echo e($fundraiser->progressPercent()); ?>%"></div>
                            </div>

                            <a href="<?php echo e(route('student.fundraising.show', $fundraiser)); ?>" class="mt-4 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                <?php echo e($fundraiser->isAcceptingDonations() ? 'Donate' : 'View Campaign'); ?>

                            </a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-slate-300 md:col-span-2">
                        No fundraisers found.
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6">
                <?php echo e($fundraisers->links()); ?>

            </div>
        </div>
    </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/fundraising/index.blade.php ENDPATH**/ ?>