<?php
    $metricCards = [
        [
            'label' => 'Voter Turnout',
            'value' => $statistics['turnout_percent'].'%',
            'href' => '#live-voting-panel',
            'tone' => 'emerald',
            'chart' => 'line',
        ],
        [
            'label' => 'Total Votes',
            'value' => number_format($statistics['votes_cast']),
            'href' => '#live-voting-panel',
            'tone' => 'violet',
            'chart' => 'bars',
        ],
        [
            'label' => 'Eligible Voters',
            'value' => number_format($statistics['eligible_voters']),
            'href' => '#live-voting-panel',
            'tone' => 'indigo',
            'chart' => 'donut',
        ],
        [
            'label' => 'Not Voted',
            'value' => number_format($voterBreakdown['notVoted']),
            'href' => '#live-voting-panel',
            'tone' => 'rose',
            'chart' => 'line',
        ],
        [
            'label' => 'Campaigns',
            'value' => number_format($statistics['partylists']),
            'href' => '#posters',
            'tone' => 'violet',
            'chart' => 'bars',
        ],
        [
            'label' => 'Candidates',
            'value' => number_format($statistics['candidates']),
            'href' => '#live-voting-panel',
            'tone' => 'emerald',
            'chart' => 'donut',
        ],
    ];

    $chartStroke = [
        'violet' => '#a78bfa',
        'emerald' => '#34d399',
        'indigo' => '#818cf8',
        'rose' => '#fb7185',
        'amber' => '#fbbf24',
    ];
    $sparklineKeys = [
        'Voter Turnout' => 'turnout_percent',
        'Total Votes' => 'votes_cast',
        'Eligible Voters' => 'eligible_voters',
        'Not Voted' => 'not_voted',
        'Campaigns' => 'partylists',
        'Candidates' => 'candidates',
    ];
?>

<div class="grid h-full grid-cols-2 gap-3 sm:grid-cols-3">
    <?php $__currentLoopData = $metricCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $stroke = $chartStroke[$card['tone']] ?? '#a78bfa';
            $sparklineKey = $sparklineKeys[$card['label']] ?? null;
            $sparkline = $sparklineKey ? ($statSparklines[$sparklineKey] ?? null) : null;
        ?>
        <a
            href="<?php echo e($card['href']); ?>"
            class="group flex flex-col justify-between rounded-2xl border border-violet-500/15 bg-slate-900/80 p-4 shadow-sm shadow-black/20 transition hover:border-violet-400/35 hover:bg-slate-900"
        >
            <div class="flex items-start justify-between gap-2">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?php echo e($card['label']); ?></p>
                <span class="text-[10px] text-slate-600 group-hover:text-violet-300">↗</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-white" data-live-stat="<?php echo e(match($card['label']) {
                'Voter Turnout' => 'turnout_percent',
                'Total Votes' => 'votes_cast',
                'Eligible Voters' => 'eligible_voters',
                'Not Voted' => 'not_voted',
                'Campaigns' => 'partylists',
                'Candidates' => 'candidates',
                default => 'metric',
            }); ?>"><?php echo e($card['value']); ?></p>
            <div class="mt-3 h-8 opacity-80">
                <?php if($sparkline): ?>
                    <?php if (isset($component)) { $__componentOriginal6166694be8110e92b8873751fdaa9810 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6166694be8110e92b8873751fdaa9810 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-sparkline','data' => ['type' => $sparkline['type'],'values' => $sparkline['values'] ?? [],'percent' => $sparkline['percent'] ?? 0,'stroke' => $stroke,'liveKey' => $sparklineKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-sparkline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sparkline['type']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sparkline['values'] ?? []),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sparkline['percent'] ?? 0),'stroke' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stroke),'live-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sparklineKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6166694be8110e92b8873751fdaa9810)): ?>
<?php $attributes = $__attributesOriginal6166694be8110e92b8873751fdaa9810; ?>
<?php unset($__attributesOriginal6166694be8110e92b8873751fdaa9810); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6166694be8110e92b8873751fdaa9810)): ?>
<?php $component = $__componentOriginal6166694be8110e92b8873751fdaa9810; ?>
<?php unset($__componentOriginal6166694be8110e92b8873751fdaa9810); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_stats.blade.php ENDPATH**/ ?>