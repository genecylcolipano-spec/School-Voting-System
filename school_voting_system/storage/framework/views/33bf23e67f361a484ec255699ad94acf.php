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
    ?>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <?php if($preview): ?>
                    <a href="<?php echo e(route('admin.announcements.edit', $announcement)); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to editor</a>
                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">Admin preview</span>
                <?php else: ?>
                    <a href="<?php echo e(route('student.announcements.index')); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to announcements</a>
                    <a href="<?php echo e(route('student.dashboard')); ?>" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
                <?php endif; ?>
            </div>

            <?php if($preview): ?>
                <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    This is a read-only preview. The announcement has not been published to students unless it is already live.
                </div>
            <?php endif; ?>

            <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
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

                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php if($announcement->is_pinned): ?>
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-200">Pinned</span>
                                <?php endif; ?>
                                <?php if($announcement->category): ?>
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($announcement->category->badgeClasses()); ?>"><?php echo e($announcement->category->label()); ?></span>
                                <?php endif; ?>
                                <?php if($announcement->priority): ?>
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($announcement->priority->badgeClasses()); ?>"><?php echo e($announcement->priority->label()); ?></span>
                                <?php endif; ?>
                            </div>
                            <h1 class="mt-3 text-2xl font-bold text-white"><?php echo e($announcement->title); ?></h1>
                            <p class="mt-2 text-xs text-slate-500">
                                Published <?php echo e(optional($announcement->published_at)->format('M d, Y g:i A') ?? '—'); ?>

                                <?php if($announcement->expires_at): ?>
                                    · Expires <?php echo e($announcement->expires_at->format('M d, Y g:i A')); ?>

                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="rounded-full border border-slate-700 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300"><?php echo e($announcement->displayStatusLabel()); ?></span>
                    </div>

                    <?php if($announcement->summary): ?>
                        <p class="mt-4 text-base text-slate-300"><?php echo e($announcement->summary); ?></p>
                    <?php endif; ?>

                    <?php if($announcement->body): ?>
                        <div class="mt-6 whitespace-pre-line text-sm leading-relaxed text-slate-200"><?php echo e($announcement->body); ?></div>
                    <?php endif; ?>

                    <?php if($announcement->related_module && $announcement->related_module !== \App\Enums\AnnouncementRelatedModule::None && $announcement->relatedRecordUrl()): ?>
                        <div class="mt-6">
                            <a href="<?php echo e($announcement->relatedRecordUrl()); ?>" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/20">
                                <?php echo e($announcement->related_module->viewLabel()); ?>

                                <?php if($announcement->relatedRecordTitle()): ?>
                                    <span class="text-cyan-100/80">— <?php echo e($announcement->relatedRecordTitle()); ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if($announcement->attachments->isNotEmpty()): ?>
                        <section class="mt-8 border-t border-slate-800 pt-6">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Attachments</h2>
                            <ul class="mt-3 space-y-2">
                                <?php $__currentLoopData = $announcement->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(($preview ?? false) ? route('admin.announcements.attachments.download', [$announcement, $attachment]) : route('student.announcements.attachments.download', [$announcement, $attachment])); ?>" class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm hover:border-cyan-500/30 hover:bg-slate-950/70">
                                            <span class="font-medium text-slate-200"><?php echo e($attachment->original_name); ?></span>
                                            <span class="text-xs text-slate-500"><?php echo e($attachment->formattedSize()); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                </div>
            </article>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/announcements/show.blade.php ENDPATH**/ ?>