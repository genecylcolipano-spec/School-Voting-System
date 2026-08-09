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
    <?php if (isset($component)) { $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => 'Submitted Scores','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Submitted Scores','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">Competition-level completion for your assigned judging work. Individual score sheets remain locked after submit.</p>
        </section>

        <div class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
                <thead class="bg-slate-950/60 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Competition</th>
                        <th class="px-4 py-3">Judge Role</th>
                        <th class="px-4 py-3">Participants Judged</th>
                        <th class="px-4 py-3">Completion %</th>
                        <th class="px-4 py-3">Submission Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $summaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-3 text-white"><?php echo e($row['competition']->title); ?></td>
                            <td class="px-4 py-3 text-slate-300"><?php echo e($row['judge_role']); ?></td>
                            <td class="px-4 py-3 text-slate-300"><?php echo e($row['participants_judged']); ?> / <?php echo e($row['participants_total']); ?></td>
                            <td class="px-4 py-3 font-semibold text-teal-200"><?php echo e($row['completion_percent']); ?>%</td>
                            <td class="px-4 py-3 text-slate-400"><?php echo e(optional($row['submission_date'])->format('M d, Y g:i A') ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-xs font-semibold text-teal-200"><?php echo e($row['status']); ?></span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?php echo e(route('faculty.judging.show', $row['competition'])); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">Open</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No submitted scores yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <section class="mt-6 rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Individual score sheets</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-sm">
                    <thead class="bg-slate-950/60 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Competition</th>
                            <th class="px-4 py-3">Performance</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $sheets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-4 py-3 text-slate-200"><?php echo e($sheet->talentEvent?->title ?? '—'); ?></td>
                                <td class="px-4 py-3 text-white"><?php echo e($sheet->entry?->display_name ?? '—'); ?></td>
                                <td class="px-4 py-3 font-semibold text-teal-200"><?php echo e(number_format((float) $sheet->total_score, 2)); ?></td>
                                <td class="px-4 py-3 text-slate-400"><?php echo e(optional($sheet->submitted_at)->format('M d, Y g:i A') ?? '—'); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <?php if($sheet->talentEvent && $sheet->entry): ?>
                                        <a href="<?php echo e(route('faculty.judging.score', [$sheet->talentEvent, $sheet->entry])); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No individual sheets yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><?php echo e($sheets->links()); ?></div>
        </section>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/judging/submitted.blade.php ENDPATH**/ ?>