<?php
    $accent = $accent ?? '#22d3ee';
    $election = $election ?? null;
?>

<section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
    <?php if($election): ?>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <p class="text-sm font-semibold text-white"><?php echo e($election->title); ?></p>
            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $election->status?->value ?? 'draft']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($election->status?->value ?? 'draft')]); ?>
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
    <?php endif; ?>

    <div class="flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">
        <a
            href="<?php echo e(route('student.campaigns.index')); ?>"
            class="inline-flex items-center justify-center rounded-xl border border-cyan-500/25 bg-transparent px-5 py-2.5 text-sm font-semibold text-cyan-300 transition hover:border-cyan-400/40 hover:bg-slate-800"
        >
            <span aria-hidden="true" class="mr-2">←</span>
            Back to Campaigns
        </a>

        <div class="flex w-full flex-col sm:w-auto sm:items-end">
            <?php if($buttonState['enabled'] && $buttonState['url']): ?>
                <a
                    href="<?php echo e($buttonState['url']); ?>"
                    class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-500/10 transition hover:opacity-90 sm:w-auto"
                    style="background: linear-gradient(135deg, <?php echo e($accent); ?>, #38bdf8)"
                >
                    <?php echo e($buttonState['label']); ?>

                    <span aria-hidden="true" class="ml-2">→</span>
                </a>
            <?php else: ?>
                <button
                    type="button"
                    disabled
                    aria-disabled="true"
                    class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl border border-slate-700 bg-slate-800/60 px-6 py-3 text-sm font-semibold text-slate-400 sm:w-auto"
                >
                    <?php echo e($buttonState['label']); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if(! empty($buttonState['message'])): ?>
        <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'mt-4 text-sm text-slate-400',
            'text-center sm:text-right' => $buttonState['state'] !== 'voted',
            'rounded-xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-3 text-emerald-200/90' => $buttonState['state'] === 'voted',
        ]); ?>">
            <?php echo e($buttonState['message']); ?>

        </p>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/campaigns/_action-bar.blade.php ENDPATH**/ ?>