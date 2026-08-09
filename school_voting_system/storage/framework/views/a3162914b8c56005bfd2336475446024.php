<?php
    use App\Support\EventImageUrl;
?>

<section>
    <div class="mb-4">
        <h2 class="text-xl font-bold text-white">Upcoming Activities</h2>
        <p class="mt-1 text-sm text-slate-400">Browse upcoming elections, school events, talent competitions, and fundraising campaigns available to you.</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70 shadow-sm shadow-black/20">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] table-fixed border-collapse text-sm">
                <colgroup>
                    <col style="width: 72px">
                    <col>
                    <col style="width: 150px">
                    <col style="width: 170px">
                    <col style="width: 170px">
                    <col style="width: 140px">
                </colgroup>
                <thead class="border-b border-slate-800 text-slate-400">
                    <tr>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Banner</th>
                        <th class="h-12 px-3 py-3 text-left align-middle font-medium sm:px-4">Event</th>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Category</th>
                        <th class="h-12 px-3 py-3 text-left align-middle font-medium sm:px-4">Schedule</th>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Status</th>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingSchedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $actionVariant = $item['action_style'] ?? ($item['action_disabled'] ? 'disabled' : 'secondary');
                            $actionDisabled = ($actionVariant === 'disabled')
                                || (bool) ($item['action_disabled'] ?? false)
                                || empty($item['action_url']);
                            $categoryCover = EventImageUrl::coverFor($item['category_key'] ?? null);
                            $bannerSrc = EventImageUrl::uploadedOrCover($item['banner_url'] ?? null, $item['category_key'] ?? null);
                        ?>
                        <tr class="h-[72px] text-slate-200 transition hover:bg-slate-800/40">
                            <td class="px-2 py-3 align-middle">
                                <div class="flex h-10 w-full items-center justify-center">
                                    <img
                                        src="<?php echo e($bannerSrc); ?>"
                                        alt=""
                                        class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-slate-700"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='<?php echo e($categoryCover); ?>';"
                                    >
                                </div>
                            </td>
                            <td class="overflow-hidden px-3 py-3 text-left align-middle sm:px-4">
                                <span class="line-clamp-2 break-words font-medium leading-snug text-white" title="<?php echo e($item['title']); ?>">
                                    <?php echo e($item['title']); ?>

                                </span>
                            </td>
                            <td class="px-2 py-3 align-middle">
                                <div class="flex w-full items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['type' => 'category','toneKey' => $item['category_key'],'label' => $item['category']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'category','tone-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['category_key']),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['category'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-left align-middle text-slate-400 sm:px-4">
                                <span class="block leading-snug" title="<?php echo e($item['schedule_label']); ?>">
                                    <?php echo e($item['schedule_label']); ?>

                                </span>
                            </td>
                            <td class="px-2 py-3 align-middle">
                                <div class="flex w-full items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['type' => 'status','toneKey' => $item['status_key'],'label' => $item['status_label']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'status','tone-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['status_key']),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['status_label'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                </div>
                            </td>
                            <td class="px-2 py-3 align-middle">
                                <div class="flex w-full items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginal897b42a47ecd20a68e8cc0d392b7acfd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal897b42a47ecd20a68e8cc0d392b7acfd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.action-button','data' => ['href' => $actionDisabled ? null : $item['action_url'],'variant' => $actionVariant,'disabled' => $actionDisabled]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actionDisabled ? null : $item['action_url']),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actionVariant),'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actionDisabled)]); ?>
                                        <?php echo e($item['action_label']); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal897b42a47ecd20a68e8cc0d392b7acfd)): ?>
<?php $attributes = $__attributesOriginal897b42a47ecd20a68e8cc0d392b7acfd; ?>
<?php unset($__attributesOriginal897b42a47ecd20a68e8cc0d392b7acfd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal897b42a47ecd20a68e8cc0d392b7acfd)): ?>
<?php $component = $__componentOriginal897b42a47ecd20a68e8cc0d392b7acfd; ?>
<?php unset($__componentOriginal897b42a47ecd20a68e8cc0d392b7acfd); ?>
<?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <p class="text-sm font-semibold text-white">No upcoming activities</p>
                                <p class="mt-1 text-sm text-slate-500">There are currently no elections, events, competitions, or fundraising campaigns available.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/dashboards/_upcoming-activities.blade.php ENDPATH**/ ?>