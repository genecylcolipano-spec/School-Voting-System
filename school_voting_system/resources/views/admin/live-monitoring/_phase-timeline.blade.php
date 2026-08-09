@php
    $steps = $steps ?? [];
@endphp

@if (! empty($steps))
    <ol class="mt-3 flex flex-wrap items-center gap-x-1 gap-y-1.5" data-phase-steps aria-label="Workflow progress">
        @foreach ($steps as $step)
            @php
                $state = $step['state'] ?? 'upcoming';
                $dot = match ($state) {
                    'done' => 'bg-violet-400',
                    'live' => 'bg-emerald-400 ring-2 ring-emerald-400/30',
                    'current' => 'bg-amber-400 ring-2 ring-amber-400/25',
                    default => 'bg-slate-600',
                };
                $text = match ($state) {
                    'done' => 'text-violet-300/90',
                    'live' => 'text-emerald-300 font-bold',
                    'current' => 'text-amber-200 font-semibold',
                    default => 'text-slate-500',
                };
            @endphp
            <li class="inline-flex items-center gap-1 {{ $text }}">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dot }}"></span>
                <span class="text-[10px] uppercase tracking-wide">{{ $step['label'] }}</span>
                @unless ($loop->last)
                    <span class="mx-1 h-px w-3 bg-slate-700/80" aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
@endif
