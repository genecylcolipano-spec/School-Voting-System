<?php
    $onDashboard = request()->routeIs('faculty.dashboard');
    $onAssigned = request()->routeIs('faculty.judging.index', 'faculty.judging.show', 'faculty.judging.score');
    $onPerformances = request()->routeIs('faculty.judging.performances');
    $onSubmitted = request()->routeIs('faculty.judging.submitted');
    $onElections = request()->routeIs('faculty.elections.*');
    $onEvents = request()->routeIs('faculty.events.*');
    $onAnnouncements = request()->routeIs('faculty.announcements.*');
    $onSettings = request()->routeIs('profile.edit') && auth()->user()?->isFaculty();
?>

<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.dashboard'),'label' => 'Dashboard','active' => $onDashboard]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.dashboard')),'label' => 'Dashboard','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onDashboard)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <div class="px-3 py-2">
        <p x-show="!collapsed" class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">My Judging</p>
    </div>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.judging.index'),'label' => 'Assigned Competitions','active' => $onAssigned]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.judging.index')),'label' => 'Assigned Competitions','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onAssigned)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.judging.performances'),'label' => 'Judge Performances','active' => $onPerformances]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.judging.performances')),'label' => 'Judge Performances','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onPerformances)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.judging.submitted'),'label' => 'Submitted Scores','active' => $onSubmitted]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.judging.submitted')),'label' => 'Submitted Scores','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onSubmitted)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <div class="my-3 border-t border-teal-500/10"></div>

    <div class="px-3 py-2">
        <p x-show="!collapsed" class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">View only</p>
    </div>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.elections.index'),'label' => 'Elections','active' => $onElections]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.elections.index')),'label' => 'Elections','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onElections)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.events.index'),'label' => 'School Events','active' => $onEvents]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.events.index')),'label' => 'School Events','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onEvents)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('faculty.announcements.index'),'label' => 'Announcements','active' => $onAnnouncements]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.announcements.index')),'label' => 'Announcements','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onAnnouncements)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <div class="my-3 border-t border-teal-500/10"></div>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('profile.edit'),'label' => 'Settings','active' => $onSettings]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit')),'label' => 'Settings','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onSettings)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $attributes = $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7)): ?>
<?php $component = $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7; ?>
<?php unset($__componentOriginal866f5e2d42640ffa335179aba2fdf6c7); ?>
<?php endif; ?>

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="px-1 pt-2">
        <?php echo csrf_field(); ?>
        <button
            type="submit"
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-300 transition hover:bg-rose-500/10"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span x-show="!collapsed" class="truncate">Logout</span>
        </button>
    </form>
</nav>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/partials/sidebar-nav.blade.php ENDPATH**/ ?>