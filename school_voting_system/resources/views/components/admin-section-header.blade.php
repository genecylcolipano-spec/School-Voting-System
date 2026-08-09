@props(['title', 'description' => null, 'badge' => null, 'badgeTone' => 'amber'])

@php
    $badgeTones = [
        'amber' => 'bg-amber-500/20 text-amber-200',
        'emerald' => 'bg-emerald-500/20 text-emerald-200',
        'violet' => 'bg-violet-500/20 text-violet-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-3']) }}>
    <div>
        <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        @if ($badge)
            <span data-live-fundraiser-badge class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeTones[$badgeTone] }} {{ $badge ? '' : 'hidden' }}">{{ $badge }}</span>
        @else
            <span data-live-fundraiser-badge class="hidden rounded-full px-3 py-1 text-xs font-semibold {{ $badgeTones[$badgeTone] }}"></span>
        @endif
        @isset($actions)
            <div class="flex flex-wrap gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>
