<section id="posters" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <h3 class="text-lg font-semibold text-white">Partylist & Posters</h3>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <?php $__empty_1 = true; $__currentLoopData = $partylists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partylist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4" data-partylist-card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-white"><?php echo e($partylist->name); ?></h4>
                        <?php if($partylist->acronym): ?>
                            <p class="text-xs text-violet-300"><?php echo e($partylist->acronym); ?></p>
                        <?php endif; ?>
                        <?php if($partylist->motto): ?>
                            <p class="mt-1 text-xs italic text-slate-500">"<?php echo e($partylist->motto); ?>"</p>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $partylist->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($partylist->status->value)]); ?>
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
                <p class="mt-3 text-sm text-slate-400"><?php echo e($partylist->platform); ?></p>

                <div class="mt-4 space-y-3">
                    <?php $__empty_2 = true; $__currentLoopData = $partylist->posters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poster): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                        <?php if($poster->hasUploadedFile()): ?>
                            <div class="rounded-lg border border-slate-700 bg-slate-900/80 p-3">
                                <div class="flex flex-wrap gap-3">
                                    <a href="<?php echo e($poster->file_url); ?>" target="_blank" rel="noopener" class="block shrink-0 overflow-hidden rounded-lg border border-slate-700">
                                        <img src="<?php echo e($poster->file_url); ?>" alt="<?php echo e($poster->title); ?>" class="h-20 w-20 object-cover" loading="lazy">
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-white"><?php echo e($poster->title); ?></p>
                                        <?php if($poster->description): ?>
                                            <p class="mt-1 text-xs text-slate-400"><?php echo e($poster->description); ?></p>
                                        <?php endif; ?>
                                        <p class="mt-1 text-[10px] text-slate-500">Uploaded <?php echo e($poster->submitted_at?->format('M d, Y')); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                        <p class="text-xs text-slate-500">No posters uploaded for this partylist.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-400 lg:col-span-2">No published campaigns yet. Set a campaign status to Published to display it here.</p>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_posters.blade.php ENDPATH**/ ?>