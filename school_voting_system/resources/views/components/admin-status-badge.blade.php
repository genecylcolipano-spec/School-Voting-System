@props(['status', 'label' => null])

@php
    $statusValue = match (true) {
        $status instanceof \BackedEnum => $status->value,
        $status instanceof \UnitEnum => $status->name,
        default => (string) $status,
    };
    $normalized = strtolower($statusValue);
    $display = $label ?? ucfirst(str_replace('_', ' ', $normalized));
    $classes = match (true) {
        in_array($normalized, ['pending', 'open', 'entries_open', 'scheduled', 'registration_open', 'registration_period', 'results_pending', 'upcoming']) => 'border-amber-500/30 bg-amber-500/15 text-amber-300',
        in_array($normalized, ['approved', 'verified', 'active', 'voting_open', 'resolved', 'success', 'ongoing']) => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-300',
        in_array($normalized, ['draft', 'registration_closed', 'voting_closed', 'voting_paused']) => 'border-slate-500/30 bg-slate-600/40 text-slate-300',
        in_array($normalized, ['archived', 'inactive', 'completed']) => 'border-slate-500/25 bg-slate-600/40 text-slate-400',
        in_array($normalized, ['rejected', 'failed', 'annulled']) => 'border-rose-500/30 bg-rose-500/15 text-rose-300',
        in_array($normalized, ['results_published', 'published']) => 'border-violet-500/30 bg-violet-500/15 text-violet-300',
        default => 'border-slate-600/40 bg-slate-700 text-slate-300',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide '.$classes]) }}>
    {{ $display }}
</span>
