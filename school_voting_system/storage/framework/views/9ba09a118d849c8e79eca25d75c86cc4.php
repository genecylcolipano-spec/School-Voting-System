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
    <?php if (isset($component)) { $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => 'Announcements','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Announcements','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">Messages targeted to faculty and all users.</p>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
                    <?php if($announcement->hasUploadedBanner()): ?>
                        <?php if (isset($component)) { $__componentOriginalb4ae95e62e8615350ae7fdaa410354d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-image','data' => ['src' => $announcement->bannerUrl(),'srcMedium' => $announcement->bannerMediumUrl(),'srcMobile' => $announcement->bannerMobileUrl(),'orientation' => $announcement->bannerOrientation(),'contain' => $announcement->bannerNeedsContainLayout(),'alt' => $announcement->title,'class' => 'rounded-none']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcement->bannerUrl()),'src-medium' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcement->bannerMediumUrl()),'src-mobile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcement->bannerMobileUrl()),'orientation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcement->bannerOrientation()),'contain' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcement->bannerNeedsContainLayout()),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcement->title),'class' => 'rounded-none']); ?>
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
                        <div class="flex flex-wrap items-center gap-2">
                            <?php if($announcement->is_pinned): ?>
                                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-200">Pinned</span>
                            <?php endif; ?>
                            <?php if($announcement->category): ?>
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($announcement->category->badgeClasses()); ?>"><?php echo e($announcement->category->label()); ?></span>
                            <?php endif; ?>
                            <?php if($announcement->priority && $announcement->priority !== \App\Enums\AnnouncementPriority::Normal): ?>
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($announcement->priority->badgeClasses()); ?>"><?php echo e($announcement->priority->label()); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3 flex items-start justify-between gap-3">
                            <h2 class="text-lg font-semibold text-white"><?php echo e($announcement->title); ?></h2>
                            <span class="shrink-0 text-xs text-slate-500"><?php echo e(optional($announcement->published_at)->format('M d, Y')); ?></span>
                        </div>
                        <?php if($announcement->summary): ?>
                            <p class="mt-3 line-clamp-3 text-sm text-slate-400"><?php echo e($announcement->summary); ?></p>
                        <?php endif; ?>
                        <a
                            href="<?php echo e(route('faculty.announcements.show', $announcement)); ?>"
                            class="mt-4 inline-flex rounded-xl border border-teal-500/30 px-4 py-2 text-sm font-semibold text-teal-200 hover:bg-teal-500/10"
                        >
                            Read more
                        </a>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500 md:col-span-2">
                    No announcements for faculty right now.
                </div>
            <?php endif; ?>
        </div>

        <div><?php echo e($announcements->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/announcements/index.blade.php ENDPATH**/ ?>