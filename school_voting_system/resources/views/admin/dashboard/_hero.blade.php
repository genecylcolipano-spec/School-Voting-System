<div class="relative flex h-full min-h-[17rem] flex-col justify-between gap-6 overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/80 p-6 shadow-sm shadow-black/20 sm:p-7">
    <div>
        <span class="inline-flex rounded-full border border-violet-500/20 bg-slate-950/60 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
            {{ $assignedRole }} Console
        </span>
        <h2 class="mt-4 text-2xl font-bold leading-tight tracking-tight text-white sm:text-3xl">
            Unified Campus Event and Voting Management System
        </h2>
        <p class="mt-3 max-w-md text-sm font-normal leading-relaxed text-slate-400">
            Manage elections, campaigns, events, and live voting efficiently.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @unless ($isAuditor || $isReadOnly)
            @if ($canEditElection && $election)
                <a href="{{ route('admin.elections.edit', $election) }}" class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                    Manage Election
                </a>
            @elseif ($canCreateElection)
                <a href="{{ route('admin.elections.create') }}" class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                    Create Election
                </a>
            @endif
            @if ($canCreateFundraiser)
                <a href="{{ route('admin.fundraisers.create') }}" class="rounded-xl border border-violet-500/30 px-4 py-2 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">
                    Create Fundraiser
                </a>
            @endif
        @endunless
        <x-admin-status-badge
            :status="$statistics['election_status']"
            :label="$statistics['election_status']"
            data-live-election-status
            data-fallback="{{ $statistics['election_status'] }}"
        />
    </div>
</div>
