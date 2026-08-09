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
    <?php if (isset($component)) { $__componentOriginal57da683fe32826f08aa9f05c3342a7e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Reports','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reports','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Election Reports',
            'description' => 'Election summary, turnout, winners, party performance, and exports.',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if($report): ?>
            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Election</p>
                    <p class="mt-2 text-lg font-bold text-white"><?php echo e($report['election_name']); ?></p>
                </div>
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Total Votes</p>
                    <p class="mt-2 text-2xl font-bold text-white"><?php echo e(number_format($report['total_votes'])); ?></p>
                </div>
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Turnout</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-300"><?php echo e(number_format($report['turnout_percent'], 1)); ?>%</p>
                </div>
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Registered Students</p>
                    <p class="mt-2 text-2xl font-bold text-white"><?php echo e(number_format($report['participants'])); ?></p>
                </div>
            </section>

            <?php if($exportUrls): ?>
                <div class="mb-6 flex flex-wrap gap-2">
                    <a href="<?php echo e($exportUrls['pdf']); ?>" class="rs-export-btn">Export PDF</a>
                    <a href="<?php echo e($exportUrls['excel']); ?>" class="rs-export-btn">Export Excel</a>
                    <a href="<?php echo e($exportUrls['print']); ?>" class="rs-export-btn">Print Report</a>
                    <?php if($election): ?>
                        <a href="<?php echo e(route('admin.results.election.show', $election)); ?>" class="rs-export-btn">Open Results Dashboard</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal578dbf52e12dc6d3ec213f47252a1a45 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.winner-spotlight','data' => ['spotlight' => $report['winners'],'primary' => collect($report['winners'])->first(),'theme' => 'admin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('winner-spotlight'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['spotlight' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['winners']),'primary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($report['winners'])->first()),'theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('admin')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45)): ?>
<?php $attributes = $__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45; ?>
<?php unset($__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal578dbf52e12dc6d3ec213f47252a1a45)): ?>
<?php $component = $__componentOriginal578dbf52e12dc6d3ec213f47252a1a45; ?>
<?php unset($__componentOriginal578dbf52e12dc6d3ec213f47252a1a45); ?>
<?php endif; ?>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h3 class="text-lg font-semibold text-white">Winning Party</h3>
                    <?php if($winningParty): ?>
                        <p class="mt-3 text-2xl font-bold text-violet-200"><?php echo e($winningParty['party'] ?? '—'); ?></p>
                        <p class="mt-1 text-sm text-slate-400"><?php echo e(number_format($winningParty['total_votes'] ?? $winningParty['votes'] ?? 0)); ?> votes · <?php echo e(number_format($winningParty['percent'] ?? $winningParty['share'] ?? 0, 1)); ?>% share · <?php echo e($winningParty['seats_won'] ?? 0); ?> seats</p>
                    <?php else: ?>
                        <p class="mt-4 text-sm text-slate-400">No party performance data yet.</p>
                    <?php endif; ?>
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h3 class="text-lg font-semibold text-white">Party Performance</h3>
                    <div class="mt-4 space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $report['party_performance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $party): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium text-white"><?php echo e($party['party'] ?? '—'); ?></p>
                                    <span class="font-semibold text-violet-300"><?php echo e(number_format($party['percent'] ?? $party['share'] ?? 0, 1)); ?>%</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400"><?php echo e(number_format($party['total_votes'] ?? $party['votes'] ?? 0)); ?> votes · <?php echo e($party['seats_won'] ?? 0); ?> seats won</p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-slate-400">No party data available.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Participation by Grade / Section</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-slate-400">
                                <th class="px-4 py-3">Grade</th>
                                <th class="px-4 py-3">Section</th>
                                <th class="px-4 py-3">Registered</th>
                                <th class="px-4 py-3">Voted</th>
                                <th class="px-4 py-3">Turnout</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php
                                $turnoutRows = collect($report['turnout_sections'] ?? [])->isNotEmpty()
                                    ? collect($report['turnout_sections'])
                                    : collect($turnoutSections ?? []);
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $turnoutRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $grade = $row['grade'] ?? null;
                                    $section = $row['section'] ?? null;
                                    if (($grade === null || $section === null) && filled($row['label'] ?? null)) {
                                        $parts = array_map('trim', explode('·', (string) $row['label'], 2));
                                        $grade ??= $parts[0] ?? 'All';
                                        $section ??= $parts[1] ?? 'General';
                                    }
                                ?>
                                <tr>
                                    <td class="px-4 py-3 text-white"><?php echo e($grade ?: '—'); ?></td>
                                    <td class="px-4 py-3 text-white"><?php echo e($section ?: '—'); ?></td>
                                    <td class="px-4 py-3"><?php echo e(number_format($row['registered'] ?? $row['eligible'] ?? 0)); ?></td>
                                    <td class="px-4 py-3"><?php echo e(number_format($row['voted'] ?? 0)); ?></td>
                                    <td class="px-4 py-3 font-semibold text-emerald-300"><?php echo e(number_format($row['turnout_percent'] ?? $row['turnout'] ?? 0, 1)); ?>%</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No turnout breakdown available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php else: ?>
            <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 px-6 py-12 text-center">
                <p class="text-lg font-semibold text-white">No assigned election report</p>
                <p class="mt-2 text-sm text-slate-400">Assign an election to your admin account to generate election summary reports.</p>
                <a href="<?php echo e(route('admin.analytics.index')); ?>" class="mt-5 inline-flex rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Open Analytics Dashboard</a>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $attributes = $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $component = $__componentOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>