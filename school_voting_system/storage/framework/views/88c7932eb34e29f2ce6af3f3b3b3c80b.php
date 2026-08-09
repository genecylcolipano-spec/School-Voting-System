<?php
    $onDashboard = request()->routeIs('student.dashboard');
    $onStatistics = request()->routeIs('student.statistics');
?>

<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.dashboard'),'label' => 'Dashboard','active' => $onDashboard]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.dashboard')),'label' => 'Dashboard','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onDashboard)]); ?>
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

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.statistics'),'label' => 'Statistics','active' => $onStatistics]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.statistics')),'label' => 'Statistics','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onStatistics)]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.events.index'),'label' => 'Events','active' => request()->routeIs('student.events.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.events.index')),'label' => 'Events','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.events.*'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.voting.index'),'label' => 'My Voting','active' => request()->routeIs('student.voting.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.voting.index')),'label' => 'My Voting','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.voting.*'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.results.index'),'label' => 'Results','active' => request()->routeIs('student.results.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.results.index')),'label' => 'Results','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.results.*'))]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.campaigns.index'),'label' => 'Campaigns','active' => request()->routeIs('student.campaigns.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.campaigns.index')),'label' => 'Campaigns','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.campaigns.*'))]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.talent-voting.index'),'label' => 'Talent Competition','active' => request()->routeIs('student.talent-voting.*') || request()->routeIs('student.talent-registration.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.talent-voting.index')),'label' => 'Talent Competition','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.talent-voting.*') || request()->routeIs('student.talent-registration.*'))]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.fundraising.index'),'label' => 'Fundraising','active' => request()->routeIs('student.fundraising.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.fundraising.index')),'label' => 'Fundraising','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.fundraising.*'))]); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('student.announcements.index'),'label' => 'Announcements','active' => request()->routeIs('student.announcements.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.announcements.index')),'label' => 'Announcements','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('student.announcements.*'))]); ?>
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

    <div class="my-3 border-t border-violet-500/10"></div>

    <?php if (isset($component)) { $__componentOriginal866f5e2d42640ffa335179aba2fdf6c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal866f5e2d42640ffa335179aba2fdf6c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-sidebar-link','data' => ['href' => route('profile.edit'),'label' => 'Settings','active' => request()->routeIs('profile.*')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit')),'label' => 'Settings','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('profile.*'))]); ?>
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

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="pt-2">
        <?php echo csrf_field(); ?>
        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-400 transition hover:bg-slate-800/70 hover:text-rose-300">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span x-show="!collapsed">Logout</span>
        </button>
    </form>
</nav>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/partials/sidebar-nav.blade.php ENDPATH**/ ?>