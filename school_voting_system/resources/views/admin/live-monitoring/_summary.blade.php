@php
    $summary = $summary ?? [
        'live_now' => 0,
        'voting_open' => 0,
        'results_pending' => 0,
        'published' => 0,
        'total_votes' => 0,
        'active_owners' => 0,
        'total_activities' => 0,
        'mode' => $mode ?? 'election',
    ];

    $isSuper = (bool) ($isSuperAdmin ?? false);
    $isElection = ($mode ?? 'election') === 'election';

    $liveLabel = $isElection
        ? ($isSuper ? 'Live Elections' : 'My Live Elections')
        : ($isSuper ? 'Live Competitions' : 'My Live Competitions');

    $ownersLabel = $isSuper ? 'Active Administrators' : 'Owned by You';
@endphp

<div class="mb-4">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-wide {{ $isSuper ? 'text-violet-300' : 'text-slate-500' }}">
                {{ $isSuper ? 'Institution overview' : 'Your monitoring scope' }}
            </p>
            <p class="mt-0.5 text-xs text-slate-400">
                {{ $isSuper
                    ? 'Aggregated from all administrators across the current tab.'
                    : 'Showing only activities you created or manage.' }}
            </p>
        </div>
        <p class="text-xs text-slate-500">
            <span data-summary="total_activities">{{ number_format($summary['total_activities'] ?? 0) }}</span> activities in view
        </p>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6" data-live-summary>
        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-300/80">{{ $liveLabel }}</p>
            <p class="mt-1 text-2xl font-bold text-white" data-summary="live_now">{{ number_format($summary['live_now'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Open</p>
            <p class="mt-1 text-2xl font-bold text-white" data-summary="voting_open">{{ number_format($summary['voting_open'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-orange-500/20 bg-orange-500/5 px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-300/80">Results Pending</p>
            <p class="mt-1 text-2xl font-bold text-white" data-summary="results_pending">{{ number_format($summary['results_pending'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-sky-500/20 bg-sky-500/5 px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-300/80">Published</p>
            <p class="mt-1 text-2xl font-bold text-white" data-summary="published">{{ number_format($summary['published'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Votes</p>
            <p class="mt-1 text-2xl font-bold text-white" data-summary="total_votes">{{ number_format($summary['total_votes'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $ownersLabel }}</p>
            <p class="mt-1 text-2xl font-bold text-white" data-summary="active_owners">{{ number_format($summary['active_owners'] ?? 0) }}</p>
        </div>
    </div>
</div>
