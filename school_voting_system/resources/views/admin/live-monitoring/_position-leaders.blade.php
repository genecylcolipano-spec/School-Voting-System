@php
    $showLeaders = ! empty($card['show_position_leaders']);
    $leaders = collect($card['position_leaders'] ?? []);
@endphp

<div
    class="overflow-hidden rounded-xl border border-violet-500/15 bg-slate-950/40 {{ $showLeaders ? '' : 'hidden' }}"
    data-position-leaders
>
    <div class="border-b border-slate-800 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Leading by position</div>
    <ul class="{{ $leaders->isEmpty() ? 'hidden' : '' }} divide-y divide-slate-800" data-position-leaders-list>
        @foreach ($leaders as $row)
            <li class="flex items-center gap-3 px-3 py-2 text-sm">
                <span class="w-28 shrink-0 truncate text-[11px] font-semibold uppercase tracking-wide text-violet-300">{{ $row['position'] }}</span>
                <span class="min-w-0 flex-1 truncate text-slate-200">
                    {{ $row['display'] }}
                    @if (! empty($row['tied']))
                        <span class="ml-1 text-[10px] font-semibold uppercase tracking-wide text-amber-300">Tie</span>
                    @endif
                </span>
                <span class="font-bold text-white">{{ number_format($row['votes'] ?? 0) }}</span>
                <span class="w-12 text-right text-xs text-slate-400">{{ $row['percent'] ?? 0 }}%</span>
            </li>
        @endforeach
    </ul>
    <p class="{{ $leaders->isEmpty() ? '' : 'hidden' }} px-3 py-4 text-sm text-slate-400" data-position-leaders-empty>No candidates yet.</p>
</div>
