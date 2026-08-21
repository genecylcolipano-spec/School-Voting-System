<?php
    $wrapperClass = $wrapperClass ?? 'mt-3 flex flex-wrap items-center gap-2';
    $isTalent = $isTalent ?? false;
    $canCreateTalentEvents = $canCreateTalentEvents ?? false;
?>

<div class="<?php echo e($wrapperClass); ?>">
    <?php if($isTalent): ?>
        <a href="<?php echo e(route('admin.talent-competition.edit', $event)); ?>" class="inline-flex min-h-10 items-center rounded-lg border border-violet-500/30 px-3 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10 md:min-h-8 md:py-1.5 md:text-xs">Manage</a>
        <?php if($canCreateTalentEvents && (auth()->user()->isSuperAdmin() || (int) $event->created_by === (int) auth()->id())): ?>
            <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.talent-competition.destroy', $event),'buttonClass' => 'inline-flex min-h-10 items-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10 md:min-h-8 md:py-1.5 md:text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.talent-competition.destroy', $event)),'button-class' => 'inline-flex min-h-10 items-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10 md:min-h-8 md:py-1.5 md:text-xs']); ?>
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
    <?php else: ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $event)): ?>
            <a href="<?php echo e(route('admin.events.edit', $event)); ?>" class="inline-flex min-h-10 items-center rounded-lg border border-violet-500/30 px-3 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10 md:min-h-8 md:py-1.5 md:text-xs">Manage</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $event)): ?>
            <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.events.destroy', $event),'buttonClass' => 'inline-flex min-h-10 items-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10 md:min-h-8 md:py-1.5 md:text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.events.destroy', $event)),'button-class' => 'inline-flex min-h-10 items-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10 md:min-h-8 md:py-1.5 md:text-xs']); ?>
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
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_event-preview-actions.blade.php ENDPATH**/ ?>