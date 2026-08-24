<?php
    $accent = $accent ?? 'cyan';
    $isTeal = $accent === 'teal';
    $linkClass = $isTeal ? 'text-teal-300 hover:text-teal-200' : 'text-cyan-300 hover:text-cyan-200';
    $cardBorder = $isTeal ? 'border-teal-500/15' : 'border-cyan-500/15';
    $headingClass = $isTeal ? 'text-teal-300' : 'text-cyan-300';
    $winnerBorder = $isTeal ? 'border-teal-500/15 hover:border-teal-400/25' : 'border-cyan-500/15 hover:border-cyan-400/25';
    $congratsBg = $isTeal ? 'from-emerald-500/10 to-teal-500/5' : 'from-emerald-500/10 to-cyan-500/5';
    $spotlightTheme = $isTeal ? 'faculty' : 'student';
?>

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="<?php echo e($backUrl); ?>" class="text-sm font-medium <?php echo e($linkClass); ?> transition">&larr; <?php echo e($backLabel); ?></a>
        <h2 class="mt-2 text-2xl font-bold text-white"><?php echo e($detail['name']); ?></h2>
        <p class="mt-1 text-sm text-slate-400"><?php echo e($detail['category']); ?></p>
    </div>
</div>


<?php if($detail['is_official']): ?>
    <div class="flex items-start gap-3 rounded-2xl border border-emerald-500/25 bg-emerald-500/10 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">🟢</span>
        <div>
            <p class="font-semibold text-emerald-200">Official Results</p>
            <p class="mt-1 text-sm text-emerald-100/80">Congratulations to the winners.</p>
        </div>
    </div>
<?php elseif($detail['is_open']): ?>
    <div class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/10 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">🟡</span>
        <div>
            <p class="font-semibold text-amber-200">Voting is still ongoing.</p>
            <p class="mt-1 text-sm text-amber-100/80">Official results will be published once voting officially closes.</p>
        </div>
    </div>
<?php elseif(($detail['student_status_tone'] ?? '') === 'review' || ($detail['student_status'] ?? '') === 'Under Review'): ?>
    <div class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/10 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">⏳</span>
        <div>
            <p class="font-semibold text-amber-200">Results are not yet available.</p>
            <p class="mt-1 text-sm text-amber-100/80">Official results will be published after administrator review.</p>
        </div>
    </div>
<?php else: ?>
    <div class="flex items-start gap-3 rounded-2xl border border-slate-700 bg-slate-900/70 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">⏳</span>
        <div>
            <p class="font-semibold text-slate-200">Event not yet open</p>
            <p class="mt-1 text-sm text-slate-400">Results will be available after the voting event is completed.</p>
        </div>
    </div>
<?php endif; ?>


<section class="mb-6 rounded-2xl border <?php echo e($cardBorder); ?> bg-slate-900/70 p-5 sm:p-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide <?php echo e($headingClass); ?>">Event Information</h2>
    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">Status</dt>
            <dd class="mt-1 font-medium text-white"><?php echo e($detail['student_status']); ?></dd>
        </div>
        <?php if($detail['event_date']): ?>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Date</dt>
                <dd class="mt-1 font-medium text-white"><?php echo e($detail['event_date']); ?></dd>
            </div>
        <?php endif; ?>
        <?php if($detail['starts_at']): ?>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Voting Starts</dt>
                <dd class="mt-1 font-medium text-white"><?php echo e($detail['starts_at']); ?></dd>
            </div>
        <?php endif; ?>
        <?php if($detail['ends_at']): ?>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Voting Ends</dt>
                <dd class="mt-1 font-medium text-white"><?php echo e($detail['ends_at']); ?></dd>
            </div>
        <?php endif; ?>
    </dl>
    <?php if($detail['description']): ?>
        <p class="mt-4 text-sm leading-relaxed text-slate-300"><?php echo e($detail['description']); ?></p>
    <?php endif; ?>
</section>

