<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'type' => 'line',
    'labels' => [],
    'values' => [],
    'yMax' => 100,
    'yTicks' => [0, 50, 100],
    'valuePrefix' => '',
    'valueSuffix' => '',
    'accent' => '#818cf8',
    'footerLink' => null,
    'footerLabel' => 'View full reports →',
    'liveKey' => null,
    'emptyMessage' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title',
    'subtitle' => null,
    'type' => 'line',
    'labels' => [],
    'values' => [],
    'yMax' => 100,
    'yTicks' => [0, 50, 100],
    'valuePrefix' => '',
    'valueSuffix' => '',
    'accent' => '#818cf8',
    'footerLink' => null,
    'footerLabel' => 'View full reports →',
    'liveKey' => null,
    'emptyMessage' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $labels = array_values($labels);
    $values = array_map('floatval', array_values($values));
    $count = max(count($labels), count($values), 1);

    $isEmpty = count(array_filter($values, static fn ($value) => $value > 0)) === 0;

    while (count($values) < $count) {
        $values[] = 0.0;
    }

    while (count($labels) < $count) {
        $labels[] = '';
    }

    $plotW = 280;
    $plotH = 120;
    $padL = 44;
    $padB = 30;
    $padT = 10;
    $padR = 10;
    $width = $padL + $plotW + $padR;
    $height = $padT + $plotH + $padB;
    $yMax = max((float) $yMax, 1.0);
    $yTicks = array_values($yTicks);

    $toX = static function (int $index) use ($count, $padL, $plotW): float {
        if ($count <= 1) {
            return (float) ($padL + ($plotW / 2));
        }

        return $padL + (($index / ($count - 1)) * $plotW);
    };

    $toY = static function (float $value) use ($yMax, $padT, $plotH): float {
        return $padT + $plotH - (min($value, $yMax) / $yMax) * $plotH;
    };

    $formatTick = static function (float $tick) use ($valuePrefix, $valueSuffix): string {
        $formatted = fmod($tick, 1.0) === 0.0 ? (string) (int) $tick : number_format($tick, 1);

        return $valuePrefix.$formatted.$valueSuffix;
    };
?>

