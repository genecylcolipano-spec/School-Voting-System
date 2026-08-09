@php
    $statusTone = match ($card['status_key'] ?? '') {
        'voting_open' => 'bg-emerald-500/15 text-emerald-200',
        'voting_paused' => 'bg-amber-500/15 text-amber-200',
        'results_pending', 'voting_closed' => 'bg-orange-500/15 text-orange-200',
        'published' => 'bg-sky-500/15 text-sky-200',
        default => 'bg-slate-700/40 text-slate-300',
    };
    $turnout = min(100, max(0, (float) ($card['turnout_percent'] ?? 0)));
@endphp

<article
    class="overflow-hidden rounded-2xl border {{ ! empty($card['is_live']) ? 'border-emerald-500/30' : 'border-violet-500/15' }} bg-slate-900/80"
    data-live-card
    data-card-id="{{ $card['id'] }}"
    data-card-type="election"
    data-votes-cast="{{ (int) ($card['votes_cast'] ?? 0) }}"
>
    <div class="relative aspect-[21/7] overflow-hidden bg-slate-950">
        <img src="{{ $card['banner_url'] }}" alt="" class="h-full w-full object-cover opacity-85">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/25 to-transparent"></div>
        <div class="absolute left-3 top-3">
            @include('admin.live-monitoring._live-badge', ['isLive' => ! empty($card['is_live'])])
        </div>
        <div class="absolute bottom-3 left-3 right-3 flex flex-wrap items-end justify-between gap-2">
            <div class="min-w-0 pr-2">
                <h3 class="truncate text-base font-bold text-white sm:text-lg" data-field="name">{{ $card['name'] }}</h3>
                <p class="mt-0.5 truncate text-xs text-slate-300">
                    <span class="text-slate-500">Owner</span>
                    <span data-field="owner_name">{{ $card['owner_name'] }}</span>
                    @if (! empty($card['owner_account']))
                        <span class="text-slate-500">({{ $card['owner_account'] }})</span>
                    @endif
                </p>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $statusTone }}" data-field="status_label">{{ $card['status_label'] }}</span>
        </div>
    </div>

    <div class="space-y-3.5 p-4">
        @include('admin.live-monitoring._phase-timeline', ['steps' => $card['phase_steps'] ?? []])

        <div class="grid grid-cols-3 gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Votes Cast</p>
                <p class="mt-1 text-xl font-bold text-white transition-colors duration-300" data-field="votes_cast" data-flashable>{{ number_format($card['votes_cast'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Turnout</p>
                <p class="mt-1 text-xl font-bold text-white"><span data-field="turnout_percent" data-flashable>{{ $card['turnout_percent'] ?? 0 }}</span>%</p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-violet-500 transition-all duration-500" data-turnout-bar style="width: {{ $turnout }}%"></div>
                </div>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Vote</p>
                <p class="mt-1 text-sm font-semibold leading-snug text-slate-200" data-field="last_vote_at" data-flashable>{{ $card['last_vote_at'] ?? '—' }}</p>
            </div>
        </div>

        <details class="group">
            <summary class="cursor-pointer list-none text-xs font-semibold text-slate-500 hover:text-slate-300 [&::-webkit-details-marker]:hidden">
                <span class="inline-flex items-center gap-1">More metrics <span class="transition group-open:rotate-90">›</span></span>
            </summary>
            <dl class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2">
                    <dt class="text-[10px] uppercase tracking-wide text-slate-500">Schedule</dt>
                    <dd class="mt-1 text-xs text-slate-200" data-field="schedule">{{ $card['schedule'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2">
                    <dt class="text-[10px] uppercase tracking-wide text-slate-500">Registered</dt>
                    <dd class="mt-1 text-sm font-bold text-white" data-field="registered_voters">{{ number_format($card['registered_voters'] ?? 0) }}</dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2">
                    <dt class="text-[10px] uppercase tracking-wide text-slate-500">Candidates</dt>
                    <dd class="mt-1 text-sm font-bold text-white" data-field="candidates_count">{{ number_format($card['candidates_count'] ?? 0) }}</dd>
                </div>
            </dl>
        </details>

        <div class="flex flex-wrap gap-2 pt-0.5">
            <a href="{{ $card['details_url'] }}" class="rounded-xl border border-slate-700 px-3 py-1.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Open Details</a>
            @if (! empty($card['show_results_shortcut']))
                <a href="{{ $card['results_url'] }}" class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1.5 text-sm font-semibold text-white hover:opacity-90">View Results</a>
            @endif
        </div>
    </div>
</article>