<?php if($detail['is_official']): ?>
    <?php if (isset($component)) { $__componentOriginal578dbf52e12dc6d3ec213f47252a1a45 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.winner-spotlight','data' => ['spotlight' => $detail['winner_spotlight'] ?? [],'primary' => $detail['primary_winner'] ?? null,'publishedAt' => $detail['results_published_at'] ?? null,'publishedTime' => $detail['results_published_time'] ?? null,'publishedBy' => $detail['results_published_by'] ?? null,'theme' => $spotlightTheme]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('winner-spotlight'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['spotlight' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['winner_spotlight'] ?? []),'primary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['primary_winner'] ?? null),'published-at' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['results_published_at'] ?? null),'published-time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['results_published_time'] ?? null),'published-by' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['results_published_by'] ?? null),'theme' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spotlightTheme)]); ?>
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

    
    <?php if(count($detail['winners']) > 0 && ($detail['winners_layout'] ?? '') !== 'election'): ?>
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">
                <?php if($detail['winners_layout'] === 'election'): ?>
                    Winner by Position
                <?php elseif($detail['winners_layout'] === 'intramurals'): ?>
                    Placements &amp; Awards
                <?php else: ?>
                    Winners
                <?php endif; ?>
            </h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <?php $__currentLoopData = $detail['winners']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $winner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="rounded-2xl border <?php echo e($winnerBorder); ?> bg-slate-900/70 p-5 transition">
                        <p class="text-xs font-semibold uppercase tracking-wide <?php echo e($headingClass); ?>">🏆 <?php echo e($winner['label']); ?></p>
                        <p class="mt-2 text-lg font-semibold text-white"><?php echo e($winner['name']); ?></p>
                        <?php if(! empty($winner['party']) && ($detail['winners_layout'] ?? '') === 'election'): ?>
                            <p class="mt-1 text-sm text-slate-400">Party · <?php echo e($winner['party']); ?></p>
                        <?php elseif(! empty($winner['position'] ?? null)): ?>
                            <p class="mt-1 text-sm text-slate-400"><?php echo e($winner['position']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>

    
    <?php if(count($detail['special_awards'] ?? []) > 0): ?>
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Special Awards</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <?php $__currentLoopData = $detail['special_awards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-violet-300"><?php echo e($award['label']); ?></p>
                        <p class="mt-2 text-lg font-semibold text-white"><?php echo e($award['name']); ?></p>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>

    
    <?php if(count($detail['top_finalists'] ?? []) > 0): ?>
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Top Finalists</h2>
            <div class="overflow-hidden rounded-2xl border <?php echo e($cardBorder); ?> bg-slate-900/70">
                <ul class="divide-y divide-slate-800">
                    <?php $__currentLoopData = $detail['top_finalists']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $finalist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-sm font-medium text-slate-400">#<?php echo e($finalist['rank']); ?></span>
                            <span class="flex-1 font-medium text-white"><?php echo e($finalist['name']); ?></span>
                            <?php if(! empty($finalist['position'])): ?>
                                <span class="text-xs text-slate-500"><?php echo e($finalist['position']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    
    <?php if(count($detail['rankings']) > 0 && ($detail['winners_layout'] ?? '') !== 'election'): ?>
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Final Rankings</h2>
            <div class="overflow-hidden rounded-2xl border <?php echo e($cardBorder); ?> bg-slate-900/70">
                <ul class="divide-y divide-slate-800">
                    <?php $__currentLoopData = $detail['rankings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('student.results._ranking-row', [
                            'rank' => $row['rank'] ?? null,
                            'name' => $row['name'] ?? '—',
                            'meta' => $row['position'] ?? null,
                            'votes' => $row['votes'] ?? null,
                            'percent' => $row['percent'] ?? 0,
                            'accent' => $accent,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    
    <?php if(($detail['winners_layout'] ?? '') === 'election' && count($detail['rankings']) > 0): ?>
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Full Rankings</h2>
            <?php
                $grouped = collect($detail['rankings'])->groupBy('position');
            ?>
            <div class="space-y-4">
                <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position => $candidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="overflow-hidden rounded-2xl border <?php echo e($cardBorder); ?> bg-slate-900/70">
                        <div class="border-b border-slate-800 px-5 py-3">
                            <h3 class="text-sm font-semibold <?php echo e($headingClass); ?>"><?php echo e($position); ?></h3>
                        </div>
                        <ul class="divide-y divide-slate-800">
                            <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('student.results._ranking-row', [
                                    'rank' => $row['rank'] ?? null,
                                    'name' => $row['name'] ?? '—',
                                    'meta' => $row['party'] ?? null,
                                    'votes' => $row['votes'] ?? null,
                                    'percent' => $row['percent'] ?? 0,
                                    'accent' => $accent,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>

    
    <?php if(! empty($detail['statistics'])): ?>
        <section class="mb-6 rounded-2xl border <?php echo e($cardBorder); ?> bg-slate-900/70 p-5 sm:p-6">
            <h2 class="text-lg font-bold text-white">Vote Statistics</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-950/50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Turnout</dt>
                    <dd class="mt-1 text-2xl font-bold text-emerald-300"><?php echo e(number_format($detail['statistics']['turnout_percent'], 1)); ?>%</dd>
                </div>
                <div class="rounded-xl bg-slate-950/50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Total Votes</dt>
                    <dd class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($detail['statistics']['total_votes'])); ?></dd>
                </div>
                <div class="rounded-xl bg-slate-950/50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">
                        <?php echo e(($detail['type'] ?? '') === 'election' ? 'Eligible Voters' : 'Contestants'); ?>

                    </dt>
                    <dd class="mt-1 text-2xl font-bold text-white"><?php echo e(number_format($detail['statistics']['participants'])); ?></dd>
                </div>
            </dl>
        </section>
    <?php endif; ?>

    <div class="rounded-2xl border border-emerald-500/20 bg-gradient-to-br <?php echo e($congratsBg); ?> px-6 py-8 text-center">
        <p class="text-2xl" aria-hidden="true">🎉</p>
        <p class="mt-3 text-lg font-semibold text-white">Congratulations to all winners!</p>
        <p class="mt-1 text-sm text-slate-400">Thank you to everyone who participated in this event.</p>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/results/_event-detail.blade.php ENDPATH**/ ?>