<?php
    $accent = $campaign->color ?: '#22d3ee';
?>

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
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-end">
                <a href="<?php echo e(route('student.campaigns.index')); ?>" class="shrink-0 rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    All campaigns
                </a>
            </div>

            <?php echo $__env->make('student.campaigns._banner-section', ['campaign' => $campaign, 'accent' => $accent], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="mt-6 space-y-6">
                <?php if($campaign->description || $campaign->platform): ?>
                    <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Campaign Information</h2>

                        <?php if($campaign->description): ?>
                            <div class="mt-4">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">About</h3>
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($campaign->description); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if($campaign->platform): ?>
                            <div class="<?php echo e($campaign->description ? 'mt-5 border-t border-slate-800 pt-5' : 'mt-4'); ?>">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Platform</h3>
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($campaign->platform); ?></p>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                
                <?php if($relevantElection && $campaignCandidates->isNotEmpty()): ?>
                    <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Candidates</h2>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-400"><?php echo e($relevantElection->title); ?></span>
                                <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $relevantElection->status?->value ?? 'draft']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relevantElection->status?->value ?? 'draft')]); ?>
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
                        <?php if($relevantElection->voting_starts_at): ?>
                            <p class="mt-1 text-xs text-slate-500">
                                <?php echo e($relevantElection->voting_starts_at->format('M j, Y g:i A')); ?>

                                <?php if($relevantElection->voting_ends_at): ?> – <?php echo e($relevantElection->voting_ends_at->format('M j, Y g:i A')); ?> <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <?php $__currentLoopData = $campaignCandidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $candPhoto = \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                                        ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                                        : null;
                                ?>
                                <div class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                                    <?php if($candPhoto): ?>
                                        <img src="<?php echo e($candPhoto); ?>" alt="<?php echo e($candidate->display_name); ?>" class="h-14 w-14 rounded-full border border-slate-700 object-cover">
                                    <?php else: ?>
                                        <span class="flex h-14 w-14 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-slate-500">
                                            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" /></svg>
                                        </span>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-white"><?php echo e($candidate->display_name); ?></p>
                                        <p class="truncate text-xs" style="color: <?php echo e($accent); ?>"><?php echo e($candidate->category?->name ?? $candidate->position); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </section>
                <?php elseif($relevantElection): ?>
                    <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Candidates</h2>
                        <p class="mt-3 text-sm text-slate-400">No active candidates listed for this campaign in <?php echo e($relevantElection->title); ?>.</p>
                    </section>
                <?php endif; ?>

                <?php echo $__env->make('student.campaigns._posters-section', ['campaign' => $campaign], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('student.campaigns._action-bar', [
                    'buttonState' => $buttonState,
                    'accent' => $accent,
                    'election' => $relevantElection,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>

    <style>[x-cloak]{display:none !important;}</style>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/campaigns/show.blade.php ENDPATH**/ ?>