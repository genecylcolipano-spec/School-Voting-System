@props([
    'type' => 'status',
    'toneKey' => null,
    'label' => '',
])

@php
    $isCategory = $type === 'category';
    $key = strtolower((string) ($toneKey ?? ''));

    if ($isCategory) {
        $tone = match ($key) {
            'election' => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-300',
            'school_event' => 'border-sky-500/30 bg-sky-500/15 text-sky-300',
            'talent' => 'border-violet-500/30 bg-violet-500/15 text-violet-300',
            'fundraising' => 'border-amber-500/30 bg-amber-500/15 text-amber-300',
            default => 'border-cyan-500/30 bg-cyan-500/15 text-cyan-300',
        };
        // Fixed box so every category badge occupies the same space.
        $size = 'h-6 w-[132px]';
    } else {
        $tone = match (true) {
            in_array($key, ['pending', 'open', 'entries_open', 'scheduled', 'registration_open', 'registration_period', 'results_pending', 'upcoming'], true)
                => 'border-amber-500/30 bg-amber-500/15 text-amber-300',
            in_array($key, ['approved', 'verified', 'active', 'voting_open', 'resolved', 'success', 'ongoing'], true)
                => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-300',
            in_array($key, ['draft', 'registration_closed', 'voting_closed', 'voting_paused'], true)
                => 'border-slate-500/30 bg-slate-600/40 text-slate-300',
            in_array($key, ['archived', 'inactive', 'completed'], true)
                => 'border-slate-500/25 bg-slate-600/40 text-slate-400',
            in_array($key, ['rejected', 'failed', 'annulled'], true)
                => 'border-rose-500/30 bg-rose-500/15 text-rose-300',
            in_array($key, ['results_published', 'published'], true)
                => 'border-violet-500/30 bg-violet-500/15 text-violet-300',
            default => 'border-slate-600/40 bg-slate-700 text-slate-300',
        };
        // Fixed box so every status badge occupies the same space.
        $size = 'h-6 w-[140px]';
    }

    $shell = implode(' ', [
        'inline-flex',
        $size,
        'shrink-0',
        'items-center',
        'justify-center',
        'overflow-hidden',
        'whitespace-nowrap',
        'rounded-full',
        'border',
        'px-2.5',
        'text-[10px]',
        'font-semibold',
        'uppercase',
        'tracking-wide',
        'leading-none',
        $tone,
    ]);
@endphp

<span {{ $attributes->merge(['class' => $shell]) }}>
    <span class="truncate">{{ $label !== '' ? $label : $slot }}</span>
</span>
