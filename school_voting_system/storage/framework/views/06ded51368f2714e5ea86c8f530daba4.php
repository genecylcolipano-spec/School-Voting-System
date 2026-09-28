<div class="flex flex-wrap items-center <?php echo e($align ?? 'justify-end'); ?> gap-1.5">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $election)): ?>
        <a href="<?php echo e(route('admin.elections.show', $election)); ?>" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $election)): ?>
        <a href="<?php echo e(route('admin.elections.edit', $election)); ?>" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Edit</a>
    <?php endif; ?>
    <?php if($canCreateElections ?? false): ?>
        <form method="POST" action="<?php echo e(route('admin.elections.duplicate', $election)); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Duplicate</button>
        </form>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $election)): ?>
        <?php if (! ($election->status === \App\Enums\ElectionStatus::Archived)): ?>
            <form method="POST" action="<?php echo e(route('admin.elections.archive', $election)); ?>" onsubmit="return confirm('Archive this election? Students will no longer see it as an open vote.');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="rounded-lg border border-amber-500/40 px-2 py-1 text-xs font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $election)): ?>
        <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.elections.destroy', $election),'warning' => $deleteWarning,'buttonClass' => 'rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.elections.destroy', $election)),'warning' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deleteWarning),'button-class' => 'rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57)): ?>
<?php $attributes = $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57; ?>
<?php unset($__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal469a4ba3cbb96eb4bd9792641d671d57)): ?>
<?php $component = $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57; ?>
<?php unset($__componentOriginal469a4ba3cbb96eb4bd9792641d671d57); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/elections/partials/row-actions.blade.php ENDPATH**/ ?>