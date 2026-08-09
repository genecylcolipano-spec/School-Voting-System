@props([
    'label',
    'value',
    'href' => null,
    'icon' => null,
    'tone' => 'violet',
])

@php
    $tones = [
        'violet' => 'border-violet-500/20 bg-violet-500/5',
        'emerald' => 'border-emerald-500/20 bg-emerald-500/5',
        'amber' => 'border-amber-500/20 bg-amber-500/5',
        'indigo' => 'border-indigo-500/20 bg-indigo-500/5',
        'rose' => 'border-rose-500/20 bg-rose-500/5',
    ];
    $iconTones = [
        'violet' => 'text-violet-300',
        'emerald' => 'text-emerald-300',
        'amber' => 'text-amber-300',
        'indigo' => 'text-indigo-300',
        'rose' => 'text-rose-300',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'group block rounded-2xl border p-4 transition hover:border-violet-400/40 hover:bg-slate-900/90 '.$tones[$tone]]) }}>
@else
    <div {{ $attributes->merge(['class' => 'rounded-2xl border p-4 '.$tones[$tone]]) }}>
@endif
    <div class="flex items-start justify-between gap-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p>
        @if ($icon)
            <span class="{{ $iconTones[$tone] }} opacity-80">{!! $icon !!}</span>
        @endif
    </div>
    <p class="mt-2 text-2xl font-bold text-white">{{ $value }}</p>
    @if ($href)
        <p class="mt-1 text-[10px] font-medium text-violet-300/80 group-hover:text-violet-200">View section →</p>
    @endif
@if ($href)
    </a>
@else
    </div>
@endif
