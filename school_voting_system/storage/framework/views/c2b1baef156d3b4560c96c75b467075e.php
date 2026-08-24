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
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <a href="<?php echo e(url()->previous()); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back</a>

            <article class="mt-6 overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                <div class="grid gap-6 p-6 md:grid-cols-[160px_1fr]">
                    <?php if (isset($component)) { $__componentOriginalef16c20e6fca2a3d5d9ed18ab3425243 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalef16c20e6fca2a3d5d9ed18ab3425243 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.candidate-avatar','data' => ['path' => $candidate->photo_path,'name' => $candidate->display_name,'size' => 'xl','class' => 'mx-auto !h-40 !w-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('candidate-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($candidate->photo_path),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($candidate->display_name),'size' => 'xl','class' => 'mx-auto !h-40 !w-40']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalef16c20e6fca2a3d5d9ed18ab3425243)): ?>
<?php $attributes = $__attributesOriginalef16c20e6fca2a3d5d9ed18ab3425243; ?>
<?php unset($__attributesOriginalef16c20e6fca2a3d5d9ed18ab3425243); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalef16c20e6fca2a3d5d9ed18ab3425243)): ?>
<?php $component = $__componentOriginalef16c20e6fca2a3d5d9ed18ab3425243; ?>
<?php unset($__componentOriginalef16c20e6fca2a3d5d9ed18ab3425243); ?>
<?php endif; ?>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-cyan-300"><?php echo e($candidate->category?->name ?? $candidate->position ?? 'Candidate'); ?></p>
                        <h1 class="mt-1 text-3xl font-bold text-white"><?php echo e($candidate->display_name); ?></h1>
                        <p class="mt-2 text-sm text-slate-400"><?php echo e($candidate->party_or_group ?: 'Independent'); ?></p>
                        <?php if($grade || $section): ?>
                            <p class="mt-1 text-sm text-slate-500">Grade <?php echo e($grade ?? '—'); ?> · Section <?php echo e($section ?? '—'); ?></p>
                        <?php endif; ?>
                        <p class="mt-2 text-xs text-slate-500"><?php echo e($candidate->election?->title); ?></p>
                    </div>
                </div>

                <div class="space-y-6 border-t border-slate-800 p-6">
                    <?php if($candidate->platform): ?>
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Platform</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($candidate->platform); ?></p>
                        </section>
                    <?php endif; ?>

                    <?php if($candidate->biography): ?>
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Biography</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($candidate->biography); ?></p>
                        </section>
                    <?php endif; ?>

                    <?php if($candidate->campaign_promises): ?>
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Campaign Promises</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($candidate->campaign_promises); ?></p>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/candidates/show.blade.php ENDPATH**/ ?>