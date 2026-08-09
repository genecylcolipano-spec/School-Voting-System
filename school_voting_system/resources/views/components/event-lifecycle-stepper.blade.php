@props(['steps' => []])

@if (count($steps) > 0)
    <section class="vm-lifecycle mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
        <h3 class="text-lg font-semibold text-white">Event Status Timeline</h3>
        <p class="mt-1 text-sm text-slate-400">Election lifecycle from draft through archival.</p>

        <ol class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-2">
            @foreach ($steps as $step)
                @php
                    $state = $step['state'] ?? 'upcoming';
                    $tone = match ($state) {
                        'current' => 'border-violet-400 bg-violet-500/15 text-violet-100 shadow-lg shadow-violet-900/20',
                        'completed' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-100',
                        default => 'border-slate-800 bg-slate-950/40 text-slate-400',
                    };
                @endphp
                <li class="relative flex flex-1 flex-col items-center rounded-xl border px-3 py-4 text-center transition {{ $tone }}">
                    <span class="text-xl" aria-hidden="true">{{ $step['icon'] ?? '•' }}</span>
                    <span class="mt-2 text-xs font-semibold uppercase tracking-wide">{{ $step['label'] }}</span>
                    @if ($state === 'current')
                        <span class="mt-2 rounded-full bg-violet-500/20 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-200">Current</span>
                    @endif
                    @if (! $loop->last)
                        <span class="pointer-events-none absolute -bottom-3 left-1/2 hidden h-6 w-px -translate-x-1/2 bg-slate-700 lg:bottom-auto lg:left-auto lg:right-0 lg:top-1/2 lg:block lg:h-px lg:w-full lg:translate-x-1/2 lg:-translate-y-1/2" aria-hidden="true"></span>
                    @endif
                </li>
            @endforeach
        </ol>
    </section>
@endif
