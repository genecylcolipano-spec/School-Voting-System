@props([
    'session',
])

<li class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-sm font-semibold text-white">
                {{ $session['device'] }}
                @if ($session['is_current'])
                    <span class="ml-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-200">Current</span>
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $session['browser'] }} · {{ $session['os'] }}
                @if ($session['ip_address'])
                    · IP {{ $session['ip_address'] }}
                @endif
            </p>
        </div>
        <p class="text-xs text-slate-500">
            Last activity {{ optional($session['last_activity'])->diffForHumans() ?? '—' }}
        </p>
    </div>
</li>
