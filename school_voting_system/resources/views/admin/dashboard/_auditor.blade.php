@if ($isAuditor && ! empty($auditorChecks))
    <section class="rounded-2xl border border-fuchsia-500/20 bg-fuchsia-950/20 p-5">
        <h3 class="text-lg font-semibold text-fuchsia-200">Auditor Cross-Check</h3>
        <p class="mt-1 text-sm text-slate-400">Verified results only — read-only, no modifications.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ([
                ['label' => 'Votes Cast', 'value' => $auditorChecks['votes_cast']],
                ['label' => 'Eligible Voters', 'value' => $auditorChecks['eligible_voters']],
                ['label' => 'Balance Check', 'value' => $auditorChecks['balance_ok'] ? 'Balanced' : 'Anomaly', 'tone' => $auditorChecks['balance_ok'] ? 'emerald' : 'rose'],
                ['label' => 'Duplicate Attempts (7d)', 'value' => $auditorChecks['duplicate_attempts'], 'tone' => 'amber'],
                ['label' => 'Pending Posters', 'value' => $auditorChecks['pending_posters'], 'tone' => 'violet'],
            ] as $check)
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                    <p class="text-xs text-slate-400">{{ $check['label'] }}</p>
                    <p @class([
                        'text-2xl font-bold',
                        'text-white' => ! isset($check['tone']),
                        'text-emerald-400' => ($check['tone'] ?? '') === 'emerald',
                        'text-rose-400' => ($check['tone'] ?? '') === 'rose',
                        'text-amber-300' => ($check['tone'] ?? '') === 'amber',
                        'text-violet-300' => ($check['tone'] ?? '') === 'violet',
                    ])>{{ $check['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif
