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
    <?php
        $preview = $preview ?? false;
        $minDonation = $fundraiser->minimumDonationAmount();
        $maxDonation = $fundraiser->maximumDonationAmount();
        $accepting = ! $preview && $fundraiser->isAcceptingDonations();
    ?>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <?php if($preview): ?>
                    <a href="<?php echo e(route('admin.fundraisers.edit', $fundraiser)); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to campaign editor</a>
                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">Admin preview</span>
                <?php else: ?>
                    <a href="<?php echo e(route('student.fundraising.index')); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to fundraising</a>
                    <a href="<?php echo e(route('student.dashboard')); ?>" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
                <?php endif; ?>
            </div>

            <?php if($preview): ?>
                <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    This is a read-only preview of the student donation page. Donations cannot be submitted from preview mode.
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

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
                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h1 class="truncate text-2xl font-bold text-white"><?php echo e($fundraiser->title); ?></h1>
                            <?php if($fundraiser->category): ?>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-cyan-300"><?php echo e($fundraiser->category->label()); ?></p>
                            <?php endif; ?>
                            <?php if($fundraiser->description): ?>
                                <p class="mt-2 text-sm text-slate-300"><?php echo e($fundraiser->description); ?></p>
                            <?php endif; ?>
                            <?php if($fundraiser->beneficiary || $fundraiser->purpose): ?>
                                <dl class="mt-3 space-y-1 text-sm text-slate-400">
                                    <?php if($fundraiser->beneficiary): ?>
                                        <div><span class="text-slate-500">Beneficiary:</span> <?php echo e($fundraiser->beneficiary); ?></div>
                                    <?php endif; ?>
                                    <?php if($fundraiser->purpose): ?>
                                        <div><span class="text-slate-500">Purpose:</span> <?php echo e($fundraiser->purpose); ?></div>
                                    <?php endif; ?>
                                </dl>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e($fundraiser->displayStatusLabel()); ?></p>
                            <p class="mt-1 text-xs text-slate-400">
                                Raised ₱<?php echo e(number_format((float) $fundraiser->amount_raised, 2)); ?>

                            </p>
                            <p class="text-xs text-slate-400">
                                Goal ₱<?php echo e(number_format((float) $fundraiser->goal_amount, 2)); ?>

                            </p>
                        </div>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400" style="width: <?php echo e($fundraiser->progressPercent()); ?>%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500"><?php echo e(number_format($fundraiser->progressPercent(), 1)); ?>% of goal · Remaining ₱<?php echo e(number_format($fundraiser->remainingAmount(), 2)); ?></p>
                </div>
            </article>

            <section class="mt-6 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                <h2 class="text-lg font-semibold text-white">Make a donation</h2>
                <?php if($preview): ?>
                    <p class="mt-3 text-sm text-slate-400">
                        <?php if($fundraiser->isAcceptingDonations()): ?>
                            Students can donate here when this campaign is published and visible.
                        <?php else: ?>
                            This campaign is not currently accepting donations.
                        <?php endif; ?>
                    </p>
                <?php elseif($accepting): ?>
                    <form method="POST" action="<?php echo e(route('student.fundraising.donate', $fundraiser)); ?>" class="mt-4 space-y-4">
                        <?php echo csrf_field(); ?>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Amount (PHP)</label>
                            <input
                                name="amount"
                                type="number"
                                step="0.01"
                                min="<?php echo e($minDonation); ?>"
                                <?php if($maxDonation): ?> max="<?php echo e($maxDonation); ?>" <?php endif; ?>
                                required
                                value="<?php echo e(old('amount')); ?>"
                                class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Minimum ₱<?php echo e(number_format($minDonation, 2)); ?>

                                <?php if($maxDonation): ?>
                                    · Maximum ₱<?php echo e(number_format($maxDonation, 2)); ?>

                                <?php endif; ?>
                            </p>
                            <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Message (optional)</label>
                            <input
                                name="message"
                                type="text"
                                maxlength="255"
                                value="<?php echo e(old('message')); ?>"
                                class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                            />
                            <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <?php if($fundraiser->allow_anonymous !== false): ?>
                            <label class="flex items-center gap-2 text-sm text-slate-300">
                                <input type="checkbox" name="is_anonymous" value="1" class="rounded border-slate-700 bg-slate-950/50 text-cyan-500 focus:ring-cyan-500/30" />
                                Donate anonymously
                            </label>
                        <?php endif; ?>

                        <button type="submit" class="inline-flex rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950">
                            Donate
                        </button>
                    </form>
                <?php else: ?>
                    <p class="mt-3 text-sm text-slate-400">This campaign is not currently accepting donations.</p>
                <?php endif; ?>
            </section>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/fundraising/show.blade.php ENDPATH**/ ?>