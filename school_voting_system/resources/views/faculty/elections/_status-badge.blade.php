@php
    $label = $election->campusStatusLabel();
    $class = match ($label) {
        'Open' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200',
        'Paused' => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
        'Closed' => 'border-slate-500/30 bg-slate-500/10 text-slate-300',
        default => 'border-slate-500/30 bg-slate-800/60 text-slate-400',
    };
@endphp
<span class="inline-flex rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $class }}">{{ $label }}</span>
