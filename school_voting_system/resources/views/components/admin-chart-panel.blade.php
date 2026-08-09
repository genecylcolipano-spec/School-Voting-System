@props([
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
])

@php
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
@endphp

<div
    {{ $attributes->merge(['class' => 'flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20']) }}
    @if ($liveKey) data-live-chart="{{ $liveKey }}" data-chart-type="{{ $type }}" data-chart-accent="{{ $accent }}" @endif
    @if ($emptyMessage) data-empty-message="{{ $emptyMessage }}" @endif
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="text-base font-semibold text-white">{{ $title }}</h3>
            @if ($subtitle)
                <p class="mt-0.5 text-xs text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="mt-4 flex-1">
        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-44 w-full" role="img" aria-label="{{ $title }} chart" data-live-chart-canvas>
            @foreach ($yTicks as $tick)
                @php
                    $y = $toY((float) $tick);
                @endphp
                <line x1="{{ $padL }}" y1="{{ round($y, 1) }}" x2="{{ $padL + $plotW }}" y2="{{ round($y, 1) }}" stroke="#334155" stroke-width="1" stroke-dasharray="3 4" />
                <text x="{{ $padL - 6 }}" y="{{ round($y + 3, 1) }}" text-anchor="end" fill="#94a3b8" font-size="9" font-family="ui-sans-serif, system-ui, sans-serif">
                    {{ $formatTick((float) $tick) }}
                </text>
            @endforeach

            @if ($type === 'line')
                @php
                    $points = collect($values)->map(fn ($value, $index) => round($toX($index), 1).','.round($toY($value), 1))->all();
                    $path = 'M '.implode(' L ', $points);
                @endphp
                <path d="{{ $path }}" fill="none" stroke="{{ $accent }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                @foreach ($values as $index => $value)
                    <circle cx="{{ round($toX($index), 1) }}" cy="{{ round($toY($value), 1) }}" r="3.5" fill="{{ $accent }}" stroke="#0f172a" stroke-width="1.5" />
                @endforeach
            @elseif ($type === 'bar')
                @php $slotWidth = $plotW / $count; @endphp
                @foreach ($values as $index => $value)
                    @php
                        $barW = $slotWidth * 0.55;
                        $x = $padL + ($index * $slotWidth) + (($slotWidth - $barW) / 2);
                        $barH = ($value / $yMax) * $plotH;
                        $y = $padT + $plotH - $barH;
                        $opacity = 0.45 + (($index % 5) * 0.12);
                    @endphp
                    <rect x="{{ round($x, 1) }}" y="{{ round($y, 1) }}" width="{{ round($barW, 1) }}" height="{{ round($barH, 1) }}" rx="2" fill="{{ $accent }}" opacity="{{ min(1, $opacity) }}" />
                @endforeach
            @else
                @php
                    $rowH = $plotH / max($count, 1);
                    $barTrack = $plotW * 0.72;
                @endphp
                @foreach ($values as $index => $value)
                    @php
                        $label = $labels[$index] ?? '';
                        $barW = ($value / $yMax) * $barTrack;
                        $y = $padT + ($index * $rowH) + ($rowH * 0.22);
                        $barH = $rowH * 0.56;
                    @endphp
                    <text x="{{ $padL - 4 }}" y="{{ round($y + ($barH / 2) + 3, 1) }}" text-anchor="end" fill="#94a3b8" font-size="8" font-family="ui-sans-serif, system-ui, sans-serif">
                        {{ \Illuminate\Support\Str::limit($label, 14) }}
                    </text>
                    <rect x="{{ $padL }}" y="{{ round($y, 1) }}" width="{{ round($barW, 1) }}" height="{{ round($barH, 1) }}" rx="2" fill="{{ $accent }}" opacity="0.85" />
                @endforeach
            @endif

            @if ($type !== 'horizontal-bar')
                @foreach ($labels as $index => $label)
                    <text
                        x="{{ round($toX($index), 1) }}"
                        y="{{ $padT + $plotH + 18 }}"
                        text-anchor="middle"
                        fill="#94a3b8"
                        font-size="9"
                        font-family="ui-sans-serif, system-ui, sans-serif"
                    >
                        {{ $label }}
                    </text>
                @endforeach
            @endif

            <line x1="{{ $padL }}" y1="{{ $padT + $plotH }}" x2="{{ $padL + $plotW }}" y2="{{ $padT + $plotH }}" stroke="#475569" stroke-width="1" />
            <line x1="{{ $padL }}" y1="{{ $padT }}" x2="{{ $padL }}" y2="{{ $padT + $plotH }}" stroke="#475569" stroke-width="1" />

            @if ($isEmpty && $emptyMessage)
                <text x="{{ $padL + ($plotW / 2) }}" y="{{ $padT + ($plotH / 2) + 3 }}" text-anchor="middle" fill="#64748b" font-size="11" font-family="ui-sans-serif, system-ui, sans-serif" data-live-chart-empty>
                    {{ $emptyMessage }}
                </text>
            @endif
        </svg>
    </div>

    @if ($footerLink)
        <a href="{{ $footerLink }}" class="mt-3 inline-block text-xs font-semibold text-violet-300 hover:text-violet-200">{{ $footerLabel }}</a>
    @endif
</div>
