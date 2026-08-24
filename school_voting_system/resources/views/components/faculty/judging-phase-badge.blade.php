@props([
    'event',
    'locked' => false,
    'showOpensAt' => false,
])

@php
    $phase = $event->judgingPhase();
    $key = $locked ? 'submitted' : $phase['key'];
    $label = $locked ? 'Submitted' : $phase['label'];
    $tone = match ($key) {
        'open', 'submitted' => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-200',
        'scheduled' => 'border-sky-500/30 bg-sky-500/10 text-sky-200',
        default => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
    };
@endphp

<div {{ $attributes->merge(['class' => 'shrink-0']) }}>
    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $tone }}">
        {{ $label }}
    </span>
    @if ($showOpensAt && ! $locked && $phase['key'] === 'scheduled' && filled($phase['opens_at']))
        <p class="mt-1 text-xs text-sky-200/80">Opens {{ $phase['opens_at'] }}</p>
    @elseif ($showOpensAt && ! $locked && $phase['key'] === 'open' && filled($phase['closes_at']))
        <p class="mt-1 text-xs text-emerald-200/80">Closes {{ $phase['closes_at'] }}</p>
    @endif
</div>
