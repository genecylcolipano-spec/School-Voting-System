<div class="grid gap-4 xl:grid-cols-12">
    <div class="xl:col-span-6">
        <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Participation Growth (Events/Voting)','subtitle' => 'Monthly turnout and event participation','type' => 'line','liveKey' => 'participation','labels' => $analyticsWidgets['participation']['labels'],'values' => $analyticsWidgets['participation']['values'],'yMax' => $analyticsWidgets['participation']['yMax'],'yTicks' => $analyticsWidgets['participation']['yTicks'],'valueSuffix' => $analyticsWidgets['participation']['valueSuffix'],'accent' => '#34d399','emptyMessage' => 'No participation data available.','footerLink' => route('admin.analytics.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Participation Growth (Events/Voting)','subtitle' => 'Monthly turnout and event participation','type' => 'line','live-key' => 'participation','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['participation']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['participation']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['participation']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['participation']['yTicks']),'value-suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['participation']['valueSuffix']),'accent' => '#34d399','empty-message' => 'No participation data available.','footer-link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.analytics.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3f4023e8ae0200a7792ee5dfef809633)): ?>
<?php $attributes = $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633; ?>
<?php unset($__attributesOriginal3f4023e8ae0200a7792ee5dfef809633); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3f4023e8ae0200a7792ee5dfef809633)): ?>
<?php $component = $__componentOriginal3f4023e8ae0200a7792ee5dfef809633; ?>
<?php unset($__componentOriginal3f4023e8ae0200a7792ee5dfef809633); ?>
<?php endif; ?>
    </div>
    <div class="xl:col-span-6">
        <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Donation/Fundraising History','subtitle' => 'Monthly donation totals for '.e(now()->year).'','type' => 'bar','liveKey' => 'fundraising','labels' => $analyticsWidgets['fundraising']['labels'],'values' => $analyticsWidgets['fundraising']['values'],'yMax' => $analyticsWidgets['fundraising']['yMax'],'yTicks' => $analyticsWidgets['fundraising']['yTicks'],'valuePrefix' => $analyticsWidgets['fundraising']['valuePrefix'],'accent' => '#818cf8','emptyMessage' => 'No fundraising records available.','footerLink' => route('admin.analytics.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Donation/Fundraising History','subtitle' => 'Monthly donation totals for '.e(now()->year).'','type' => 'bar','live-key' => 'fundraising','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['fundraising']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['fundraising']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['fundraising']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['fundraising']['yTicks']),'value-prefix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($analyticsWidgets['fundraising']['valuePrefix']),'accent' => '#818cf8','empty-message' => 'No fundraising records available.','footer-link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.analytics.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3f4023e8ae0200a7792ee5dfef809633)): ?>
<?php $attributes = $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633; ?>
<?php unset($__attributesOriginal3f4023e8ae0200a7792ee5dfef809633); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3f4023e8ae0200a7792ee5dfef809633)): ?>
<?php $component = $__componentOriginal3f4023e8ae0200a7792ee5dfef809633; ?>
<?php unset($__componentOriginal3f4023e8ae0200a7792ee5dfef809633); ?>
<?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_analytics-widgets.blade.php ENDPATH**/ ?>