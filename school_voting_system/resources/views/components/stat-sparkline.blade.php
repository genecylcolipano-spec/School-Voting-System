@props([
    'type' => 'line',
    'values' => [],
    'percent' => 0,
    'stroke' => '#a78bfa',
    'liveKey' => null,
])

@php
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
@endphp

<div
    class="h-full w-full"
    @if ($liveKey) data-live-sparkline="{{ $liveKey }}" data-sparkline-stroke="{{ $stroke }}" @endif
>
    @if ($type === 'line')
        <svg viewBox="0 0 80 24" class="h-full w-full" preserveAspectRatio="none" aria-hidden="true" data-sparkline-canvas>
            <path d="{{ $linePath($values) }}" fill="none" stroke="{{ $stroke }}" stroke-width="2" stroke-linecap="round" />
        </svg>
    @elseif ($type === 'bars')
        <svg viewBox="0 0 80 24" class="h-full w-full" preserveAspectRatio="none" aria-hidden="true" data-sparkline-canvas>
            @foreach ($heights as $index => $barHeight)
                @php
                    $slotWidth = 80 / max(count($heights), 1);
                    $barW = 8;
                    $x = 4 + ($index * $slotWidth);
                    $y = 24 - $barHeight;
                    $opacity = $barHeight > 0 ? min(1, 0.45 + (($index % 5) * 0.12)) : 0.25;
                @endphp
                <rect x="{{ round($x, 1) }}" y="{{ round($y, 1) }}" width="{{ $barW }}" height="{{ max($barHeight, 1) }}" rx="1.5" fill="{{ $stroke }}" opacity="{{ $opacity }}" />
            @endforeach
        </svg>
    @else
        <svg viewBox="0 0 36 24" class="h-full w-auto" aria-hidden="true" data-sparkline-canvas>
            <circle cx="12" cy="12" r="8" fill="none" stroke="#1e293b" stroke-width="3" />
            @if ($percent > 0)
                <circle cx="12" cy="12" r="8" fill="none" stroke="{{ $stroke }}" stroke-width="3" stroke-dasharray="{{ $donutDash($percent) }}" stroke-linecap="round" transform="rotate(-90 12 12)" />
            @endif
        </svg>
    @endif
</div>
