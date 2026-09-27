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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => ''.e($entry->display_name).'','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($entry->display_name).'','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php
            $routeParams = filled($from)
                ? [$competition, $entry, 'from' => $from]
                : [$competition, $entry];
            $cta = $sheet?->isLocked() ? 'View scores' : ($sheet ? 'Continue' : 'Score');
            $category = $entry->talentCategoryLabel();
        ?>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e($backUrl); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; <?php echo e($backLabel); ?></a>
            <a
                href="<?php echo e(route('faculty.judging.score', $routeParams)); ?>"
                class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
            >
                <?php echo e($cta); ?>

            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70 lg:col-span-1">
                <div class="aspect-square bg-slate-950">
                    <?php echo $__env->make('faculty.judging._entry-photo', ['entry' => $entry, 'initialClass' => 'text-5xl'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="p-5">
                    <h1 class="text-xl font-bold text-white"><?php echo e($entry->display_name); ?></h1>
                    <?php if($category): ?>
                        <p class="mt-2">
                            <span class="rounded-full border border-teal-400/30 bg-teal-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-200"><?php echo e($category); ?></span>
                        </p>
                    <?php endif; ?>
                    <p class="mt-3 text-sm font-semibold text-teal-200"><?php echo e($entry->performance_title ?: 'Untitled performance'); ?></p>
                    <p class="mt-1 text-sm text-slate-400"><?php echo e($competition->title); ?></p>
                </div>
            </section>

            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-teal-300">Participant</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Grade / Year</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($entry->grade_level ?: '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Section</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($entry->section ?: '—'); ?></dd>
                        </div>
                        <?php if($entry->course_strand): ?>
                            <div class="sm:col-span-2">
                                <dt class="text-xs uppercase tracking-wide text-slate-500">Course / Strand</dt>
                                <dd class="mt-1 font-medium text-white"><?php echo e($entry->course_strand); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </section>

                <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-teal-300">About this performance</h2>
                    <?php if($entry->profile_summary): ?>
                        <p class="mt-4 text-sm leading-relaxed text-slate-300"><?php echo e($entry->profile_summary); ?></p>
                    <?php endif; ?>
                    <?php if($entry->performance_description): ?>
                        <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($entry->performance_description); ?></p>
                    <?php endif; ?>
                    <?php if($entry->social_media): ?>
                        <p class="mt-4 text-sm text-teal-200"><?php echo e($entry->social_media); ?></p>
                    <?php endif; ?>
                    <?php if(! $entry->profile_summary && ! $entry->performance_description && ! $entry->social_media): ?>
                        <p class="mt-4 text-sm text-slate-500">No profile details were provided for this performance.</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/judging/profile.blade.php ENDPATH**/ ?>