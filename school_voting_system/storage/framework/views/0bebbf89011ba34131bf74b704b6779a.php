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
        $minDonation = $fundraiser->minimumDonationAmount();
        $maxDonation = $fundraiser->maximumDonationAmount();
        $accepting = $fundraiser->isAcceptingDonations();
        $paymentMethods = $paymentMethods ?? $fundraiser->acceptedPaymentMethods();
        $paymongoConfigured = $paymongoConfigured ?? filled(config('services.paymongo.secret_key'));
        $hasOnlineMethod = collect($paymentMethods)->contains(fn ($method) => $method->isOnline());
        $defaultPaymentMethod = collect($paymentMethods)
            ->first(fn ($method) => ! ($method->isOnline() && ! $paymongoConfigured))
            ?->value;
        $selectedPaymentMethod = \App\Enums\DonationPaymentMethod::tryFrom((string) old('payment_method', $defaultPaymentMethod));
        $submitLabel = $selectedPaymentMethod?->donateSubmitLabel() ?? 'Submit donation';
        $submitLabels = collect($paymentMethods)
            ->mapWithKeys(fn ($method) => [$method->value => $method->donateSubmitLabel()])
            ->all();
    ?>
    <?php if (isset($component)) { $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => $fundraiser->title,'user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fundraiser->title),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('faculty.fundraising.index')); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">← Back to fundraising</a>
        </div>

        <?php if(session('success')): ?>
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
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
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <h1 class="break-words text-2xl font-bold text-white"><?php echo e($fundraiser->title); ?></h1>
                        <?php if($fundraiser->category): ?>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-teal-300"><?php echo e($fundraiser->category->label()); ?></p>
                        <?php endif; ?>
                        <?php if($fundraiser->description): ?>
                            <p class="mt-2 whitespace-pre-line text-justify text-sm leading-relaxed text-slate-300"><?php echo e($fundraiser->description); ?></p>
                        <?php endif; ?>
                        <?php if($fundraiser->beneficiary || $fundraiser->purpose): ?>
                            <dl class="mt-3 space-y-1 text-sm text-slate-400">
                                <?php if($fundraiser->beneficiary): ?>
                                    <div><span class="text-slate-400">Beneficiary:</span> <?php echo e($fundraiser->beneficiary); ?></div>
                                <?php endif; ?>
                                <?php if($fundraiser->purpose): ?>
                                    <div><span class="text-slate-400">Purpose:</span> <?php echo e($fundraiser->purpose); ?></div>
                                <?php endif; ?>
                            </dl>
                        <?php endif; ?>
                    </div>
                    <div class="shrink-0 sm:text-right">
                        <span class="inline-flex rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-300">
                            <?php echo e($fundraiser->displayStatusLabel()); ?>

                        </span>
                        <p class="mt-2 text-lg font-semibold tabular-nums text-white">₱<?php echo e(number_format((float) $fundraiser->amount_raised, 2)); ?></p>
                        <p class="text-xs text-slate-400">Raised</p>
                        <p class="mt-1 text-sm text-slate-300">Goal ₱<?php echo e(number_format((float) $fundraiser->goal_amount, 2)); ?></p>
                    </div>
                </div>

                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400" style="width: <?php echo e($fundraiser->progressPercent()); ?>%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-400"><?php echo e(number_format($fundraiser->progressPercent(), 1)); ?>% of goal · Remaining ₱<?php echo e(number_format($fundraiser->remainingAmount(), 2)); ?></p>
            </div>
        </article>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-6">
            <h2 class="text-lg font-semibold text-white">Make a donation</h2>
            <?php if($accepting): ?>
                <?php echo $__env->make('fundraising._donate-form', [
                    'donateAction' => route('faculty.fundraising.donate', $fundraiser),
                    'accent' => 'teal',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <p class="mt-3 text-sm text-slate-400">This campaign is not currently accepting donations.</p>
            <?php endif; ?>
        </section>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $attributes = $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $component = $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/fundraising/show.blade.php ENDPATH**/ ?>