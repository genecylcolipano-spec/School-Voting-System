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
    <?php
        $badgePalette = [
            'bg-violet-500/15 text-violet-200',
            'bg-emerald-500/15 text-emerald-200',
            'bg-amber-500/15 text-amber-200',
            'bg-rose-500/15 text-rose-200',
            'bg-sky-500/15 text-sky-200',
        ];
    ?>

    <?php if (isset($component)) { $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => ''.e($election->title).'','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($election->title).'','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('faculty.elections.index')); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to elections</a>
            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">View only</span>
        </div>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-white sm:text-2xl"><?php echo e($election->title); ?></h2>
                    <?php if($election->description): ?>
                        <p class="mt-2 text-sm text-slate-300"><?php echo e($election->description); ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e($election->status?->value ?? $election->status); ?></p>
                    <?php if($election->voting_starts_at): ?>
                        <p class="mt-1 text-xs text-slate-400">Starts <?php echo e($election->voting_starts_at->format('M d, Y g:i A')); ?></p>
                    <?php endif; ?>
                    <?php if($election->voting_ends_at): ?>
                        <p class="text-xs text-slate-400">Ends <?php echo e($election->voting_ends_at->format('M d, Y g:i A')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="space-y-6">
            <?php $__empty_1 = true; $__currentLoopData = $election->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $candidates = $election->activeCandidates->where('election_category_id', $category->id);
                ?>
                <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h3 class="text-lg font-semibold text-white"><?php echo e($category->name); ?></h3>
                    <?php if($category->description): ?>
                        <p class="mt-1 text-sm text-slate-400"><?php echo e($category->description); ?></p>
                    <?php endif; ?>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <?php $__empty_2 = true; $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <?php
                                $party = $candidate->party_or_group ?: 'Independent';
                                $badge = $party === 'Independent'
                                    ? 'bg-slate-700/60 text-slate-300'
                                    : $badgePalette[crc32($party) % count($badgePalette)];
                                $photo = \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                                    ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                                    : null;
                            ?>
                            <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                                <div class="flex items-start gap-3">
                                    <?php if($photo): ?>
                                        <img src="<?php echo e($photo); ?>" alt="" class="h-12 w-12 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-500/15 text-sm font-semibold text-teal-200">
                                            <?php echo e(strtoupper(substr($candidate->display_name, 0, 1))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <p class="font-medium text-white"><?php echo e($candidate->display_name); ?></p>
                                        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold <?php echo e($badge); ?>"><?php echo e($party); ?></span>
                                        <?php if($candidate->platform): ?>
                                            <p class="mt-2 line-clamp-3 text-xs text-slate-400"><?php echo e($candidate->platform); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <p class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">No active candidates for this position.</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500">
                    No positions have been configured for this election yet.
                </div>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $attributes = $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $component = $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
<?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/elections/show.blade.php ENDPATH**/ ?>