<div
    <?php echo e($attributes->merge(['class' => 'flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20'])); ?>

    <?php if($liveKey): ?> data-live-chart="<?php echo e($liveKey); ?>" data-chart-type="<?php echo e($type); ?>" data-chart-accent="<?php echo e($accent); ?>" <?php endif; ?>
    <?php if($emptyMessage): ?> data-empty-message="<?php echo e($emptyMessage); ?>" <?php endif; ?>
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="text-base font-semibold text-white"><?php echo e($title); ?></h3>
            <?php if($subtitle): ?>
                <p class="mt-0.5 text-xs text-slate-400"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 flex-1">
        <svg viewBox="0 0 <?php echo e($width); ?> <?php echo e($height); ?>" class="h-44 w-full" role="img" aria-label="<?php echo e($title); ?> chart" data-live-chart-canvas>
            <?php $__currentLoopData = $yTicks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tick): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $y = $toY((float) $tick);
                ?>
                <line x1="<?php echo e($padL); ?>" y1="<?php echo e(round($y, 1)); ?>" x2="<?php echo e($padL + $plotW); ?>" y2="<?php echo e(round($y, 1)); ?>" stroke="#334155" stroke-width="1" stroke-dasharray="3 4" />
                <text x="<?php echo e($padL - 6); ?>" y="<?php echo e(round($y + 3, 1)); ?>" text-anchor="end" fill="#94a3b8" font-size="9" font-family="ui-sans-serif, system-ui, sans-serif">
                    <?php echo e($formatTick((float) $tick)); ?>

                </text>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($type === 'line'): ?>
                <?php
                    $points = collect($values)->map(fn ($value, $index) => round($toX($index), 1).','.round($toY($value), 1))->all();
                    $path = 'M '.implode(' L ', $points);
                ?>
                <path d="<?php echo e($path); ?>" fill="none" stroke="<?php echo e($accent); ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <circle cx="<?php echo e(round($toX($index), 1)); ?>" cy="<?php echo e(round($toY($value), 1)); ?>" r="3.5" fill="<?php echo e($accent); ?>" stroke="#0f172a" stroke-width="1.5" />
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php elseif($type === 'bar'): ?>
                <?php $slotWidth = $plotW / $count; ?>
                <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $barW = $slotWidth * 0.55;
                        $x = $padL + ($index * $slotWidth) + (($slotWidth - $barW) / 2);
                        $barH = ($value / $yMax) * $plotH;
                        $y = $padT + $plotH - $barH;
                        $opacity = 0.45 + (($index % 5) * 0.12);
                    ?>
                    <rect x="<?php echo e(round($x, 1)); ?>" y="<?php echo e(round($y, 1)); ?>" width="<?php echo e(round($barW, 1)); ?>" height="<?php echo e(round($barH, 1)); ?>" rx="2" fill="<?php echo e($accent); ?>" opacity="<?php echo e(min(1, $opacity)); ?>" />
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <?php
                    $rowH = $plotH / max($count, 1);
                    $barTrack = $plotW * 0.72;
                ?>
                <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $label = $labels[$index] ?? '';
                        $barW = ($value / $yMax) * $barTrack;
                        $y = $padT + ($index * $rowH) + ($rowH * 0.22);
                        $barH = $rowH * 0.56;
                    ?>
                    <text x="<?php echo e($padL - 4); ?>" y="<?php echo e(round($y + ($barH / 2) + 3, 1)); ?>" text-anchor="end" fill="#94a3b8" font-size="8" font-family="ui-sans-serif, system-ui, sans-serif">
                        <?php echo e(\Illuminate\Support\Str::limit($label, 14)); ?>

                    </text>
                    <rect x="<?php echo e($padL); ?>" y="<?php echo e(round($y, 1)); ?>" width="<?php echo e(round($barW, 1)); ?>" height="<?php echo e(round($barH, 1)); ?>" rx="2" fill="<?php echo e($accent); ?>" opacity="0.85" />
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <?php if($type !== 'horizontal-bar'): ?>
                <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <text
                        x="<?php echo e(round($toX($index), 1)); ?>"
                        y="<?php echo e($padT + $plotH + 18); ?>"
                        text-anchor="middle"
                        fill="#94a3b8"
                        font-size="9"
                        font-family="ui-sans-serif, system-ui, sans-serif"
                    >
                        <?php echo e($label); ?>

                    </text>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <line x1="<?php echo e($padL); ?>" y1="<?php echo e($padT + $plotH); ?>" x2="<?php echo e($padL + $plotW); ?>" y2="<?php echo e($padT + $plotH); ?>" stroke="#475569" stroke-width="1" />
            <line x1="<?php echo e($padL); ?>" y1="<?php echo e($padT); ?>" x2="<?php echo e($padL); ?>" y2="<?php echo e($padT + $plotH); ?>" stroke="#475569" stroke-width="1" />

            <?php if($isEmpty && $emptyMessage): ?>
                <text x="<?php echo e($padL + ($plotW / 2)); ?>" y="<?php echo e($padT + ($plotH / 2) + 3); ?>" text-anchor="middle" fill="#64748b" font-size="11" font-family="ui-sans-serif, system-ui, sans-serif" data-live-chart-empty>
                    <?php echo e($emptyMessage); ?>

                </text>
            <?php endif; ?>
        </svg>
    </div>

    <?php if($footerLink): ?>
        <a href="<?php echo e($footerLink); ?>" class="mt-3 inline-block text-xs font-semibold text-violet-300 hover:text-violet-200"><?php echo e($footerLabel); ?></a>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/admin-chart-panel.blade.php ENDPATH**/ ?>