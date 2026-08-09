<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'line',
    'values' => [],
    'percent' => 0,
    'stroke' => '#a78bfa',
    'liveKey' => null,
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
    'type' => 'line',
    'values' => [],
    'percent' => 0,
    'stroke' => '#a78bfa',
    'liveKey' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $values = array_map('floatval', array_values($values));
    $percent = max(0.0, min(100.0, (float) $percent));

    $linePath = static function (array $series, float $width = 80, float $height = 24): string {
        if ($series === []) {
            return 'M0 '.($height / 2).' L'.$width.' '.($height / 2);
        }

        $max = max($series);
        $min = min($series);
        $range = max($max - $min, 0.001);
        $count = count($series);
        $points = [];

        foreach ($series as $index => $value) {
            $x = $count <= 1 ? $width / 2 : ($index / ($count - 1)) * $width;
            $y = $height - (($value - $min) / $range) * ($height - 4) - 2;
            $points[] = round($x, 1).','.round($y, 1);
        }

        return 'M '.implode(' L ', $points);
    };

    $barHeights = static function (array $series, float $maxHeight = 24): array {
        if ($series === []) {
            return array_fill(0, 5, 0.0);
        }

        $peak = max(max($series), 1);

        return array_map(
            fn (float $value) => round(($value / $peak) * $maxHeight, 1),
            $series,
        );
    };

    $donutDash = static function (float $pct): string {
        $circumference = 2 * M_PI * 8;
        $filled = ($pct / 100) * $circumference;

        return round($filled, 2).' '.round($circumference, 2);
    };

    $heights = $type === 'bars' ? $barHeights($values) : [];
?>

<div
    class="h-full w-full"
    <?php if($liveKey): ?> data-live-sparkline="<?php echo e($liveKey); ?>" data-sparkline-stroke="<?php echo e($stroke); ?>" <?php endif; ?>
>
    <?php if($type === 'line'): ?>
        <svg viewBox="0 0 80 24" class="h-full w-full" preserveAspectRatio="none" aria-hidden="true" data-sparkline-canvas>
            <path d="<?php echo e($linePath($values)); ?>" fill="none" stroke="<?php echo e($stroke); ?>" stroke-width="2" stroke-linecap="round" />
        </svg>
    <?php elseif($type === 'bars'): ?>
        <svg viewBox="0 0 80 24" class="h-full w-full" preserveAspectRatio="none" aria-hidden="true" data-sparkline-canvas>
            <?php $__currentLoopData = $heights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $barHeight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $slotWidth = 80 / max(count($heights), 1);
                    $barW = 8;
                    $x = 4 + ($index * $slotWidth);
                    $y = 24 - $barHeight;
                    $opacity = $barHeight > 0 ? min(1, 0.45 + (($index % 5) * 0.12)) : 0.25;
                ?>
                <rect x="<?php echo e(round($x, 1)); ?>" y="<?php echo e(round($y, 1)); ?>" width="<?php echo e($barW); ?>" height="<?php echo e(max($barHeight, 1)); ?>" rx="1.5" fill="<?php echo e($stroke); ?>" opacity="<?php echo e($opacity); ?>" />
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </svg>
    <?php else: ?>
        <svg viewBox="0 0 36 24" class="h-full w-auto" aria-hidden="true" data-sparkline-canvas>
            <circle cx="12" cy="12" r="8" fill="none" stroke="#1e293b" stroke-width="3" />
            <?php if($percent > 0): ?>
                <circle cx="12" cy="12" r="8" fill="none" stroke="<?php echo e($stroke); ?>" stroke-width="3" stroke-dasharray="<?php echo e($donutDash($percent)); ?>" stroke-linecap="round" transform="rotate(-90 12 12)" />
            <?php endif; ?>
        </svg>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/stat-sparkline.blade.php ENDPATH**/ ?>