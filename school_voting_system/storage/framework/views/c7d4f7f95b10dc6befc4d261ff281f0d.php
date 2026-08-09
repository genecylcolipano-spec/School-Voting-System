<div class="relative flex h-full min-h-[17rem] flex-col justify-between gap-6 overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/80 p-6 shadow-sm shadow-black/20 sm:p-7">
    <div>
        <span class="inline-flex rounded-full border border-violet-500/20 bg-slate-950/60 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
            <?php echo e($assignedRole); ?> Console
        </span>
        <h2 class="mt-4 text-2xl font-bold leading-tight tracking-tight text-white sm:text-3xl">
            Unified Campus Event and Voting Management System
        </h2>
        <p class="mt-3 max-w-md text-sm font-normal leading-relaxed text-slate-400">
            Manage elections, campaigns, events, and live voting efficiently.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <?php if (! ($isAuditor || $isReadOnly)): ?>
            <?php if($canEditElection && $election): ?>
                <a href="<?php echo e(route('admin.elections.edit', $election)); ?>" class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                    Manage Election
                </a>
            <?php elseif($canCreateElection): ?>
                <a href="<?php echo e(route('admin.elections.create')); ?>" class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                    Create Election
                </a>
            <?php endif; ?>
            <?php if($canCreateFundraiser): ?>
                <a href="<?php echo e(route('admin.fundraisers.create')); ?>" class="rounded-xl border border-violet-500/30 px-4 py-2 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">
                    Create Fundraiser
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $statistics['election_status'],'label' => $statistics['election_status'],'dataLiveElectionStatus' => true,'dataFallback' => ''.e($statistics['election_status']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['election_status']),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['election_status']),'data-live-election-status' => true,'data-fallback' => ''.e($statistics['election_status']).'']); ?>
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
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_hero.blade.php ENDPATH**/ ?>